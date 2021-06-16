<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Post;
use Haxibiao\Sns\Like;
use Illuminate\Bus\Queueable;

class LikedNotification extends BreezeNotification
{
    use Queueable;

    public static $data_action = "新点赞";
    protected $like;

    public function __construct(Like $like)
    {
        $this->like   = $like;
        $this->sender = $like->user;
    }

    public function toArray($notifiable)
    {
        if ($this->like->likable_type == 'comments') {
            $body = '赞了你的评论';
            //评论文章，视频，答案，动态
            $lou          = $this->like->likable->lou;
            $comment_body = $this->like->likable->body;
            $commentable  = $this->like->likable->commentable;
            //评论问答中的答案
            if ($this->like->likable->commentable_type == 'answers') {
                $question = $commentable->question;
                $url      = '/issue/' . $question->id;
                $title    = $comment_body;
            } else {
                $url   = $commentable->url . '#' . $lou;
                $title = $comment_body;
            }
        } else {
            $body  = '喜欢了你的' . $this->like->likable->resoureTypeCN();
            $url   = $this->like->likable->url;
            $title = '《' . $this->like->likable->title . '》';

            //完善新通知结构配图
            if ($this->like->likable instanceof Post) {
                $post                   = $this->like->likable;
                $this->data_description = $post->description;
                $this->data_cover       = $post->cover;
            }
        }

        //兼容旧结构的
        $data = [
            'likeable_type' => $this->like->likable_type,
            'url'           => $url,
            'title'         => $title,
            'body'          => $body,
        ];

        //互动用户
        $data = array_merge($data, $this->senderToArray());

        //互动对象
        $data = array_merge($data, [
            'type'        => $this->like->likable_type,
            'id'          => $this->like->likable_id,
            'description' => $this->data_description, //对象的内容
            'cover'       => $this->data_cover,
        ]);

        return $data;
    }
}
