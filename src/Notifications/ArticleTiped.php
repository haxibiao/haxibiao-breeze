<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Sns\Tip;
use Illuminate\Bus\Queueable;

class ArticleTiped extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "打赏了文章";

    protected $article;
    protected $user;
    protected $tip;

    public function __construct($article, User $user, Tip $tip)
    {
        $this->article = $article;
        $this->user    = $user;
        $this->tip     = $tip;
    }

    public function toArray($notifiable)
    {
        $article_title = $this->article->title ?: $this->article->video->title;
        // 标题 视频标题都不存在 则取description
        if (empty($article_title)) {
            $article_title = $this->article->summary;
        }
        return [
            'type'          => 'tip',
            'amount'        => $this->tip->amount,
            'tip_id'        => $this->tip->id,
            'message'       => $this->tip->message,
            'user_name'     => $this->user->name,
            'user_avatar'   => $this->user->avatarUrl,
            'user_id'       => $this->user->id,
            'article_title' => $article_title,
            'article_id'    => $this->article->id,
        ];
    }
}
