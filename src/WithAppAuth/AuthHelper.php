<?php

namespace Haxifang\Users\WithAuth;

use App\User;

trait AuthHelper
{

    /**
     * UUID登录（通过设备唯一识别码）
     */
    public static function autoSignIn(string $uuid)
    {
        return User::where('uuid', $uuid)->first();
    }

    /**
     * UUID注册（可传递其他数据作为注册默认数据）
     */
    public static function autoSignUp(string $uuid, array $createData = null)
    {
        return User::crate(array_merge(['uuid' => $uuid], $createData));
    }

    /**
     * 通过account登录,如果登录账户的设备ID与传入设备ID不同,则会更新登录账户设备ID
     */
    public static function signIn(string $account, string $uuid)
    {
        $user = User::where('account', $account);
        if ($user) {
            if (!strcmp($user->uuid, $uuid)) {
                $user->update(['uuid' => $uuid]);
            }
            return $user;
        }
    }

    /**
     * 注册
     */
    public static function signUp(string $account, string $uuid, array $createData = null)
    {
        $signUpData = array_merge($createData, [
            'account' => $account,
            'uuid'    => $uuid,
        ]);
        return User::create($signUpData);
    }
}
