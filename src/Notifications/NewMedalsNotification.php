<?php
namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Task\Medal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMedalsNotification extends Notification
{
    use Queueable;

    public $medal;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Medal $medal)
    {
        $this->medal = $medal;
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
        $medal = $this->medal;
        //文本描述
        $message = "恭喜达成新的勋章成就:【$medal->name_cn】!";

        $data = [
            'type'    => $medal->getMorphClass(),
            'id'      => $medal->id,
            'title'   => "勋章成就", //标题
            'message' => $message, //通知主体内容
            'cover'   => $medal->done_icon_url,
        ];

        return $data;
    }
}
