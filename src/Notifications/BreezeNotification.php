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
    //通知的事件名
    protected $custom_event;
    //通知相关对象
    protected $data_id;
    protected $data_type;
    //通知的消息
    protected $data_message;
    //通知的配图
    protected $data_cover;
    //通知的配文
    protected $data_description;

    //触发通知的人
    protected $sender;

    public static $notify_event = "通知";

    public function __construct(User $sender,
        $data_id,
        $data_type,
        $data_message,
        $data_cover = null,
        $data_description = null,
        $custom_event = null
    ) {
        $this->sender = $sender;

        $this->data_id          = $data_id;
        $this->data_type        = $data_type;
        $this->data_message     = $data_message;
        $this->data_cover       = $data_cover;
        $this->data_description = $data_description;
        $this->custom_event     = $custom_event;
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
        //发邮件有需要，填充url到data
        $this->data_url = url($this->data_type . "/" . $this->data_id);
        $mailSubject    = "来自" . $this->sender->name . "的通知：" . self::$notify_event;
        return (new MailMessage)
            ->from('notification@' . env('APP_DOMAIN'), config('app.name_cn'))
            ->subject($mailSubject)
            ->line($mailSubject . " " . $this->data_title . " <br/>"+$this->data_description)
            ->action('查看详情', $this->data_url)
            ->line($this->data_description);
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
                'type'        => $this->data_type,
                'id'          => $this->data_id,
                'message'     => $this->data_message,
                'cover'       => $this->data_cover,
                'description' => $this->data_description,
                'event'       => $this->custom_event,
            ]);
    }
}
