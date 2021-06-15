<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Sns\Comment;
use Haxibiao\Sns\Feedback;
use Illuminate\Bus\Queueable;

class FeedbackCommentNotification extends BreezeNotification
{
    use Queueable;

    public static $notify_action = "反馈被评论";
    protected $comment           = null;
    protected $feedback          = null;

    public function __construct(Feedback $feedback, Comment $comment)
    {
        $this->feedback = $feedback;
        $this->comment  = $comment;
        $this->sender   = $comment->user;
    }

    public function toArray($notifiable)
    {
        //兼容旧morph数据
        $data = $this->comment->notifyToArray([
            'feedback_id' => $this->feedback->id,
        ]);

        $data = array_merge($data,
            //通知互动的用户
            $this->senderToArray(),
            [
                //新版通知互动对象的context
                'id'          => $this->feedback->id,
                'type'        => 'feedback',
                'title'       => $this->comment->body,
                'description' => $this->feedback->content,
                //反馈评论，图片就不勉强先
            ]);

        return $data;
    }
}
