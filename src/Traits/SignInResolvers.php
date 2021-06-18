<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/8
 * @Version
 */

namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\SignIn;
use Illuminate\Support\Arr;

trait SignInResolvers
{

    public function resolveSignIn($root, array $args, $context, $info): SignIn
    {
        $user   = getUser();
        $signIn = $user->signIns()->where('created_at', '>=', today())->first();
        if (!is_null($signIn)) {
            throw new UserException('已经签到过了,请勿重复签到哦!');
        }
        //FIXME: SignIn model 需要全部改名为 CheckIn, 表名也需要改
        return SignIn::checkIn($user);
    }

    public function resolveSignIns($root, array $args, $context, $info)
    {
        $user = getUser();
        $days = Arr::get($args, 'days', 7);
        return SignIn::getSignIns($user, $days);
    }
}
