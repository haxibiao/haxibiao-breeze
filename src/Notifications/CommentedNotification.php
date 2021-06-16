<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Post;
use Haxibiao\Sns\Comment;
use Illuminate\Bus\Queueable;

/**
 * 新评论通知
 */
class CommentedNotification extends BreezeNotification
{
    use Queueable;

    public static $data_action = "新评论";
    private $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->sender  = $comment->user;
    }

    public function toArray($notifiable)
    {
        //兼容旧通知数据格式
        $data = [
            'comment_id' => $this->comment->id,
        ];

        //互动用户
        $data = array_merge($data, $this->senderToArray());

        //互动对象
        $commentable = $this->comment->commentable;
        // - 评论了动态
        if ($commentable instanceof Post) {
            $post                   = $commentable;
            $this->data_description = $post->description;
            $this->data_cover       = $post->cover;
        }
        // - FIXME: 评论了电影/文章
        $data = array_merge($data, [
            'id'          => $this->comment->commentable_id,
            'type'        => $this->comment->commentable_type,
            'message'     => $this->comment->body, //评论
            'description' => $this->data_description, //对象的内容
            'cover'       => $this->data_cover, //内容的配图
        ]);

        return $data;
    }
}
