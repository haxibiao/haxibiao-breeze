<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Task\Contribute;

class ContributeObserver
{
    /**
     * Handle the contribute "created" event.
     *
     * @param  \App\Contribute  $contribute
     * @return void
     */
    public function created(Contribute $contribute)
    {
        //更新user表上的冗余字段
        $user = $contribute->user;
        //更新任务状态
        $user->reviewTasksByClass(get_class($contribute));
    }
}
