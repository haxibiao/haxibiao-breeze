<?php

namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\CheckIn;
use Haxibiao\Breeze\Exceptions\UserException;
use Illuminate\Support\Arr;

trait CheckInResolvers
{

    public function resolveStore($root, array $args, $context, $info): CheckIn
    {
        $user   = getUser();
        $signIn = $user->checkIns()->where('created_at', '>=', today())->first();
        if (!is_null($signIn)) {
            throw new UserException('已经签到过了,请勿重复签到哦!');
        }
        return CheckIn::checkIn($user);
    }

    public function resolveSignIns($root, array $args, $context, $info)
    {
        if ($user = currentUser()) {
            $days = Arr::get($args, 'days', 7);
            return CheckIn::getSignIns($user, $days);
        }
    }
}
