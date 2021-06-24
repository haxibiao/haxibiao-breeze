<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Traits\NotificationAttrs;
use Haxibiao\Breeze\Traits\NotificationResolver;
use Illuminate\Notifications\DatabaseNotification;

/**
 * breeze 的通知
 * FIXME: 逐步用通知数据代原来混乱的每个类型一个属性的通知data结构
 */
class Notification extends DatabaseNotification
{

    use NotificationAttrs;
    use NotificationResolver;

    /**
     * 通知数据 data_id
     */
    public function getDataIdAttribute()
    {
        return data_get($this, 'data.id');
    }

    /**
     * 通知数据 data_type
     */
    public function getDataTypeAttribute()
    {
        return data_get($this, 'data.type');
    }

    /**
     * 通知数据 data_message (有评论，消息的通知)
     */
    public function getDataMessageAttribute()
    {
        $message = data_get($this, 'data.message');
        return $message;
    }

    /**
     * 通知数据 data_cover
     */
    public function getDataCoverAttribute()
    {
        return data_get($this, 'data.cover');
    }

    /**
     * 通知数据 data_title
     */
    public function getDataTitleAttribute()
    {
        return data_get($this, 'data.title');
    }

    /**
     * 通知数据 data_description
     */
    public function getDataDescriptionAttribute()
    {
        $description = data_get($this, 'data.description');

        //避免通知没配文的尴尬
        if (blank($description) && $this->data_type == 'movies') {
            $description = '剧集更新...';
        }

        return $description;
    }

    /**
     * 通知数据 data_url
     */
    public function getDataUrlAttribute()
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
        //优先尊重数据里自定义的事件名
        $data_event = data_get($this, 'data.event');
        if (!blank($data_event)) {
            return $data_event;
        }

        $notification_namespace = "Haxibiao\\Breeze\\Notifications\\";
        $notification_class     = $notification_namespace . short_notify_type($this->type);
        return $notification_class::$notify_event ?? '通知';
    }

    /**
     * 这个就是被通知的 notfiable 用户自己
     */
    public function target()
    {
        return $this->morphTo();
    }

}
