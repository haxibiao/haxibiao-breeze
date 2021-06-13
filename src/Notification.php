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

    //通知的行为描述
    public function getBodyAttribute()
    {
        $comment_id = data_get($this, 'data.comment_id');
        //赞了你 评论的内容  @某某某 内容
        switch (short_notify_type($this->type)) {
            case "ArticleApproved":
                return "收录了动态";
            case "ArticleRejected":
                return "拒绝了动态";
            case "ArticleCommented":
                $comment = Comment::find($comment_id);
                return str_limit($comment->body, 15, '...');
            case "CommentedNotification":
                $comment = Comment::find($comment_id);
                return str_limit($comment->body, 15, '...');
            case "ArticleFavorited":
                return "收藏了动态";
            case "ArticleLiked":
                return "喜欢了文章";
            case "LikedNotification":
                $type = data_get($this, 'date.type');
                if ($type == 'comments') {
                    return "点赞了评论";
                }
                return "喜欢了动态";
            case "CommentLiked":
                return "赞了评论";
            case "ArticleTiped":
                return "打赏了动态";
            case "CategoryFollowed":
                return "关注了专题";
            case "CategoryRequested":
                return "投稿了专题";
            case "CollectionFollowed":
                return "关注了文集";
            case "UserFollowed":
                return "关注了";
            case "ReplyComment":
                $comment = Comment::find($comment_id);
                return str_limit($comment->body, 15, '...');
            case "CommentAccepted":
                $comment = Comment::find($comment_id);
                return str_limit($comment->body, 15, '...');
            case "ReceiveAward":
                return data_get($this, 'data.subject') . data_get($this, 'data.gold') . '金币';
            default:
                return "其他";
        }
    }

    //通知类型
    public function getTypeNameAttribute()
    {
        switch (short_notify_type($this->type)) {
            case "ArticleApproved":
                return "收录了动态";
            case "ArticleRejected":
                return "拒绝了动态";
            case "ArticleCommented":
                return "评论了动态";
            case "CommentedNotification":
                return "评论了";
            case "ArticleFavorited":
                return "收藏了动态";
            case "ArticleLiked":
                return "喜欢了文章";
            case "LikedNotification":
                if (data_get($this, 'data.type') == 'comments') {
                    return "点赞了评论";
                }
                return "喜欢了动态";
            case "CommentLiked":
                return "赞了评论";
            case "ArticleTiped":
                return "打赏了动态";
            case "CategoryFollowed":
                return "关注了专题";
            case "CategoryRequested":
                return "投稿了专题";
            case "CollectionFollowed":
                return "关注了文集";
            case "UserFollowed":
                return "关注了";
            case "ReplyComment":
                return "回复了评论";
            case "CommentAccepted":
                return "评论被采纳";
            case "ReceiveAward":
                return data_get($this, 'data.subject') . data_get($this, 'data.gold') . '金币';
            default:
                return "其他";
        }
    }

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    //通知关联的用户
    public function getUserAttribute()
    {
        if (isset($this->data['user_id'])) {
            $user = User::find($this->data['user_id']);
            return $user;
        }
        return null;
    }

    //通知关联的文章
    public function getArticleAttribute()
    {
        $modelType = data_get($this, 'data.type');
        if ($modelType == 'articles') {
            if ($modelId = data_get($this, 'data.id')) {
                $modelString = Relation::getMorphedModel($modelType);
                return $modelString::withTrashed()->find($modelId);
            }
            return null;
        }
    }

    //通知关联的动态
    public function getPostAttribute()
    {
        $modelType = data_get($this, 'data.type');
        //尊重通知数据里的posts id 即可
        if ($modelType == 'posts') {
            if ($modelId = data_get($this, 'data.id')) {
                $modelString = Relation::getMorphedModel($modelType);
                return $modelString::withTrashed()->find($modelId);
            }
        }
        //喜欢了动态
        if ($modelType == 'likes' && data_get($this, 'data.likeable_type') == 'posts') {
            if ($modelId = data_get($this, 'data.id')) {
                $modelString = Relation::getMorphedModel('posts');
                return $modelString::withTrashed()->find($modelId);
            }
        }
        return null;
    }

    //通知关联的评论
    public function getCommentAttribute()
    {
        $modelType = data_get($this, 'data.type');
        if ($modelType == 'comments') {
            if ($modelId = data_get($this, 'data.id')) {
                $modelString = Relation::getMorphedModel($modelType);
                return $modelString::withTrashed()->find($modelId);
            }
        }
        //喜欢了评论
        if ($modelType == 'likes' && data_get($this, 'data.likeable_type') == 'comments') {
            if ($modelId = data_get($this, 'data.id')) {
                $modelString = Relation::getMorphedModel('comments');
                return $modelString::withTrashed()->find($modelId);
            }
        }
        return null;
    }

    //通知关联的回复
    public function getReplyAttribute()
    {
        return $this->getCommentAttribute();
    }

    public function target()
    {
        return $this->morphTo();
    }

}
