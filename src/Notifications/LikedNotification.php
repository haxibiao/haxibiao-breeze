<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Sns\Like;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LikedNotification extends Notification
{
    use Queueable;

    protected $like;
    protected $sender;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Like $like)
    {
        $this->like   = $like;
        $this->sender = $like->user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $isSelf = $notifiable->id == $this->sender->id;
        if ($isSelf) {
            return [];
        }
        $notification = $notifiable->notifications()
        ->whereType('App\Notifications\LikedNotification')
        ->where('data->likeable_type', $this->like->likable_type)
        ->where('data->id', $this->like->likable_id)
        ->where('data->user_id', $this->like->user_id)
        ->first();
        if ($notification) {
            return [];
        }
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
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
            }else{
                $url   = $commentable->url . '#' . $lou;
                $title = $comment_body;
            }
        }else{
            $body  = '喜欢了你的' . $this->like->likable->resoureTypeCN();
            $url   = $this->like->likable->url;
            $title = '《' . $this->like->likable->title . '》';
        }
        return [
            'type'          => 'likes',
            'id'            => $this->like->likable_id,
            'likeable_type' => $this->like->likable_type,
            'user_avatar'   => $this->sender->avatarUrl,
            'user_name'     => $this->sender->name,
            'user_id'       => $this->sender->id,
            'url'           => $url,
            'body'          => $body,
            'title'         => $title,
        ];
    }
}
