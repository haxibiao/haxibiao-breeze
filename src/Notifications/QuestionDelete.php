<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Issue;
use Illuminate\Bus\Queueable;

class QuestionDelete extends BreezeNotification
{
    use Queueable;
    public static $notify_event = "删除了问题";

    protected $issue;

    public function __construct(Issue $issue)
    {
        $this->issue = $issue;
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'other',
            'subtype'     => 'question_answered',
            'question_id' => $this->issue->id,
            'message'     => '您的付费问题' . $this->issue->title . $this->issue->result(),
        ];
    }
}
