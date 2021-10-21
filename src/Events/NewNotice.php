<?php
namespace Haxibiao\Breeze\Events;

use App\Notice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewNotice implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $notice;
    public $user_id;
    public function __construct($notice, Int $user_id)
    {
        $this->notice  = $notice;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if (in_array(config('app.name'), ['haxibiao', 'yinxiangshipin'])) {
            return new PrivateChannel(config('app.name') . '.User.' . $this->user_id);
        }
        return new PrivateChannel('App.User.' . $this->user_id);
    }

    public function broadcastWith()
    {
        return [
            'title'   => $this->notice->title,
            'content' => $this->notice->content,
            'id'      => $this->notice->id,
        ];
    }

}
