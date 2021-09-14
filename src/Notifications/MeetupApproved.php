<?php

namespace Haxibiao\Breeze\Notifications;

class MeetupApproved extends BreezeNotification
{
    public static $notify_event = "申请加入联盟";
    protected $sender;
    protected $meetup;
    protected $league;

    public function __construct($sender,$meetup,$league)
    {
        $this->sender   = $sender;
        $this->meetup   = $meetup;
        $this->league   = $league;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $meetups = data_get($notifiable,'json.meetups');
        return array_merge(
            $this->senderToArray(), [
            //通知互动的内容
            'type'        => 'meetups',
            'id'          => count($meetups)-1,// 偏移量
            'message'     => '申请加入《'.data_get($this->league,'title').'》联盟',
            'cover'       => data_get($this,'meetup.images.0.url'),
            'description' => $this->meetup->title,
            'meetup_id'   => $this->meetup->id,
            'league_id'   => $this->league->id,
            'event'       => '加入联盟订单',
        ]);
    }
}
