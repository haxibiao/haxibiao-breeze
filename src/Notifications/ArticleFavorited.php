<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Content\Article;
use Illuminate\Bus\Queueable;

class ArticleFavorited extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "收藏了文章";

    protected $article;
    protected $user;

    public function __construct(Article $article, User $user)
    {
        $this->article = $article;
        $this->user    = $user;
    }

    public function toArray($notifiable)
    {
        return [
            'type'          => 'other',
            'user_avatar'   => $this->user->avatarUrl,
            'user_name'     => $this->user->name,
            'user_id'       => $this->user->id,
            'article_title' => $this->article->title,
            'article_id'    => $this->article->id,
            'message'       => $this->user->link() . "收藏了您的文章" . $this->article->link(),
        ];
    }
}
