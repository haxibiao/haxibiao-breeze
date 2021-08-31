<?php

namespace Haxibiao\Breeze\Traits;

use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\OAuth;
use Haxibiao\Breeze\User;
use Haxibiao\Helpers\utils\OAuthUtils;
use Haxibiao\Helpers\utils\PayUtils;
use Haxibiao\Helpers\utils\PhoneUtils;
use Haxibiao\Helpers\utils\WechatUtils;
use Haxibiao\Wallet\Withdraw;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait OAuthResolvers
{
    public function bindOAuth($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = getUser();
        $code = Arr::get($args, 'code');
        $type = Arr::get($args, 'oauth_type');
        throw_if(empty($code) || empty($type), GQLException::class, '绑定失败,参数错误!');

        return $this->bind($user, $code, $type);
    }

    public function bind($user, $code, $type)
    {
        // throw_if(OAuth::getUserOauth($user, $type), GQLException::class, '您已绑定成功,请直接登录!');
        throw_if(!method_exists($this, $type), GQLException::class, '绑定失败,该授权方式不存在!');
        $oauth = $this->$type($user, $code);

        return $oauth;
    }

    public function wechat(User $user, $code)
    {
        //获取微信token
        $wechatIns            = WechatUtils::getInstance();
        list($appid, $secret) = $wechatIns->getWechatAppConfig('');
        $accessTokens         = $wechatIns->accessToken($code, $$appid, $secret);

        throw_if(!Arr::has($accessTokens, ['unionid', 'openid']), GQLException::class, '授权失败,请稍后再试!');

        //建立oauth关联
        $oAuth  = OAuth::firstOrNew(['oauth_type' => 'wechat', 'oauth_id' => $accessTokens['unionid']]);
        $openId = Arr::get($accessTokens, 'openid');
        if (isset($oAuth->id)) {
            throw_if($oAuth->user_id != $user->id, GQLException::class, '授权失败,该微信已绑定其他账户');
            $oAuthData = $oAuth->data;
            //存在open_id
            if (isset($oAuthData['openid'])) {
                $openId = Arr::get($oAuthData, 'openid');
                $wallet = $user->wallet;
                $payId  = $wallet->getPayId(Withdraw::WECHAT_PLATFORM);
                if (empty($payId)) {
                    $wallet->setPayId($openId, Withdraw::WECHAT_PLATFORM);
                    $wallet->save();

                    return $oAuth;
                } else {
                    throw new GQLException('您已授权成功,请勿重复授权!');
                }
            }
        }
        $oAuth->user_id = $user->id;
        $oAuth->data    = Arr::only($accessTokens, ['openid', 'refresh_token']);
        $oAuth->save();

        //同步钱包配置
        $wallet = $user->wallet;
        $wallet->setPayId($openId, Withdraw::WECHAT_PLATFORM);
        $wallet->save();

        return $oAuth;
    }

    public function alipay(User $user, $code)
    {
        // throw_if(true, GQLException::class, '支付宝暂未开放,请稍后再试!');

        $userInfo = PayUtils::userInfo($code);
        $openId   = Arr::get($userInfo, 'user_id');
        throw_if(empty($openId), GQLException::class, '授权失败,请稍后再试!');

        $oauth = OAuth::firstOrNew(['oauth_type' => 'alipay', 'oauth_id' => $openId]);
        throw_if(isset($oauth->id), GQLException::class, '该支付宝已被绑定,请尝试其他账户!');

        //更新OAuth绑定
        $oauth->user_id = $user->id;
        $oauth->data    = $userInfo;
        $oauth->save();

        //更新钱包OPENID
        $wallet = $user->wallet;
        $wallet->setPayId($openId, Withdraw::ALIPAY_PLATFORM);
        $wallet->save();

        return $oauth;
    }

    public function phone(User $user, $code)
    {
        $utils = new PhoneUtils();

        //获取手机号码
        $accessTokens = $utils->accessToken($code);

        Log::info('移动获取号码接口参数', $accessTokens);

        throw_if($accessTokens['resultCode'] != '103000', GQLException::class, '授权失败,请稍后再试!');

        $phone = $accessTokens['msisdn'];
        //建立oauth关联
        $oAuth = OAuth::firstOrNew(['oauth_type' => 'phone', 'oauth_id' => $phone]);
        if (isset($oAuth->id)) {
            throw_if($oAuth->user_id != $user->id, GQLException::class, '授权失败,该手机号已绑定其他账户');

        }
        $accessTokens['code'] = $code;
        $oAuth->user_id       = $user->id;
        $oAuth->data          = $accessTokens;
        $oAuth->save();

        //同步用户数据
        $user->phone = $phone;
        $user->save();

        return $oAuth;
    }

    public function resovlerOAuthBind($root, $args, $context, $info)
    {
        app_track_event("个人中心", "授权绑定", $args['oauth_type']);
        return OAuthUtils::bind(getUser(), $args['code'], $args['oauth_type'], $args['platform'] ?? null);
    }

    public function resovlerWechatBindWithCode($root, $args, $context, $info)
    {
        return WechatUtils::bindWechatWithCode(getUser(), $args['code']);
    }

    public function resovlerWechatBindWithToken($root, $args, $context, $info)
    {
        return WechatUtils::bindWechatWithToken(getUser(), $args['access_token'], $args['open_id']);
    }

    public function resovlerQQBindWithToken($root, $args, $context, $info)
    {
        $user        = getUser();
        $accessToken = $args['access_token'];
        $openId      = $args['open_id'];

        $userInfo = app('qpay')->userInfo($accessToken, $openId);
        $ret      = Arr::get($userInfo, 'ret', -1);
        throw_if($ret != 0, UserException::class, '绑定失败,腾讯QQ授权参数错误!');

        $oauth = OAuth::firstOrNew(['oauth_type' => OAuth::QQ_AUTH, 'oauth_id' => $openId]);

        if (isset($oauth->id)) {
            throw_if($oauth->user_id == $user->id, UserException::class, '绑定失败,您的账号已绑定成功,请勿重复绑定!');
            throw_if(true, UserException::class, '该QQ账户已被绑定,请尝试其他账户!');
        }

        return OAuth::store($user->id, OAuth::QQ_AUTH, $openId);
    }
}
