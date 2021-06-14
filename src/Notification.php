<?php

namespace Haxibiao\Breeze;

use App\User;
use Haxibiao\Breeze\Traits\NotificationAttrs;
use Haxibiao\Breeze\Traits\NotificationResolver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;

/**
 * breeze 的通知
 * FIXME: 逐步用系统通知来替代原来混乱的每个类型一个属性的通知data结构
 */
class Notification extends DatabaseNotification
{

    use NotificationAttrs;
    use NotificationResolver;

    /**
     * 系统通知属性 notify_id
     */
    public function getNotifyIdAttribute()
    {
        return data_get($this, 'data.id');
    }

    /**
     * 系统通知属性 notify_type
     */
    public function getNotifyTypeAttribute()
    {
        return data_get($this, 'data.type');
    }

    /**
     * 系统通知属性 notify_title
     */
    public function getNotifyTitleAttribute()
    {
        return data_get($this, 'data.title');
    }

    /**
     * 系统通知属性 notify_cover
     */
    public function getNotifyCoverAttribute()
    {
        return data_get($this, 'data.cover');
    }

    /**
     * 系统通知属性 notify_description
     */
    public function getNotifyDescriptionAttribute()
    {
        return data_get($this, 'data.description');
    }

    /**
     * 系统通知属性 notify_url
     */
    public function getNotifyUrlAttribute()
    {
        return data_get($this, 'data.url');
    }

    /**
     * 通知的行为描述?
     * @deprecated 这个属性的代码几乎冗余,和getTypeNameAttribute()一样
     */
    public function getBodyAttribute()
    {
        //这个属性的代码几乎冗余..
        return $this->getTypeNameAttribute();
    }

    /**
     * 通知的行为类型
     */
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
                if (data_get($this, 'data.type')) {
                    return "长视频更新";
                }
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
        //尊重data里缓存的用户信息，避免多余查询
        $user = new User([
            'id'     => data_get($this, 'data.user_id'),
            'name'   => data_get($this, 'data.user_name'),
            'avatar' => data_get($this, 'data.user_avatar'),
        ]);
        return $user;
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
