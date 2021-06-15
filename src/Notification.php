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
        $notification_namespace = "Haxibiao\\Breeze\\Notifications\\";
        $notification_class     = $notification_namespace . short_notify_type($this->type);
        return $notification_class::$notify_action ?? '互动通知';
    }

    /**
     * 这个就是被通知的 notfiable 用户自己
     */
    public function target()
    {
        return $this->morphTo();
    }

}
