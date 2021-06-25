<?php

namespace Haxibiao\Breeze\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RewardNotification extends Notification
{
    use Queueable;

    public $rewards;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($rewards)
    {
        $this->rewards = $rewards;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $rewards = $this->rewards;
        //文本描述
        $message = "恭喜您获得系统奖励!";
        if ($rewards['ticket'] ?? null) {
            $message = $message . "奖励精力点：{$rewards['ticket']}";
        }
        if ($rewards['gold'] ?? null) {
            $message = $message . "奖励智慧点点：{$rewards['gold']}";
        }
        if ($rewards['contribute'] ?? null) {
            $message = $message . "奖励贡献点：{$rewards['contribute']}";
        }

        $data = [
            'title'   => "系统奖励", //标题
            'message' => $message, //通知主体内容
        ];

        return $data;

    }
}
