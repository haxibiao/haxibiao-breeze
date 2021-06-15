<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Post;
use Haxibiao\Sns\Comment;
use Illuminate\Bus\Queueable;

/**
 * 评论被回复通知
 */
class ReplyCommentNotification extends BreezeNotification
{
    use Queueable;

    public static $notify_action = "评论被回复";
    protected $reply;
    protected $sender;

    public function __construct(Comment $comment)
    {
        $this->reply  = $comment;
        $this->sender = $comment->user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        //兼容旧互动content morph实现
        $comment = $this->reply->commentable;
        $data    = [
            'reply_content' => $this->reply->getContent(),
            'reply_id'      => $this->reply->id,
            'comment_id'    => $comment->id,
            'comment_body'  => $comment->body,
        ];

        //互动用户
        $data = array_merge($data, $this->senderToArray());

        //互动对象
        $commentable = $this->comment->commentable;
        // - 评论了动态
        if ($commentable instanceof Post) {
            $this->notify_description = $commentable->description;
            $this->notify_cover       = $commentable->cover;
        }
        // - FIXME: 评论了电影/文章
        $data = array_merge($data, [
            'id'          => $this->comment->commentable_id,
            'type'        => $this->comment->commentable_type,
            'title'       => $this->comment->body, //评论
            'description' => $this->notify_description, //评论的内容
            'cover'       => $this->notify_cover, //内容的配图
        ]);
    }
}
