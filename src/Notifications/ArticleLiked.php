<?php

namespace Haxibiao\Breeze\Notifications;

use App\Article;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * 问答，文章，视频，动态的评论全都由本类负责通知
 */
class ArticleLiked extends BreezeNotification implements ShouldQueue
{
    use Queueable;

    public static $notify_event = "点赞了文章";

    protected $article;
    protected $user;
    protected $comment;

    public function __construct($article_id, $user_id, $comment = null)
    {
        $this->article = Article::find($article_id);
        $this->user    = User::find($user_id);
        $this->comment = $comment;
    }

    public function toArray($notifiable)
    {
        $article_title = $this->article->title;
        // 标题不存在 则取description
        if (empty($article_title)) {
            $article_title = $this->article->summary;
        }

        if (!empty($this->comment)) {
            $body = '赞你的评论';
            //评论文章，视频，答案，动态
            $lou          = $this->comment->lou;
            $comment_body = $this->comment->body;
            $commentable  = $this->comment->commentable;
            //评论问答中的答案
            if ($this->comment->commentable_type == 'answers') {
                $question = $commentable->question;
                $url      = '/question/' . $question->id;
                $title    = $comment_body;
                //其余的都是article的子类型
            } else {
                $url   = $commentable->url . '#' . $lou;
                $title = $comment_body;
            }
        } else {
            $body  = '喜欢了你的' . $this->article->resoureTypeCN();
            $url   = $this->article->url;
            $title = '《' . $article_title . '》';
        }
        return [
            'type'          => 'like',
            'user_avatar'   => $this->user->avatarUrl,
            'user_name'     => $this->user->name,
            'user_id'       => $this->user->id,
            'article_title' => $article_title,
            'article_id'    => $this->article->id,
            'url'           => $url,
            'body'          => $body,
            'title'         => $title,
        ];
    }
}
