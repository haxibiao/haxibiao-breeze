<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Breeze\User;
use Illuminate\Bus\Queueable;

/**
 * 获得粉丝关注的通知
 */
class UserFollowed extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "新关注";
    protected $sender;

    public function __construct(User $sender)
    {
        $this->sender = $sender;
    }

    public function toArray($notifiable)
    {
        $data = [
            //关注通知有sender基本够了
            'type' => 'follow',
        ];

        //互动用户
        $data = array_merge($data, $this->senderToArray());

        return $data;
    }
}
