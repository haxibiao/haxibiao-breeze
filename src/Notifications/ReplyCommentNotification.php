<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Sns\Comment;
use Illuminate\Bus\Queueable;

/**
 * 评论被回复通知
 */
class ReplyCommentNotification extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "回复了评论";
    protected $comment; //评论
    protected $reply; //楼中楼回复
    protected $sender;

    public function __construct(Comment $comment)
    {
        $this->reply = $comment;
        //楼中楼回复的评论
        $this->comment = $this->reply->commentable;
        $this->sender  = $comment->user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        //通知中的消息文本
        $this->data_message = $this->reply->body;
        $comment            = $this->comment;
        //通知中的配文
        $this->data_description = $comment->body;

        //兼容web在用的data
        $data = [
            'reply_content' => $this->reply->getContent(),
            'reply_id'      => $this->reply->id,
            'comment_id'    => $comment->id,
            'comment_body'  => $comment->body,
        ];

        //互动用户
        $data = array_merge($data, $this->senderToArray());

        //评论中的内容
        $commentable = $comment->commentable;
        if ($commentable) {
            //内容的封面
            $this->data_cover = $commentable->cover;
        }

        // - FIXME: 评论了电影/文章
        $data = array_merge($data, [
            'id'          => $this->comment->commentable_id,
            'type'        => $this->comment->commentable_type,
            'message'     => $this->data_message, //评论消息
            'description' => $this->data_description, //评论的内容
            'cover'       => $this->data_cover, //内容的配图
        ]);

        return $data;
    }
}
