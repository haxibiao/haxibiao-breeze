<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Wallet\Withdraw;

class WithdrawObserver
{
    /**
     * Handle the withdraw "created" event.
     *
     * @param  \App\Withdraw  $withdraw
     * @return void
     */
    public function created(Withdraw $withdraw)
    {
        $wallet = $withdraw->wallet;
        $user   = $wallet->user;
        $user->withdrawAt();
    }

    /**
     * Handle the withdraw "updated" event.
     *
     * @param  \App\Withdraw  $withdraw
     * @return void
     */
    public function updated(Withdraw $withdraw)
    {
        $withdraw->syncData();
        $user = $withdraw->user;
        if (!is_null($user)) {
            $user->profile->syncWithdrawCount();
            $user->reviewTasksByClass(get_class($withdraw));
        }

        $user->reviewTasksByClass(get_class($withdraw));
    }

}
