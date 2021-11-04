<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Sns\Chat;
use Illuminate\Bus\Queueable;

class ChatJoinResultNotification extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "成功加入群聊通知";

    public $chat;
    public $result;

    public function __construct(Chat $chat, $result)
    {
        $this->chat   = $chat;
        $this->result = $result;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        \info("====");
        $description = '';
        if ($this->result) {
            $description = "您申请加入【{$this->chat->subjuect}】的申请已通过审核";
        } else {
            $description = "您申请加入【{$this->chat->subjuect}】的申请已被拒绝";
        }
        //互动对象
        $data = [
            'type'        => 'chat',
            'id'          => $this->chat->id,
            'name'        => $this->chat->subject,
            'description' => $description, //对象的内容
            'cover' => $this->chat->icon ?? null,
        ];
        return $data;

    }

}
