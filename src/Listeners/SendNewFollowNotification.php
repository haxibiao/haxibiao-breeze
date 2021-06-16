<?php

namespace Haxibiao\Breeze\Listeners;

use Haxibiao\Breeze\Events\NewFollow;
use Haxibiao\Breeze\Notifications\UserFollowed;
use Haxibiao\Sns\Follow;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewFollowNotification implements ShouldQueue
{
    public $afterCommit = true;
    public function __construct()
    {
        //

    }

    /**
     * Handle the event.
     *
     * @param  NewFollow  $event
     * @return void
     */
    public function handle(NewFollow $event)
    {

        //TODO:: 汇总新关注通知
        $follow = $event->follow;
        $user   = $follow->user;

        if ($follow->followable instanceof \App\User) {
            $follow->followable->notify(new UserFollowed($user));
        }

    }
}
