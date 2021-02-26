<?php

namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\User;
use Illuminate\Support\Str;

/**
 * 一些通用的兼容resolvers的静态方法 - 目前工厂APP在用...
 * //FIXME: 待重构
 */
trait AuthResolvers
{

    /**
     * 静默登录，uuid 必须传递，手机号可选
     */
    public static function resolveAutoSignIn($root, array $args, $context, $info)
    {

        $qb = User::where('uuid', $args['uuid']);

        // 不是首次登录
        if ($qb->exists()) {
            $user = $qb->first();
            if (User::STATUS_OFFLINE === $user->status) {
                throw new GQLException('登录失败！账户已被封禁');
            } else if (User::STATUS_DESTORY === $user->status) {
                throw new GQLException('登录失败！账户已被注销');
            }
        } else {
            $user = User::create([
                'uuid'      => $args['uuid'],
                'account'   => $args['phone'] ?? $args['uuid'],
                'name'      => config('auth.default_name', '匿名用户'),
                'api_token' => Str::random(60),
                'avatar'    => config('auth.default_avatar'),
            ]);
            $user->name = $user->name . $user->id;
            $user->save();

            //FIXME: 确保user->profile lazy load会修复这个数据
            // Profile::create([
            //     'user_id'      => $user->id,
            //     'introduction' => '这个人暂时没有 freestyle ',
            //     'app_version'  => request()->header('version', null),
            // ]);

            //FIXME: 记得兼容网警需要的IP跟踪
            // Ip::createIpRecord('users', $user->id, $user->id);
        }
        $user->updateProfileAppVersion($user);
        return $user;
    }

    /**
     * 注册
     */
    public static function resolveSignUp($root, array $args, $context, $info)
    {

        if (isset($args['account'])) {

            $account = $args['account'];

            $exists = User::where('phone', $account)->orWhere('account', $account)->exists();
            //手机号格式验证
            $flag = preg_match('/^[1](([3][0-9])|([4][5-9])|([5][0-3,5-9])|([6][5,6])|([7][0-8])|([8][0-9])|([9][1,8,9]))[0-9]{8}$/', $account);
            if (!$flag) {
                throw new GQLException('注册失败，手机号格式不正确，请检查是否输入正确');
            }

            if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $args['password'])) {
                throw new GQLException('密码中不能包含中文');
            }

            if ($exists) {
                throw new GQLException('该账号已经存在');
            }
            $name = $args['name'] ?? config('auth.default_name', '匿名用户');
            return self::createUser($name, $account, $args['password']);
        }

        $email  = $args['email'];
        $exists = \App\User::Where('email', $email)->exists();

        if ($exists) {
            throw new GQLException('该邮箱已经存在');
        }

        $user        = self::createUser(config('auth.default_name'), $email, $args['password'], '匿名用户');
        $user->phone = null;
        $user->email = $email;
        $user->save();

        //FIXME: 记得兼容网警需要的IP跟踪
        // Ip::createIpRecord('users', $user->id, $user->id);
        return $user;
    }

    /**
     * 兼容哈希表邮箱登录的主动登录
     */
    public static function resolveSignIn($root, $args, $context, $info)
    {
        $account = $args['account'] ?? $args['email'];
        $qb      = User::where('phone', $account)->orWhere('email', $account)->orWhere('account', $account);
        if ($qb->exists()) {
            $user = $qb->first();
            if (!password_verify($args['password'], $user->password)) {
                throw new GQLException('登录失败！账号或者密码不正确');
            }

            if (User::STATUS_OFFLINE === $user->status) {
                throw new GQLException('登录失败！账户已被封禁');
            } else if (User::STATUS_DESTORY === $user->status) {
                throw new GQLException('登录失败！账户已被注销');
            }

            $user->touch(); //更新用户的更新时间来统计日活用户
            return $user;
        } else {
            throw new GQLException('登录失败！邮箱或者密码不正确');
        }
    }

}
