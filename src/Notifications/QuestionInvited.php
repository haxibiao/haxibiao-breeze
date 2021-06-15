<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Issue;
use Illuminate\Bus\Queueable;

class QuestionInvited extends BreezeNotification
{
    use Queueable;

    public static $notify_action = "邀请回答";
    protected $user;
    protected $issue;

    public function __construct($user_id, $question_id)
    {
        $this->user  = User::find($user_id);
        $this->issue = Issue::find($question_id);
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'other',
            'subtype'     => 'question_invite',
            'question_id' => $this->issue->id,
            'message'     => $this->user->link() . '邀请了您去回答问题:' . $this->issue->link(),
        ];
    }
}
