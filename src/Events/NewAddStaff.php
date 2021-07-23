<?php

namespace Haxibiao\Breeze\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewAddStaff
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $staffUser;
    public $user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $staffUser, User $user)
    {
        $this->staffUser = $staffUser;
        $this->user   = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if (isset($this->staffUser->id)) {
			return new PrivateChannel('App.User.' . $this->staffUser->id);
        }
    }

    public function broadcastWith()
    {
        $staffUser = $this->staffUser;
        $user      = $this->user;
        $message   = "成为员工账户消息通知:【$staffUser->name】";
        $data =  [
            'messgae'   => $message,
            'id'        => $user->id, //上级用户id
            'title'     => $user->name, //上级用户昵称
            'cover'     => $user->avatar, //上级用户头像
            'type'      => $staffUser->getMorphClass(),
        ];
        return $data;
    }
}
