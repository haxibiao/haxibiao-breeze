<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 一个通用的系统通知
 */
class BreezeNotification extends Notification
{
    //通知相关对象
    protected $notify_id;
    protected $notify_type;
    protected $notify_title;
    protected $notify_cover;
    protected $notify_description;
    protected $notify_url;
    //相关的用户(不是被通知的用户:$notifiable)
    protected $user;

    public function __construct(User $user,
        $notify_id,
        $notify_type,
        $notify_title,
        $notify_cover = null,
        $notify_description = null,
        $notify_url = null
    ) {
        $this->user = $user;

        $this->notify_id          = $notify_id;
        $this->notify_type        = $notify_type;
        $this->notify_title       = $notify_title;
        $this->notify_cover       = $notify_cover;
        $this->notify_description = $notify_description;
        $this->notify_url         = $notify_url;
    }

    public function via($notifiable)
    {
        //暂时不发邮件
        return ['database'];
    }

    public function toMail($notifiable)
    {
        // $this->user->name . ', 您求片的' . $this->movie->name . '已完成修复.'
        $mailSubject = $this->notfiy_title;
        return (new MailMessage)
            ->from('notification@' . env('APP_DOMAIN'), config('app.name_cn'))
            ->subject($mailSubject)
            ->line($mailSubject)
            //$this->movie->url
            ->action('查看详情', $this->notify_url)
            // $this->user->name . ' 你悬赏的影片 ' . $this->movie->name . ' 已可以正常播放 '
            ->line($this->notify_description);
    }

    public function toArray($notifiable)
    {
        return [
            'user_id'     => $this->user->id,
            'user_avatar' => $this->user->avatarUrl,
            'user_name'   => $this->user->name,

            'type'        => 'movies',
            'id'          => $this->notify_id,
            'title'       => $this->notify_title,
            'description' => $this->notify_description,
            'cover'       => $this->notify_cover,
            'url'         => $this->notify_url,
        ];
    }
}
