<?php

namespace haxibiao\user;

use App\Exceptions\GQLException;
use App\User;

/**
 * 一些通用的兼容resolvers的静态方法
 */
trait UserResolvers
{

    /**
     * 静默登录，uuid 必须传递，手机号可选
     */
    public function resolveAutoSignIn($root, array $args, $context, $resolveInfo)
    {

        $qb = User::where('uuid', $args['uuid']);

        // 不是首次登录
        if ($qb->exists()) {
            $user = $qb->first();
            if ($user->status === User::STATUS_OFFLINE) {
                throw new GQLException('登录失败！账户已被封禁');
            } else if ($user->status === User::STATUS_DESTORY) {
                throw new GQLException('登录失败！账户已被注销');
            }
        } else {
            $user = User::create([
                'uuid'      => $args['uuid'],
                'account'   => $args['phone'] ?? $args['uuid'],
                'name'      => User::DEFAULT_NAME,
                'api_token' => str_random(60),
                'avatar'    => User::AVATAR_DEFAULT,
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

        app_track_user('静默登录', "auto_signIn", $user->id);

        return $user;
    }

    /**
     * 注册
     */
    public function resolveSignUp($rootValue, array $args, $context, $resolveInfo)
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
            $name = $args['name'] ?? User::DEFAULT_NAME;
            return self::createUser($name, $account, $args['password']);
        }

        $email  = $args['email'];
        $exists = User::Where('email', $email)->exists();

        if ($exists) {
            throw new GQLException('该邮箱已经存在');
        }

        $user        = self::createUser(User::DEFAULT_NAME, $email, $args['password']);
        $user->phone = null;
        $user->email = $email;
        $user->save();

        app_track_user("用户注册", 'register');

        //FIXME: 记得兼容网警需要的IP跟踪
        // Ip::createIpRecord('users', $user->id, $user->id);
        return $user;
    }

    /**
     * 兼容哈希表邮箱登录的主动登录
     */
    public function resolveSignIn($rootValue, array $args, $context, $resolveInfo)
    {
        $account = $args['account'] ?? $args['email'];
        $qb      = User::where('phone', $account)->orWhere('email', $account)->orWhere('account', $account);
        if ($qb->exists()) {
            $user = $qb->first();
            if (!password_verify($args['password'], $user->password)) {
                throw new GQLException('登录失败！账号或者密码不正确');
            }

            if ($user->status === User::STATUS_OFFLINE) {
                throw new GQLException('登录失败！账户已被封禁');
            } else if ($user->status === User::STATUS_DESTORY) {
                throw new GQLException('登录失败！账户已被注销');
            }

            $user->touch(); //更新用户的更新时间来统计日活用户
            return $user;
        } else {
            throw new GQLException('登录失败！邮箱或者密码不正确');
        }
    }

    /**
     * 退出登录
     */
    public function resolveSignOut($rootValue, array $args, $context, $resolveInfo)
    {
        $user_id = $args['user_id'];
        return User::findOrFail($user_id);
    }

}
