<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 一个通用的系统通知
 */
class BreezeNotification extends Notification
{
    //通知相关对象
    protected $notify_id;
    protected $notify_type;
    //通知的消息
    protected $notify_message;
    //通知的配图
    protected $notify_cover;
    //通知的配文
    protected $notify_description;
    //通知的对象的URL
    protected $notify_url;

    //触发通知的人
    protected $sender;

    public static $notify_action = "通知";

    public function __construct(User $sender,
        $notify_id,
        $notify_type,
        $notify_message,
        $notify_cover = null,
        $notify_description = null,
        $notify_url = null
    ) {
        $this->sender = $sender;

        $this->notify_id          = $notify_id;
        $this->notify_type        = $notify_type;
        $this->notify_message     = $notify_message;
        $this->notify_cover       = $notify_cover;
        $this->notify_description = $notify_description;
        $this->notify_url         = $notify_url;
    }

    public function via($notifiable)
    {
        //不发给自己
        if ($this->sender->id == $notifiable->id) {
            return [];
        }
        return ['database'];
    }

    public function toMail($notifiable)
    {
        $mailSubject = "来自" . $this->sender->name . "的" . self::$notify_action . "：" . $this->notfiy_title;
        return (new MailMessage)
            ->from('notification@' . env('APP_DOMAIN'), config('app.name_cn'))
            ->subject($mailSubject)
            ->line($mailSubject)
            ->action('查看详情', $this->notify_url)
            ->line($this->notify_description);
    }

    protected function senderToArray()
    {
        return [
            'user_id'     => $this->sender->id,
            'user_avatar' => $this->sender->avatarUrl,
            'user_name'   => $this->sender->name,
        ];
    }

    public function toArray($notifiable)
    {
        return array_merge(
            //通知互动的用户
            $this->senderToArray(), [
                //通知互动的内容
                'type'        => $this->notify_type,
                'id'          => $this->notify_id,
                'message'     => $this->notify_message,
                'description' => $this->notify_description,
                'cover'       => $this->notify_cover,
                'url'         => $this->notify_url,
            ]);
    }
}
