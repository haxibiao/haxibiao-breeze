<?php

namespace haxibiao\user;

use App\User;
use App\VerificationCode;
use haxibiao\user\Exceptions\SignInException;
use Illuminate\Support\Facades\Auth;

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
            throw_if(!is_phone_number($account), SignInException::class, '手机号格式不正确!');
            $user = User::where('account', $account)->first();
            if (empty($user)) {
                $user = User::create([
                    'uuid'      => $uuid,
                    'account'   => $account,
                    'password'  => bcrypt('123456789'),
                    'name'      => User::DEFAULT_USER_NAME,
                    'api_token' => str_random(60),
                ]);
            }
        } else {
            $user = User::where('uuid', $uuid)->first();
            if (empty($user)) {
                //静默注册一个uuid为account的新账户
                $user = User::create([
                    'uuid'      => $uuid,
                    'account'   => $uuid,
                    'password'  => bcrypt('123456789'),
                    'name'      => User::DEFAULT_USER_NAME,
                    'api_token' => str_random(60),
                ]);
            }
        }
        Auth::login($user);
        return $user;
    }

    /**
     * 手动登录 - 密码
     * @param $account 手机号
     * @param $password 密码
     * @param $uuid 获取到的UUID，保留最新的
     */
    public static function signIn(string $account, string $password, string $uuid)
    {
        throw_if(!is_phone_number($account), SignInException::class, '手机号格式不正确!');
        $user = User::where('account', $account)->first();

        throw_if(empty($user), SignInException::class, '账号不存在,请先注册!');
        if (!password_verify($password, $user->password)) {
            throw new SignInException('登录失败,账号或者密码错误');
        }

        if (!empty($uuid) && !strcmp($user->uuid, $uuid)) {
            $user->update(['uuid' => $uuid]);
        }

        Auth::login($user);
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
        throw_if(!is_phone_number($account), SignInException::class, '手机号格式不正确!');
        throw_if(empty($sms_code), SignInException::class, '验证码不能为空!');

        $code = self::getLoginVerificationCode($account);

        if (empty($code) || !strcmp($code, $sms_code)) {
            throw new SignInException('验证码不正确!');
        }

        $user = User::where('account', $account)->first();
        throw_if(empty($user), SignInException::class, '账号不存在,请先注册~');

        //更新uuid
        if (!strcmp($user->uuid, $uuid)) {
            $user->update(['uuid' => $uuid]);
        }

        //登录
        Auth::login($user);
        return $user;
    }

    /**
     * 手动注册 - 一秒注册
     * @param $account 手机号
     * @param $password 密码
     * @param $uuid 获取到的UUID
     */
    public static function signUp(string $account, string $uuid, string $password)
    {
        throw_if(User::where('account', $account)->exists(), SignInException::class, '账号已存在');

        $user = User::create([
            'uuid'      => $uuid,
            'account'   => $account,
            'password'  => bcrypt($password),
            'name'      => User::DEFAULT_USER_NAME,
            'api_token' => str_random(60),
        ]);

        Auth::login($user);
        return $user;
    }

    /**
     * 手动注册 - 手机+验证码注册
     * @param $account 手机号
     * @param $uuid 获取到的UUID
     * @param $sms_code 手机验证码
     */
    public static function signUpWithSMSCode(string $account, string $uuid, string $sms_code)
    {
        throw_if(empty($sms_code), SignInException::class, '验证码不能为空!');

        $code = self::getLoginVerificationCode($account);

        if (!strcmp($code, $sms_code)) {
            throw_if(empty($sms_code), SignInException::class, '验证码不正确!');
        }

        throw_if(User::where('account', $account)->exists(), SignInException::class, '账号已存在');

        $user = User::create([
            'uuid'      => $uuid,
            'account'   => $account,
            'password'  => bcrypt('12345678'),
            'name'      => User::DEFAULT_USER_NAME,
            'api_token' => str_random(60),
        ]);

        Auth::login($user);
        return $user;
    }

    public static function getLoginVerificationCode($account, $action = VerificationCode::USER_LOGIN)
    {
        return VerificationCode::where('account', $account)
            ->byValid(VerificationCode::CODE_VALID_TIME)
            ->where('action', $action)
            ->latest('id')
            ->first();
    }
}
