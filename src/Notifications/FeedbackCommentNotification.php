<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Sns\Comment;
use Haxibiao\Sns\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackCommentNotification extends Notification
{
    use Queueable;

    protected $comment = null;

    protected $feedback = null;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Feedback $feedback, Comment $comment)
    {
        $this->feedback = $feedback;
        $this->comment  = $comment;
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
        return null;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->comment->notifyToArray(['feedback_id' => $this->feedback->id]);
    }
}
