<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Issue;
use Illuminate\Bus\Queueable;

class QuestionBonused extends BreezeNotification
{
    use Queueable;
    public static $notify_event = "奖励了问题";
    protected $user;
    protected $issue;

    public function __construct(User $user, Issue $issue)
    {
        $this->user  = $user;
        $this->issue = $issue;
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'other',
            'subtype'     => 'question_bonused',
            'question_id' => $this->issue->id,
            'message'     => $this->user->link() . '奖励了您回答的问题' . $this->issue->link(),
        ];
    }
}
