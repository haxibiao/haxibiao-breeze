<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Issue;
use Illuminate\Bus\Queueable;

class QuestionInvited extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "邀请了回答";
    protected $sender;
    protected $issue;

    public function __construct($user_id, $question_id)
    {
        $this->sender= User::find($user_id);
        $this->issue = Issue::find($question_id);
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'other',
            'subtype'     => 'question_invite',
            'question_id' => $this->issue->id,
            'message'     => $this->sender->link() . '邀请了您去回答问题:' . $this->issue->link(),
        ];
    }
}
