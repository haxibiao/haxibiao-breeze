<?php

namespace Haxibiao\Breeze;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'channel',
        'account',
        'code',
        'action',
        'actu',
    ];

    //验证码获取间隔(单位秒S)
    const CODE_VALID_TIME = 60;

    //验证码失效时间(单位秒S)
    const CODE_TIME_OUT = 60 * 60 * 4;

    /**
     *  枚举
     */
    const SMS_CHANNEL   = 'sms';
    const EMAIL_CHANNEL = 'email';

    const RESET_PASSWORD   = 'RESET_PASSWORD';
    const USER_REGISTER    = 'USER_REGISTER';
    const USER_INFO_CHANGE = 'USER_INFO_CHANGE';
    const USER_LOGIN       = 'USER_LOGIN';
    const WECHAT_BIND      = 'WECHAT_BIND';
    const EXCHANGE_REMIND  = 'EXCHANGE_REMIND';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * 设置复用的创建时间范围查询，单位秒.
     *
     * @param Builder   $query  查询对象
     * @param int       $second 范围时间，单位秒
     * @return Builder  查询对象
     */
    public function scopeByValid(Builder $query, int $second = 300): Builder
    {
        $now = $this->freshTimestamp();
        $sub = clone $now;
        $sub->subSeconds($second);

        return $query->whereBetween('created_at', [$sub, $now]);
    }

    /**
     * 计算距离验证码过期时间.
     *
     * @param int $vaildSecond 有效时间 默认60s
     * @return int 剩余时间
     */
    public function makeSurplusSecond(int $vaildSecond = 60): int
    {
        $now    = $this->freshTimestamp();
        $differ = $this->created_at->diffInSeconds($now);

        return $vaildSecond - $differ;
    }

    /**
     * 验证码发送频道
     *
     * @return array
     */
    public static function getChannelType()
    {
        return [
            self::SMS_CHANNEL   => '手机短信',
            self::EMAIL_CHANNEL => '邮件',
        ];
    }

    /**
     * @Author      XXM
     * @DateTime    2019-03-07
     * @description [获取短信行为]
     * @return      [type]        [description]
     */
    public static function getVerificationActions()
    {
        return [
            self::RESET_PASSWORD   => [
                'sms'  => self::RESET_PASSWORD,
                'mail' => '修改密码',
            ],
            self::USER_REGISTER    => [
                'sms'  => self::USER_REGISTER,
                'mail' => '用户注册',
            ],
            self::USER_INFO_CHANGE => [
                'sms'  => self::USER_INFO_CHANGE,
                'mail' => '资料修改',
            ],
            self::USER_LOGIN       => [
                'sms'  => self::USER_LOGIN,
                'mail' => '用户登录',
            ],
            self::WECHAT_BIND      => [
                'sms'  => self::WECHAT_BIND,
                'mail' => '微信绑定',
            ],
            self::EXCHANGE_REMIND  => [
                'sms'  => self::EXCHANGE_REMIND,
                'mail' => '兑换提醒',
            ],
        ];
    }

    public static function getActionEnumTypes()
    {
        return [
            self::RESET_PASSWORD   => [
                'value'       => self::RESET_PASSWORD,
                'description' => '修改密码',
            ],
            self::USER_REGISTER    => [
                'value'       => self::USER_REGISTER,
                'description' => '用户注册',
            ],
            self::USER_INFO_CHANGE => [
                'value'       => self::USER_INFO_CHANGE,
                'description' => '用户信息修改',
            ],
            self::USER_LOGIN       => [
                'value'       => self::USER_LOGIN,
                'description' => '用户登录',
            ],
            self::WECHAT_BIND      => [
                'value'       => self::WECHAT_BIND,
                'description' => '微信绑定',
            ],
            self::EXCHANGE_REMIND  => [
                'value'       => self::EXCHANGE_REMIND,
                'description' => '兑换提醒',
            ],
        ];
    }

    public function checkCode($code)
    {
        return $this->code == $code;
    }

    public function getSurplusSecondAttribute()
    {
        return $this->makeSurplusSecond();
    }
}
