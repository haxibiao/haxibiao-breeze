<?php

namespace Haxibiao\Breeze\Notifications;

use Haxibiao\Store\Order;
use Illuminate\Bus\Queueable;

class OrderNotification extends BreezeNotification
{
    use Queueable;

    public static $notify_event = "订单通知";
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $order = $this->order;
        $store = $order->store;

        //四种约钟订单状态
        //具体通知用户、商家、技师在调用上层判断处理
        if ($this->order->status = Order::RESERVE) {
            //1. 发起预约 通知商家
            $this->sender       = $store->user;
            $this->data_message = "【{$this->order->user->name}】发起了预约订单！";
            $this->custom_event = "新的预约订单提醒";
        } else if ($order->status = Order::REJECT) {
            //2. 商家拒绝接单 通知用户
            $this->sender       = $order->user;
            $this->data_message = "商家拒绝了接单，详情请联系商家。";
            $this->custom_event = "拒绝接单提醒";
        } else if ($order->status = Order::CANCEL) {
            //3. 用户取消订单 通知商家
            $this->sender       = $store->user;
            $this->data_message = "【{$this->order->user->name}】用户取消了该订单";
            $this->custom_event = "取消订单提醒";
        } else if ($order->status = Order::ACCEPT) {
            //4. 商家接单 通知用户
            $this->sender       = $order->user;
            $this->data_message = "商家已接单";
            $this->custom_event = "订单生效提醒";
        } else if ($order->status = Order::OVER) {
            //5. 订单结束 通知技师和用户
            $this->sender       = $store->user;
            $this->data_message = "订单完成";
            $this->custom_event = "订单生效提醒";
        }

        //互动用户
        $data = $this->senderToArray();

        //互动对象
        $data = array_merge($data, [
            'type'        => 'order',
            'id'          => $this->order->id,
            'message'     => $this->data_message ?? null,
            'description' => $store->description, //对象的内容
            'cover'       => $store->logo ?? null,
            'event'       => $this->custom_event,
        ]);

        return $data;
    }
}
