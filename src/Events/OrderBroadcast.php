<?php
namespace Haxibiao\Breeze\Events;

use Haxibiao\Store\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class OrderBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $order;
    public $user_id;
    public function __construct(Order $order, Int $user_id)
    {
        $this->order   = $order;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if (in_array(config('app.name'), ['haxibiao', 'yinxiangshipin', 'yingdaquan'])) {
            return new PrivateChannel(config('app.name') . '.User.' . $this->user_id);
        }
        return new PrivateChannel('App.User.' . $this->user_id);
    }

    public function broadcastWith()
    {
        $data = [
            'title'  => '订单状态发生变化',
            'status' => $this->order->status,
            'id'     => $this->order->id,
        ];
        return $data;
    }

}
