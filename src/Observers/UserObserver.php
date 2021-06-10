<?php

namespace Haxibiao\Breeze\Observers;

use App\Gold;
use Haxibiao\Breeze\User;

class UserObserver
{
    public function creating(User $user)
    {
        //修复nova新增用户问题
        if (blank($user->api_token)) {
            $user->api_token = str_random(60);
        }

    }

    public function created(User $user)
    {
        Gold::makeIncome($user, Gold::NEW_USER_GOLD, Gold::NEW_USER_REWARD_REASON);
    }

    public function updated(User $user)
    {
        $user->reviewTasksByClass(get_class($user));
    }

}
