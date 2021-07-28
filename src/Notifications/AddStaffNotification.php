<?php

namespace Haxibiao\Breeze\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AddStaffNotification extends Notification
{
    use Queueable;
    public $staffUser;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $staffUser, User $user)
    {
        $this->staffUser = $staffUser;
        $this->user      = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
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
        $staffUser = $this->staffUser;
        $user  = $this->user;
        $message = "成为员工账户消息通知";
        $data =  [
            'message'    => $message,
            'id'        => $user->id, //上级用户id
            'title'     => $user->name, //上级用户昵称
            'cover'     => $user->avatar, //上级用户头像
            'type'      => $staffUser->getMorphClass(),
        ];
        return $data;
    }
}
