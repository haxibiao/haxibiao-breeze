<?php

namespace Haxibiao\Breeze\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewAddAssociate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $senderId;
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, Int $senderId,String $message)
    {
        $this->user   = $user;
        $this->senderId = $senderId;
        $this->message  = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if (isset($this->user->id)) {
            return new PrivateChannel(config('app.name') . '.User.' . $this->user->id);
        }
    }

    public function broadcastWith()
    {
        $user = $this->user;
        $senderId = $this->senderId;
        $message  = $this->message;
        $sender = User::find($senderId);
        $data = [
            'title'     => '用户申请成为您的员工(客户)消息提醒',
            'message'   => "用户留言:$message",
            'recipient' => "接收者:$user->id",
            'sender'    => "发起用户:$senderId",
            'sender_name' => "发起者昵称:$sender->name",
            'sender_avatar' => "发起者头像:$sender->avatar",
        ];
        return $data;
    }
}
