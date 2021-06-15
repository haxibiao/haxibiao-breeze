<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Issue;
use Illuminate\Bus\Queueable;

class QuestionAnswered extends BreezeNotification
{
    use Queueable;
    public static $notify_action = "提问被回答";
    protected $user;
    protected $issue;

    public function __construct($user_id, $issue_id)
    {
        $this->user  = User::find($user_id);
        $this->issue = Issue::find($issue_id);
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'other',
            'subtype'     => 'question_answered',
            'question_id' => $this->issue->id,
            'message'     => $this->user->link() . '回答了您的问题' . $this->issue->link(),
        ];
    }
}
