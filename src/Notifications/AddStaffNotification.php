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
    public $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $staffUser, Int $user_id)
    {
        $this->staffUser = $staffUser;
        $this->user_id   = $user_id;
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
        $user_id   = $this->user_id;
        $message = "添加员工账户消息通知:【$staffUser->name】";
        $data =  [
            'message' => $message,
            'type'    => $staffUser->getMorphClass(),
            'client_id' => $user_id,
        ];
        return $data;
    }
}
