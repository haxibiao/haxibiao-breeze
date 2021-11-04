<?php

namespace Haxibiao\Breeze\Notifications;

use App\User;
use Haxibiao\Sns\Chat;
use Illuminate\Bus\Queueable;

class ChatJoinNotification extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "申请加入群聊通知";

    public $description;
    public $chat;

    public function __construct(User $user, Chat $chat, $description)
    {
        $this->sender      = $user;
        $this->chat        = $chat;
        $this->description = $description;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        //互动用户
        $data = $this->senderToArray();
        \info("wdawda");
        //互动对象
        $data = array_merge($data, [
            'type'        => 'chat',
            'id'          => $this->chat->id,
            'name'        => $this->chat->subject,
            'description' => $this->description, //对象的内容
            'cover' => $this->chat->icon ?? null,
        ]);
        return $data;

    }

}
