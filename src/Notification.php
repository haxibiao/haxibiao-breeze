<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Traits\NotificationAttrs;
use Haxibiao\Breeze\Traits\NotificationResolver;
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

    /**
     * 这个就是被通知的 notfiable 用户自己
     */
    public function target()
    {
        return $this->morphTo();
    }

}
