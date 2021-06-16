<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Post;
use Haxibiao\Sns\Comment;
use Illuminate\Bus\Queueable;

/**
 * 评论被采纳的通知
 */
class CommentAccepted extends BreezeNotification
{
    use Queueable;
    public static $notify_event = "采纳了评论";
    protected $comment;
    protected $sender;

    public function __construct(Comment $comment, User $sender)
    {
        $this->comment = $comment;
        $this->sender  = $sender;
    }

    public function toArray($notifiable)
    {
        $data = [
            //旧版本morph关系
            'comment_id' => $this->comment->id,
            'article_id' => $this->comment->commentable_id,
        ];

        //互动用户
        $data = array_merge($data, $this->senderToArray());

        //互动对象
        $commentable = $this->comment->commentable;
        // - 评论了动态
        if ($commentable instanceof Post) {
            $this->data_description = $commentable->description;
            $this->data_cover       = $commentable->cover;
        }
        // - FIXME: 评论了电影/文章
        $data = array_merge($data, [
            'id'          => $this->comment->commentable_id,
            'type'        => $this->comment->commentable_type,
            'message'     => $this->comment->body, //评论
            'description' => $this->data_description, //评论的内容
            'cover'       => $this->data_cover, //内容的配图
        ]);
        return $data;

    }
}
