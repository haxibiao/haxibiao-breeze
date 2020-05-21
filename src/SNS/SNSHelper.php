<?php

namespace haxibiao\Users\SNS;

use App\OAuth;
use App\Wallet;
use App\Withdraw;
use haxibiao\Users\Exceptions\SNSException;
use Illuminate\Support\Arr;

trait WechatHelper
{
    /**
     * @param $code 微信授权码,客户端获取
     * 绑定微信,同时更新 OAuth与Wallet 数据
     */
    public static function bindWechat($user, string $code)
    {
        $accessTokens = WechatUtils::accessToken($code);
        throw_if(!data_get($accessTokens, 'openid'), SNSException::class, '授权失败,请稍后再试~');

        $oAuth = OAuth::firstOrNew([
            'oauth_type' => 'wechat',
            'oauth_id'   => $accessTokens['unionid'],
        ]);
        throw_if($oAuth->id, SNSException::class, '该微信已绑定过,请检查您的微信账户~');

        // update oauth&wallet data
        $oAuth->user_id = $user->id;
        $oAuth->data    = Arr::only($accessTokens, ['openid', 'refresh_token']);
        $oAuth->save();
        $wallet          = Wallet::firstOrNew(['user_id' => $user->id]);
        $wallet->open_id = $accessTokens['openid'];
        $wallet->save();
    }

    public static function bindAlipay($user, string $code)
    {
        $userInfo = AlipayUtils::userInfo($code);
        $openId   = data_get($userInfo, 'user_id');
        throw_if(empty($openId), SNSException::class, '授权失败,请稍后再试!');

        $oauth = OAuth::firstOrNew(['oauth_type' => 'alipay', 'oauth_id' => $openId]);
        throw_if(isset($oauth->id), SNSException::class, '该支付宝已被绑定,请尝试其他账户!');

        //更新OAuth绑定
        $oauth->user_id = $user->id;
        $oauth->data    = $userInfo;
        $oauth->save();

        //更新钱包OPENID
        $wallet = Wallet::firstOrNew(['user_id' => $user->id]);
        $wallet->setPayId($openId, Withdraw::ALIPAY_PLATFORM);
        $wallet->save();

        return $oauth;
    }
}
