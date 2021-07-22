<?php

namespace Haxibiao\Breeze\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AddAssociateNotification extends BreezeNotification
{
    use Queueable;
    public $user;
    public $senderId;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user,Int $senderId,String $message)
    {
        $this->user   = $user;
        $this->senderId = $senderId;
        $this->message  = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $user = $this->user;
        $senderId = $this->senderId;
        $message  = $this->message;
        $sender = User::find($senderId);
        $notice = "申请成为员工(客户)消息通知:【$user->name】";
        $data = [
            'description'       => '用户申请成为您的员工(客户)消息提醒',
            'message'     => $message,      //用户留言
            'recipient'   => "接收者:$user->id",
            'id'          => $senderId,     //发起者
            'title'       => $sender->name, //发起者昵称
            'cover'       => $sender->avatar, //发起者头像:
            'notice'      => $notice,
            'type'        => $user->getMorphClass(),
        ];
        return $data;
    }
}
