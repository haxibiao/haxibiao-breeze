<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Breeze\User;
use Haxibiao\Wallet\Gold;

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
