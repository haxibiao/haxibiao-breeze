<?php

namespace Haxibiao\Breeze\Traits;

use App\User;
use App\VerificationCode;
use Haxibiao\Breeze\BlackList;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\Ip;
use Illuminate\Support\Str;

trait AuthHelper
{

    /**
     * 静默登录/注册 - 一键登录/注册
     * @param $account 静默获取的手机号，优先尊重
     * @param $uuid  手机号为空，$account用$uuid
     */
    public static function autoSignIn($account, string $uuid)
    {
        if (!empty($account)) {
            //静默获取到了手机号的情况 - 尊重手机号
            // throw_if(!is_phone_number($account), GQLException::class, '手机号格式不正确!');
            $user = User::where('account', $account)->first();
            if (empty($user)) {
                $user = User::create([
                    'uuid'      => $uuid,
                    'account'   => $account,
                    'password'  => '',
                    'name'      => config('auth.default_name', User::DEFAULT_NAME),
                    'api_token' => Str::random(60),
                ]);
            }
        } else {
            //静默注册一个uuid为account的新账户 - 尊重uuid
            $user = User::where('uuid', $uuid)->first();
            if (empty($user)) {
                $user = User::create([
                    'uuid'      => $uuid,
                    'account'   => $uuid,
                    'password'  => '',
                    'name'      => config('auth.default_name', User::DEFAULT_NAME),
                    'api_token' => Str::random(60),
                ]);
            }
        }
        //用户名排重
        $user->update(['name' => $user->name . $user->id]);
        //记录IP
        Ip::createIpRecord('users', $user->id, $user->id);

        //已封uuid或者手机号的处理，暂时不在这里处理，没遇到刷子攻击是，p3以下需求

        //更新统计用户APP版本
        $user->updateProfileAppVersion($user);
        return $user;
    }

    /**
     * 手动登录 - 密码
     * @param $account 手机号
     * @param $password 密码
     * @param $uuid 获取到的UUID，保留最新的
     */
    public static function signIn(string $account, string $password, string $uuid): User
    {
        throw_if(!is_phone_number($account) && !is_email($account), GQLException::class, '账号格式不正确!');
        $user = User::where('account', $account)->first();

        throw_if(empty($user), GQLException::class, '账号不存在,请先注册!');
        if (!password_verify($password, $user->password)) {
            throw new GQLException('登录失败,账号或者密码错误');
        }

        if (!empty($uuid) && !strcmp($user->uuid, $uuid)) {
            $user->update(['uuid' => $uuid]);
        }

        //账号已注销
        throw_if($user->isDegregister(), UserException::class, '操作失败,账户已注销!', config('auth.close_account', '9999'));
        return $user;
    }

    /**
     * 手动登录 - 验证码
     * @param $account 手机号
     * @param $sms_code 手机验证码
     * @param $uuid 获取到的UUID，保留最新的
     */
    public static function signInWithSMSCode(string $account, string $sms_code, string $uuid)
    {
        throw_if(!is_phone_number($account), GQLException::class, '手机号格式不正确!');
        throw_if(empty($sms_code), GQLException::class, '验证码不能为空!');

        $code = User::getLoginVerificationCode($account);

        if (empty($code) || !strcmp($code, $sms_code)) {
            throw new GQLException('验证码不正确!');
        }

        $user = User::where('account', $account)->first();
        throw_if(empty($user), GQLException::class, '账号不存在,请先注册~');

        //更新uuid
        if (!strcmp($user->uuid, $uuid)) {
            $user->update(['uuid' => $uuid]);
        }

        //登录
        return $user;
    }

    /**
     * 手动注册 - 一秒注册
     * @param $account 手机号/账户
     * @param $password 密码
     * @param $uuid 获取到的UUID
     * @param $email 邮箱
     * @param $name 昵称
     */
    public static function signUp(string $account, string $password, string $uuid, $email = null, $name = null): User
    {
        app_track_event('用户', '用户注册');
        //手机号格式验证
        $flag = preg_match('/^[1](([3][0-9])|([4][5-9])|([5][0-3,5-9])|([6][5,6])|([7][0-8])|([8][0-9])|([9][1,8,9]))[0-9]{8}$/', $account);
        if (!$flag) {
            throw new GQLException('注册失败，手机号格式不正确，请检查是否输入正确');
        }
        if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $password)) {
            throw new GQLException('密码中不能包含中文');
        }

        throw_if(User::where('account', $account)->exists(), GQLException::class, '账号已存在');

        $user = User::create([
            'uuid'      => $uuid,
            'account'   => $account,
            'password'  => bcrypt($password),
            'name'      => config('auth.default_name', User::DEFAULT_NAME),
            'api_token' => Str::random(60),
            'email'     => $email,
            'name'      => $name,
        ]);

        // 记录IP
        Ip::createIpRecord('users', $user->id, $user->id);
        return $user;
    }

    /**
     * 手动注册 - 手机+验证码注册
     * @param $account 手机号
     * @param $uuid 获取到的UUID
     * @param $sms_code 手机验证码
     */
    public static function signUpWithSMSCode(string $account, string $uuid, string $sms_code): User
    {
        throw_if(empty($sms_code), GQLException::class, '验证码不能为空!');

        $code = self::getLoginVerificationCode($account);

        if (!strcmp($code, $sms_code)) {
            throw_if(empty($sms_code), GQLException::class, '验证码不正确!');
        }

        throw_if(User::where('account', $account)->exists(), GQLException::class, '账号已存在');

        $user = User::create([
            'uuid'      => $uuid,
            'account'   => $account,
            'password'  => bcrypt('12345678'),
            'name'      => config('auth.default_name', '匿名用户'),
            'api_token' => Str::random(60),
        ]);
        return $user;
    }

    //FIXME: 这验证码功能，是基础 - @wuxiuzhang
    public static function getLoginVerificationCode($account, $action = VerificationCode::USER_LOGIN)
    {
        return VerificationCode::where('account', $account)
            ->byValid(VerificationCode::CODE_VALID_TIME)
            ->where('action', $action)
            ->latest('id')
            ->first();
    }

    //FIXME: 这个黑名单功能，也要迁移到base,是网警检查必须要有的功能，每个产品都要 - @zengdawei
    public function isBlack()
    {
        if (class_exists("App\\BlackList", true)) {
            $black    = \App\BlackList::where('user_id', $this->id);
            $is_black = $black->exists();
            return $is_black;
        }
    }
}
