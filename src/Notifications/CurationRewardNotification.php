<?php

namespace Haxibiao\Breeze\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CurationRewardNotification extends Notification
{
    use Queueable;

    protected $curation;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($curation)
    {
        $this->curation = $curation;
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

        $curation = $this->curation;
        //文本描述
        $message = "您对题目“{$curation->question->description}”纠错
                  【{$curation->getTypes()[$curation->type]}】已被采纳，
                    恭喜您获得奖励：{$curation->gold_awarded}智慧点";

        $data = [
            'type'    => $curation->getMorphClass(),
            'id'      => $curation->id,
            'title'   => "题目纠错", //标题
            'message' => $message, //通知主体内容
        ];

        return $data;
    }
}
