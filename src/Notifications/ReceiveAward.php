<?php

namespace Haxibiao\Breeze\Notifications;

use Illuminate\Bus\Queueable;

class ReceiveAward extends BreezeNotification
{
    use Queueable;
    public static $notify_event = "获得奖励";
    protected $subject;
    protected $gold;
    protected $sender;
    protected $article_id;

    public function __construct($subject, $gold, $sender, $article_id)
    {
        $this->subject    = $subject;
        $this->gold       = $gold;
        $this->sender     = $sender;
        $this->article_id = $article_id;
    }

    public function toArray($notifiable)
    {
        return [
            'type'       => 'today_publish_post_receive_award',
            'subject'    => $this->subject,
            'gold'       => $this->gold,
            'user_id'    => $this->sender->id,
            'article_id' => $this->article_id,
        ];
    }
}
