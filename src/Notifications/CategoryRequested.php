<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Content\Article;
use Haxibiao\Content\Category;
use Illuminate\Bus\Queueable;

/**
 * 作用:投稿请求的通知类型
 * 注意这个通知类型已经被弃用了
 */
class CategoryRequested extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "投稿了专题";

    protected $category;
    protected $article;

    public function __construct(Category $category, Article $article)
    {
        $this->category = $category;
        $this->article  = $article;
    }

    public function toArray($notifiable)
    {
        return [
            'type'                  => 'category_request',
            'category_id'           => $this->category->id,
            'user_id'               => $this->article->user->id,
            'user_name'             => $this->article->user->name,
            'user_avatar'           => $this->article->user->avatarUrl,
            'article_id'            => $this->article->id,
            'article_title'         => $this->article->title,
            'article_description'   => $this->article->description,
            'article_image_url'     => $this->article->cover,
            'article_hits'          => $this->article->hits,
            'article_count_replies' => $this->article->count_replies,
            'article_count_likes'   => $this->article->count_likes,
            'article_count_tips'    => $this->article->count_tips,
        ];
    }
}
