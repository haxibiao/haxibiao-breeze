<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\AppId;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\Traits\OAuthRepo;
use Haxibiao\Breeze\Traits\OAuthResolvers;
use Haxibiao\Helpers\utils\SiteUtils;
use Haxibiao\Wallet\Withdraw;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class OAuth extends Model
{
    use SoftDeletes;
    use OAuthResolvers;
    use OAuthRepo;

    protected $guarded = [
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appId()
    {
        return $this->belongsTo(AppId::class, 'app_id');
    }

    public function scopeOfType($query, $value)
    {
        return is_array($value) ? $query->whereIn('oauth_type', $value) : $query->where('oauth_type', $value);
    }

    public function scopeUnionId($query, $type, $value)
    {
        $field = OAuth::getUnionIdField($type);
        return $query->where($field, $value);
    }

    const DDZ_TYPE    = Withdraw::DDZ_PLATFORM;
    const WECHAT_TYPE = Withdraw::WECHAT_PLATFORM;
    const DM_TYPE     = Withdraw::DM_PLATFORM;
    const ALIPAY_AUTH = Withdraw::ALIPAY_PLATFORM;
    const QQ_AUTH     = Withdraw::QQ_PLATFORM;

    public static function getUnionIdField($oauthType)
    {
        $sameIdTypes = [OAuth::ALIPAY_AUTH, OAuth::QQ_AUTH];
        $field       = in_array($oauthType, $sameIdTypes) ? 'oauth_id' : 'union_id';

        return $field;
    }

    public function getUnionIdAttribute()
    {
        $field = OAuth::getUnionIdField($this->oauth_type);
        return $this->$field;
    }

    public static function findWechatUser($unionId)
    {
        $oAuth = OAuth::unionId(OAuth::WECHAT_TYPE, $unionId)->first();

        if (!is_null($oAuth)) {
            return $oAuth->user;
        }
    }

    public static function unionIdFind($type, $unionId)
    {
        return OAuth::unionId($type, $unionId)->first();
    }

    public static function getTypeEnums()
    {
        return [
            'WECHAT' => [
                'value'       => 'wechat',
                'description' => '微信',
            ],
            'ALIPAY' => [
                'value'       => 'alipay',
                'description' => '支付宝',
            ],
            'TIKTOK' => [
                'value'       => 'tiktok',
                'description' => '抖音',
            ],
        ];
    }

    public static function typeTranslator($type, $language = 'zh')
    {
        $types = [
            'wechat' => '微信',
            'alipay' => '支付宝',
            'tiktok' => '抖音',
        ];

        return Arr::get($types, $type, '授权');
    }

    public static function store($userId, $type, $oauthId, $unionId = '', $data = null, $source = 0, $authAppId = '')
    {
        $field = OAuth::getUnionIdField($type);

        $oauth = OAuth::firstOrNew(['user_id' => $userId, 'oauth_type' => $type]);
        // 未绑定过该授权
        if (!isset($oauth->id)) {
            // openid是否被绑定过
            $oauth = OAuth::firstOrNew([
                'oauth_type' => $type,
                $field       => ($field == 'oauth_id' ? $oauthId : $unionId),
            ]);
            throw_if(isset($oauth->id) && $oauth->user_id != $userId, UserException::class, '该授权账户已被绑定,请尝试其他账户!');
            $appId = !empty($authAppId) ? data_get(AppId::byValue($authAppId)->select('id')->first(), 'id', 0) : 0;
            $oauth->fill([
                'oauth_id' => $oauthId,
                'union_id' => $unionId,
                'data'     => $data,
                'user_id'  => $userId,
                'is_fix'   => 1,
                'source'   => $source,
                'app_id'   => $appId,
            ]);
            $oauth->save();
        }

        return $oauth;
    }

    public static function bindSite($user, $type, $siteDomain, $siteName)
    {
        $oauth = $user->oauths()->ofType($type)->first();
        if (is_null($oauth)) {
            $oauth = self::_bindSite($user, $siteDomain, $siteName, $type);
        }

        return $oauth;
    }

    private static function _bindSite($user, $siteDomain, $siteName, $type)
    {
        //不要卡死用户提示,引导重新去登录,重新登录后会覆盖掉UUID
        $uuid = $user->uuid;
        throw_if(empty($uuid), UserException::class, '授权失败,请重新登录再试!');

        try {
            $SiteUtils = new SiteUtils($siteDomain);

            $result = $SiteUtils->auth($uuid);
            throw_if(empty($result), UserException::class, "{$siteName}绑定接口调用失败!!!", 2);

            $authUser = Arr::get($result, 'user');
            throw_if(!is_array($authUser), UserException::class, "{$siteName}绑定接口响应参数格式错误!!!", 2);

            $id = Arr::get($authUser, 'id');
            throw_if(blank($id), UserException::class, "{$siteName}绑定接口响应参数不完整!", 2);

            $oauth = OAuth::firstOrNew(['oauth_type' => $type, 'oauth_id' => $id]);
            // 重复绑定旗下平台无需异常，只认uuid
            // throw_if(isset($oauth->id), UserException::class, '授权失败,该账户已被绑定!!!', 3);

            $oauth->user_id = $user->id;
            $oauth->data    = $authUser;
            $oauth->save();
            return $oauth;
        } catch (\Exception $e) {
            Log::error("{$siteName}绑定异常 " . $user->id . ' ' . $e->getMessage());
            if ($e->getCode() == 3) {
                throw new UserException('授权失败,该账户已被绑定!!!');
            } else {
                throw new UserException('授权失败,系统错误,请稍后再试!');
            }
        }
    }

}
