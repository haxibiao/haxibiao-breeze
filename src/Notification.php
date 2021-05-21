<?php

namespace Haxibiao\Breeze;

use App\User;
use Haxibiao\Breeze\Traits\NotificationAttrs;
use Haxibiao\Breeze\Traits\NotificationResolver;
use Haxibiao\Sns\Comment;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{

    use NotificationAttrs, NotificationResolver;
    //通知的主体信息
    public function getBodyAttribute()
    {
        //赞了你 评论的内容  @某某某 内容
        switch ($this->type) {
            case "Haxibiao\\Breeze\\Notifications\\ArticleApproved":
                return "收录了动态";
            case "Haxibiao\\Breeze\\Notifications\\ArticleRejected":
                return "拒绝了动态";
            case "Haxibiao\\Breeze\\Notifications\\ArticleCommented":
                $comment = Comment::find($this->data['comment_id']);
                return str_limit($comment->body, 15, '...');
            case "Haxibiao\\Breeze\\Notifications\\CommentedNotification":
                $comment = Comment::find($this->data['comment_id']);
                return str_limit($comment->body, 15, '...');
            case "Haxibiao\\Breeze\\Notifications\\ArticleFavorited":
                return "收藏了动态";
            case "Haxibiao\\Breeze\\Notifications\\ArticleLiked":
                return "喜欢了文章";
            case "Haxibiao\\Breeze\\Notifications\\LikedNotification":
                $type = data_get($this, 'date.type');
                if ($type == 'comments') {
                    return "点赞了评论";
                }
                return "喜欢了动态";
            case "Haxibiao\\Breeze\\Notifications\\CommentLiked":
                return "赞了评论";
            case "Haxibiao\\Breeze\\Notifications\\ArticleTiped":
                return "打赏了动态";
            case "Haxibiao\\Breeze\\Notifications\\CategoryFollowed":
                return "关注了专题";
            case "Haxibiao\\Breeze\\Notifications\\CategoryRequested":
                return "投稿了专题";
            case "Haxibiao\\Breeze\\Notifications\\CollectionFollowed":
                return "关注了文集";
            case "Haxibiao\\Breeze\\Notifications\\UserFollowed":
                return "关注了";
            case "Haxibiao\\Breeze\\Notifications\\ReplyComment":
                $comment = Comment::find($this->data['comment_id']);
                return str_limit($comment->body, 15, '...');
            case "Haxibiao\\Breeze\\Notifications\\CommentAccepted":
                $comment = Comment::find($this->data['comment_id']);
                return str_limit($comment->body, 15, '...');
            case "Haxibiao\\Breeze\\Notifications\\ReceiveAward":
                return $this->data["subject"] . $this->data["gold"] . '金币';
            default:
                return "其他";
        }
    }

    public function getTypeNameAttribute()
    {
        //回复了你的评论、在评论中提到了你...等等通知类型
        switch ($this->type) {
            case "Haxibiao\\Breeze\\Notifications\\ArticleApproved":
                return "收录了动态";
            case "Haxibiao\\Breeze\\Notifications\\ArticleRejected":
                return "拒绝了动态";
            case "Haxibiao\\Breeze\\Notifications\\ArticleCommented":
                return "评论了动态";
            case "Haxibiao\\Breeze\\Notifications\\CommentedNotification":
                return "评论了";
            case "Haxibiao\\Breeze\\Notifications\\ArticleFavorited":
                return "收藏了动态";
            case "Haxibiao\\Breeze\\Notifications\\ArticleLiked":
                return "喜欢了文章";
            case "Haxibiao\\Breeze\\Notifications\\LikedNotification":
                if (data_get($this, 'data.type') == 'comments') {
                    return "点赞了评论";
                }
                return "喜欢了动态";
            case "Haxibiao\\Breeze\\Notifications\\CommentLiked":
                return "赞了评论";
            case "Haxibiao\\Breeze\\Notifications\\ArticleTiped":
                return "打赏了动态";
            case "Haxibiao\\Breeze\\Notifications\\CategoryFollowed":
                return "关注了专题";
            case "Haxibiao\\Breeze\\Notifications\\CategoryRequested":
                return "投稿了专题";
            case "Haxibiao\\Breeze\\Notifications\\CollectionFollowed":
                return "关注了文集";
            case "Haxibiao\\Breeze\\Notifications\\UserFollowed":
                return "关注了";
            case "Haxibiao\\Breeze\\Notifications\\ReplyComment":
                return "回复了评论";
            case "Haxibiao\\Breeze\\Notifications\\CommentAccepted":
                return "评论被采纳";
            case "Haxibiao\\Breeze\\Notifications\\ReceiveAward":
                return $this->data["subject"] . $this->data["gold"] . '金币';
            default:
                return "其他";
        }
    }

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getUserAttribute()
    {
        if (isset($this->data['user_id'])) {
            $user = User::find($this->data['user_id']);
            return $user;
        }
        return null;
    }

    public function getArticleAttribute()
    {
        $modelType = data_get($this, 'data.type');
        if (!in_array($modelType, ['posts', 'comments'])) {
            return null;
        }
        if ($modelType == 'posts') {
            $modelId = data_get($this, 'data.id');
            if ($modelId) {
                $modelString = Relation::getMorphedModel($modelType);
                return $modelString::withTrashed()->find($modelId);
            }
            return null;
        }
        $comment = $this->getCommentAttribute();
        if (data_get($this, 'type') == 'Haxibiao\Breeze\Notifications\LikedNotification') {
            $commentable = data_get($comment, 'commentable');
            if ($commentable instanceof Comment) {
                return data_get($comment, 'commentable.commentable');
            }
            return data_get($comment, 'commentable');
        }
        return data_get($comment, 'commentable.commentable');
    }

    public function getPostAttribute()
    {
        $modelType = data_get($this, 'data.type');
        if (in_array($modelType, ['posts', 'comments'])) {
            return null;
        }
        if ($modelType == 'posts') {
            $modelId = data_get($this, 'data.id');
            if ($modelId) {
                $modelString = Relation::getMorphedModel($modelType);
                return $modelString::withTrashed()->find($modelId);
            }
            return null;
        }
        $comment = $this->getCommentAttribute();
        return data_get($comment, 'commentable.commentable');
    }

    public function getCommentAttribute()
    {
        $modelType = data_get($this, 'data.type');
        if ($modelType != 'comments') {
            return null;
        }
        $modelId = data_get($this, 'data.id');
        if ($modelId) {
            $modelString = Relation::getMorphedModel($modelType);
            return $modelString::find($modelId);
        }
        return null;
    }

    public function getReplyAttribute()
    {
        return $this->getCommentAttribute();
    }

    public function target()
    {
        return $this->morphTo();
    }

}
