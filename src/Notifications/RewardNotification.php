<?php

namespace Haxibiao\Breeze\Notifications;

use Illuminate\Bus\Queueable;

class RewardNotification extends BreezeNotification
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $data = $this->senderToArray();

        $rewards = $this->rewards;

        //文本描述
        $message = "恭喜您获得系统奖励!";
        if (!is_null($rewards['ticket'])) {
            $message = $message . "奖励精力点：{$$rewards['ticket']}";
        }
        if (!is_null($rewards['gold'])) {
            $message = $message . "奖励智慧点点：{$$rewards['gold']}";
        }
        if (!is_null($rewards['contribute'])) {
            $message = $message . "奖励贡献点：{$$rewards['contribute']}";
        }

        $data = array_merge($data, [
            'title'   => "系统奖励", //标题
            'message' => $message, //通知主体内容
        ]);

        return $data;

    }
}
