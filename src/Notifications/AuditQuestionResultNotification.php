<?php

namespace Haxibiao\Breeze\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuditQuestionResultNotification extends Notification
{
    use Queueable;

    private $question;
    private $gold;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($question, $gold)
    {
        $this->question = $question;
        $this->gold     = $gold;
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
        $question = $this->question;
        $cateName = data_get($question->category, 'name');
        //文本描述
        $message = "您在【{$cateName}】题库下的出题“{$question->description}”已被采纳，
                    恭喜您获得奖励：{$this->gold}智慧点";

        $data = [
            'type'    => "questions",
            'id'      => $question->id,
            'title'   => "出题任务", //标题
            'message' => $message, //通知主体内容
        ];

        return $data;

    }
}
