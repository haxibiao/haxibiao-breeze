<?php
namespace Haxibiao\Breeze\Exceptions;

class ErrorCode
{
    /**
     * 错误码约定:快速溯源、简单易记、沟通标准化
     * 错误码为5位字符串类型,默认抄底业务码为-1,错误来源分为ABC,A类表示错误来源为用户,如用户自身版本过低,不符合该逻辑要求;B表示错误来源于当前系统
     * 如业务逻辑出错;C类表示错误来源于第三方服务,比如SDK请求超时,消息无法正常投递等。
     * 定义格式可参考 https://open.taobao.com/doc.htm?docId=114&docType=1
     */

    // 失败响应
    const FAILURE_STATUS = 0;

    // 成功这状态
    const SUCCESS_STATUS = 1;

    //未知异常
    const UNKNOWN_STATUS = -1;

    // 用户无钱包
    const USER_NO_WALLET = 10100;

    // 用户未绑定支付宝
    const USER_NO_BIND_ALIPAY = 10101;

    // 用户未绑定微信
    const USER_NO_BIND_WECHAT = 10102;

    // 用户未绑定懂得赚
    const USER_NO_BIND_DDZ = 10103;

    // 用户未绑定答妹
    const USER_NO_BIND_DM = 10104;

    // 智慧点不足
    const GOLD_NOT_ENOUGH = 10105;

    // 贡献点不足
    const CONTRIBUTE_NOT_ENOUGH = 10106;

    // 等级不足
    const LEVEL_NOT_ENOUGH = 10107;

    // 参数错误
    const PARAMES_ERROR = 10108;

    // 版本过低
    const VERSION_TOO_LOW = 10109;

    // 账户已注销
    const DEREGISTER_USER = 10110;

    // 其他设备登录
    const OTHER_DEVICE_LOGIN = 10111;

    // 版本错误
    const VERSION_ERROR = 10112;

    // 用户禁用
    const USER_DISABLE = 10113;

    // 支付类系统异常
    const PAY_SYSTEM_BALANCE_NOT_ENOUGH = 10600;

    // 第三方API异常
    const JDJR_SYSTEM_HAS_WITHDRAW    = 10700;
    const JDJR_SYSTEM_REGISTERED_USER = 10701;

    // 提现类异常
    const WITHDRAW_USER_IS_DISABLED = 10800;
    const WALLET_BALANCE_NOT_ENOUGH = 10801;
    const WALLET_TODAY_LIMITED      = 10802;

    const CODE_MSG = [
        self::FAILURE_STATUS                => '操作失败,请稍后再试!',
        self::SUCCESS_STATUS                => '操作成功,请稍后再试!',
        self::UNKNOWN_STATUS                => '未知错误,请联系官方人员后再操作!',
        self::USER_NO_WALLET                => '您还没有绑定支付宝或微信哦!快去绑定吧',
        self::USER_NO_BIND_ALIPAY           => '支付宝提现信息未绑定,请先去绑定哦!',
        self::USER_NO_BIND_WECHAT           => '微信提现信息未绑定,请先去绑定哦!',
        self::USER_NO_BIND_DDZ              => '懂得赚账号信息未绑定,请先去绑定哦!',
        self::USER_NO_BIND_DM               => '小答妹账号信息未绑定,请先去绑定哦!',
        self::USER_NO_BIND_DM               => '答妹账号信息未绑定,请先去绑定哦!',
        self::GOLD_NOT_ENOUGH               => '操作失败,智慧点不足!',
        self::CONTRIBUTE_NOT_ENOUGH         => '操作失败,贡献点不足!',
        self::LEVEL_NOT_ENOUGH              => '操作失败,等级不足,请先提升等级哦!',
        self::PARAMES_ERROR                 => '操作失败,参数错误!',
        self::VERSION_TOO_LOW               => '操作失败,您的应用版本过低,请先升级到最新版本!',
        self::DEREGISTER_USER               => '操作失败,您的账户已注销!',
        self::OTHER_DEVICE_LOGIN            => '操作失败,您的账号已在其他设备登陆!!',
        self::VERSION_ERROR                 => '操作失败,服务繁忙!!',
        self::USER_DISABLE                  => '抱歉，您的账户遇到了一些异常，请联系QQ群客服：735220029，您的账户ID:',
        self::PAY_SYSTEM_BALANCE_NOT_ENOUGH => '支付系统,余额不足!',
        self::JDJR_SYSTEM_HAS_WITHDRAW      => '操作失败,京东金融平台只可提现一次!',
        self::JDJR_SYSTEM_REGISTERED_USER   => '操作失败,您非京东金融未注册新用户!',
        self::WITHDRAW_USER_IS_DISABLED     => '用户已被禁用!',
        self::WALLET_BALANCE_NOT_ENOUGH     => '钱包余额不足!',
        self::WALLET_TODAY_LIMITED          => '提现失败,今日提次数已达上限!!',
    ];

    public static function getMsg($code)
    {
        return self::CODE_MSG[$code] ?? '';
    }
}
