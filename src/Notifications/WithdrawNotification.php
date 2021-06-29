<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Wallet\Withdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawNotification extends Notification
{
    use Queueable;

    protected $withdraw = null;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Withdraw $withdraw)
    {
        $this->withdraw = $withdraw;
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
        $withdraw = $this->withdraw;
        //提现文本描述
        $message = "";
        //提现成功
        if ($withdraw->status == Withdraw::SUCCESS_STATUS) {
            $message = "您于{$withdraw->created_at}发起的{$withdraw->to_account}提现【{$withdraw->amount}】元 申请处理成功！";
        } else if ($withdraw->status == Withdraw::FAILED_STATUS) {
            $message = "您于{$withdraw->created_at}发起的{$withdraw->to_account}提现【{$withdraw->amount}】元 申请处理失败。回执信息：{$withdraw->remark}。";
        }
        //互动对象
        $data = [
            'type'    => $withdraw->getMorphClass(),
            'id'      => $withdraw->id,
            'title'   => "提现通知", //标题
            'message' => $message, //通知主体内容
        ];

        return $data;
    }
}
