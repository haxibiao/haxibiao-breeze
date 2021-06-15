<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Article;
use Haxibiao\Content\Category;
use Illuminate\Bus\Queueable;

class ArticleApproved extends BreezeNotification
{
    use Queueable;

    public static $notify_action = "收录了文章";

    protected $article;
    protected $category;
    protected $approve_status;

    public function __construct(Article $article, Category $category, $approve_status)
    {
        $this->article        = $article;
        $this->category       = $category;
        $this->approve_status = $approve_status;
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'other',
            'subtype'     => 'article_approve',
            'article_id'  => $this->article->id,
            'category_id' => $this->category->id,
            'message'     => $this->category->link() . $this->approve_status . $this->article->link(),
        ];
    }
}
