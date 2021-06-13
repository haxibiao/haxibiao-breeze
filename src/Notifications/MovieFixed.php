<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Haxibiao\Media\Movie;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MovieFixed extends Notification
{
    protected $movie;
    protected $user;

    public function __construct(Movie $movie)
    {
        $this->movie = $movie;
        $this->user  = User::find($movie->user_id);
    }

    public function via($notifiable)
    {
        //暂时不发邮件
        return ['database'];
    }

    public function toMail($notifiable)
    {
        $mailSubject = $this->user->name . ', 您的影片求片已完成修复.';
        return (new MailMessage)
            ->from('notification@' . env('APP_DOMAIN'), config('app.name_cn'))
            ->subject($mailSubject)
            ->line($mailSubject)
            ->action('看片地址', $this->movie->url)
            ->line($this->user->name . ' 你悬赏的影片 ' . $this->movie->name . ' 已可以正常播放 ');
    }

    public function toArray($notifiable)
    {
        return [
            'type'        => 'movie',
            'user_id'     => $this->user->id,
            'user_avatar' => $this->user->avatarUrl,
            'user_name'   => $this->user->name,
            'movie_id'    => $this->movie->id,
            'movie_name'  => $this->movie->name,
            'movie_cover' => $this->movie->cover,
        ];
    }
}
