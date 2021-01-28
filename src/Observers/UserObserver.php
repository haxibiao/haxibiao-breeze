<?php

namespace Haxibiao\Breeze\Observers;

use App\Gold;
use Haxibiao\Breeze\User;

class UserObserver
{
    /**
     * @param $user
     */
    public function created($user)
    {
        Gold::makeIncome($user, Gold::NEW_USER_GOLD, Gold::NEW_USER_REWARD_REASON);
    }
    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        $user->reviewTasksByClass(get_class($user));
    }

}
