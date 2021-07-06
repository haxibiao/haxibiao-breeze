<?php
namespace Haxibiao\Breeze\Events;

use Haxibiao\Task\Medal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewMedal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $medal;
    public $user_id;
    public function __construct(Medal $medal, Int $user_id)
    {
        $this->medal   = $medal;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
		if(in_array(config('app.name'),['haxibiao','yinxiangshipin'])){
			return new PrivateChannel(config('app.name').'.User.' . $this->user_id);
		}
        return new PrivateChannel('App.User.' . $this->user_id);
    }

    public function broadcastWith()
    {
        $data = [
            'title' => '恭喜获得新成就',
            'name'  => $this->medal->name_cn,
            'icon'  => $this->medal->done_icon_url,
            'id'    => $this->medal->id,
        ];
        return $data;
    }

}
