<?php

namespace Haxibiao\Breeze\Listeners;

use Haxibiao\Breeze\Events\NewLike;
use Haxibiao\Breeze\Notifications\LikedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewLikeNotification implements ShouldQueue
{

    public $afterCommit = true;

    public function __construct()
    {

    }

    /**
     * ivan(2019-10-15): 监听喜欢点赞操作的其他响应.
     * LikeObserver 应该已经完成了基本的数据更新触发
     * 这里可以用来实现一些复杂的 job逻辑，比如 延迟发送通知，聚合发送点赞通知 ...
     * @param  NewLike  $event
     * @return void
     */
    public function handle(NewLike $event)
    {

        $like = $event->like;
        $likable    = $like->likable;

        if (!is_null($likable)) {
            $likableUser = $likable->user;
            if (!is_null($likableUser)) {
                $likable->user->notify(new LikedNotification($like));
            }
        }
    }
}
