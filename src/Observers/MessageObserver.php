<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Breeze\Events\NewMessage;
use Haxibiao\Sns\Message;

class MessageObserver
{

    public function created(Message $message)
    {
        broadcast(new NewMessage($message))->toOthers();
    }

    public function updated(Message $message)
    {
        //
    }

    public function deleted(Message $message)
    {
        //
    }

    public function restored(Message $message)
    {
        //
    }

    public function forceDeleted(Message $message)
    {
        //
    }
}
