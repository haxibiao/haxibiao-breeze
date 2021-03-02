<?php

namespace Haxibiao\Breeze\Listeners;

use App\Follow;
use Haxibiao\Breeze\Events\NewFollow;
use Haxibiao\Breeze\Notifications\UserFollowed;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewFollowNotification implements ShouldQueue
{
    public $delay = 60 * 10;
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

        $is_deFollow = Follow::where([
            'user_id'         => $follow->user_id,
            'followable_type' => $follow->followable_type,
            'followable_id'   => $follow->followable_id,
        ])->exists();

        if ($follow->followable instanceof \App\User) {
            if (!$is_deFollow) {
                $follow->followable->notify(new UserFollowed($user));
            }
        }

    }
}
