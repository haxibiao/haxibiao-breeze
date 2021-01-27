<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Wallet\Gold;

class GoldObserver
{
    public function created(Gold $gold)
    {
        //更新user表上的冗余字段
        $user = $gold->user;
        $user->update(['gold' => $gold->balance]);

        //更新任务状态 - 需要更新任务状态的金币变化
        $need_check_task = !in_array($gold->remark, [Gold::NEW_USER_REWARD_REASON]);
        if ($need_check_task) {
            $user->reviewTasksByClass(get_class($gold));
        }

    }
}
