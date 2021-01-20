<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Sns\Notice;

class NoticeObserver
{
    public function creating(Notice $notice)
    {
        //默认发表用户为Root
        $notice->user_id = auth()->id() ?? 1;
    }
}
