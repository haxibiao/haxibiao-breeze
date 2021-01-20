<?php

namespace Haxibiao\Breeze\Listeners;

use Haxibiao\Breeze\Events\NewMessage;
use Haxibiao\Breeze\Notifications\ChatNewMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewMessageNotification implements ShouldQueue
{

    public function __construct()
    {
        //
    }

    public function handle(NewMessage $event)
    {
        $message = $event->message;
        $chat    = $event->message->chat;
        foreach ($chat->users as $user) {
            $user->notify(new ChatNewMessage($message));
        }
    }
}
