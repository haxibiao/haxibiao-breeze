<?php

namespace haxibiao\users\Auth;

use App\Exceptions\SignInException;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * 依赖 app_track_user_event 触发事件
 */
trait AuthHelper
{

    /**
     * UUID登录（通过设备唯一识别码）
     * 依赖:
     * - Attr: user->disable
     * @param $method 闭包函数
     */
    public static function autoSignIn(string $uuid, array $createData = null, $method = null)
    {
        app_track_user_event("一键登录", "一键登录");
        $user = User::where('uuid', $uuid)->first();

        throw_if(optional($user)->disable, SignInException::class, '账户异常~');
        if (empty($user)) {
            $user = User::create(array_merge(['uuid' => $uuid], $createData));
        }

        $user = self::loadRelation($user);
        if ($method instanceof Closure) {
            $method->call($user, func_get_args());
        }
        return $user;
    }

    /**
     * 通过account登录
     * 如果登录账户的设备ID与传入设备ID不同,则会更新登录账户设备ID
     * @param $method 闭包函数
     */
    public static function signIn(string $account, string $uuid, $method = null)
    {
        app_track_user_event("手动登录", "手动登录");
        $user = User::where('account', $account);

        throw_if(empty($user), SignInException::class, '账号不存在,请先注册~');
        throw_if($user->disable, SignInException::class, '账户异常~');

        if (!strcmp($user->uuid, $uuid)) {
            $user->update(['uuid' => $uuid]);
        }
        $user = self::loadRelation($user);
        if ($method instanceof Closure) {
            $method->call($user, func_get_args());
        }
        return $user;
    }

    /**
     * 注册
     * 依赖:
     * - Attr: user->disable
     */
    public static function signUp(string $account, string $uuid, array $createData = null)
    {
        app_track_user_event("手动注册", "手动注册");
        $signUpData = array_merge($createData, [
            'account' => $account,
            'uuid'    => $uuid,
        ]);
        return User::create($signUpData);
    }

    /**
     * 预加载常用关系
     * * 依赖:
     * - Relation: profile,wallet
     */
    public static function loadRelation(User $user)
    {
        $user->load('profile');
        $user->load('wallet');
        Auth::login($user);
        return $user;
    }
}
