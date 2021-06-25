<?php

namespace Haxibiao\Breeze\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LuckyUserNotification extends Notification
{
    use Queueable;

    private $user   = null;
    private $amount = 0;
    private $isWin;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $amount, $isWin = false)
    {
        $this->user   = $user;
        $this->amount = $amount;
        $this->isWin  = $isWin;
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
        $message = "您上期参加了【高额抽奖】活动，可惜没有中奖,继续参与可以提高中奖概率哦～";
        if ($this->isWin) {
            $message = '恭喜您在上期参加的【高额抽奖】活动中中奖啦！今日内可在【我的钱包】-【活动提现】-【中奖红包】申请提现' . $this->amount . '元';
        }

        $data = [
            'title'   => "抽奖结果", //标题
            'message' => $message, //通知主体内容
        ];
        return $data;
    }
}
