<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Sns\Message;
use Illuminate\Bus\Queueable;

class ChatNewMessage extends BreezeNotification
{
    use Queueable;

    public static $notify_action = "新私信";

    public $message;
    public $user;
    public $chat;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->user    = $message->user;
        $this->chat    = $message->chat;
    }

    public function toArray($notifiable)
    {
        return [
            'message_content' => $this->message->message,
            'chat_id'         => $this->chat->id,
            'user_id'         => $this->user->id,
        ];
    }
}
