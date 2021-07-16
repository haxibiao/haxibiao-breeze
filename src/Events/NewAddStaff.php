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
    public $user_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $staffUser, Int $user_id)
    {
        $this->staffUser = $staffUser;
        $this->user_id   = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if (isset($this->staffUser->id)) {
			if(in_array(config('app.name'),['haxibiao','yinxiangshipin','gql.dongyundong.com'])){
				return new PrivateChannel(config('app.name').'.User.' . $this->staffUser->id);
			}
			return new PrivateChannel('App.User.' . $this->staffUser->id);
        }
    }

    public function broadcastWith()
    {
        $staffUser = $this->staffUser;
        $user_id   = $this->user_id;
        $data = [
            'title'       => '添加员工账户消息提醒',
            'user_id'     => $staffUser->user_id,
            'user_name'   => $staffUser->name,
            'user_avatar' => $staffUser->avatar,
            'client_id'   => $user_id,
        ];
        return $data;
    }
}
