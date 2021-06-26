<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Post;
use Haxibiao\Sns\Like;
use Illuminate\Bus\Queueable;

class LikedNotification extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "点了个赞";
    protected $like;

    public function __construct(Like $like)
    {
        $this->like   = $like;
        $this->sender = $like->user;
    }

    public function toArray($notifiable)
    {
        if ($this->like->likable_type == 'comments') {
            $this->custom_event = '赞了你的评论';

            //提取评论的内容对象
            $this->data_message = $this->like->likable->body;
            $this->data_type    = $this->like->likable->commentable_type;
            $this->data_id      = $this->like->likable->commentable_id;

            //为网页跳转提取URL
            $lou         = $this->like->likable->lou;
            $commentable = $this->like->likable->commentable;
            if ($this->data_type === 'answers') {
                $question = $commentable->question;
                $url      = '/issue/' . $question->id;
            } else {
                $url = $commentable->url . '#' . $lou;
            }

        } else {
            $this->custom_event     = '喜欢了你的' . $this->like->likable->resoureTypeCN();
            $this->data_description = $this->like->likable->title;
            $this->data_id          = $this->like->likable_id;
            $this->data_type        = $this->like->likable_type;
            $url                    = $this->like->likable->url;

            //完善新通知结构配图
            if ($this->like->likable instanceof Post) {
                $post                   = $this->like->likable;
                $this->data_description = $post->description;
                $this->data_cover       = $post->cover;
            }
        }

        //兼容旧web结构的
        $data = [
            'likeable_type' => $this->data_type,
            'url'           => $url,
            'title'         => $this->data_message,
            'body'          => $this->custom_event,
        ];

        //互动用户
        $data = array_merge($data, $this->senderToArray());

        //互动对象
        $data = array_merge($data, [
            'type'        => $this->data_type,
            'id'          => $this->data_id,
            'message'     => $this->data_message,
            'description' => $this->data_description, //对象的内容
            'cover'       => $this->data_cover,
            'event'       => $this->custom_event,
        ]);

        return $data;
    }
}
