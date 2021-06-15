<?php

namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\User;

trait NotificationResolver
{
    /**
     * 通知列表
     */
    public function resolveNotifications($root, array $args, $context, $resolveInfo)
    {
        if ($user_id = data_get($args, 'user_id')) {
            //支持查看置顶用户的消息数据
            $user = User::find($user_id);
        } else {
            $user = getUser();
        }
        $type = $args['type'] ?? ''; //GROUP_开头的其实消息组
        return UserNotifiable::getAppNotificationUnreads($user, $type);
    }

    /**
     * 答赚版的通知列表
     */
    public static function resolverNotifications($root, $args, $context, $info)
    {
        app_track_event("个人中心", "获取通知列表");
        $user = getUser();

        $notifications = \App\Notification::with(['notifiable', 'target'])
            ->where('notifiable_type', 'users')
            ->where('notifiable_id', $user->id);

        //filter 不为空
        if (!empty($args['filter'])) {
            $notifications = $notifications->whereIn('type', $args['filter']);
        }

        //已读
        if ($args['read_filter'] == 1) {
            $notifications = $notifications
                ->where('notifiable_id', $user->id)
                ->whereNotNull('read_at');
        }

        //未读
        if ($args['read_filter'] == -1) {
            $notifications = $notifications->where([
                'read_at'       => null,
                'notifiable_id' => $user->id,
            ]);
        }

        // 此处只针对未读的数据进行更新,不然扫描行太多,需要 where type 不然无法命中索引
        $unreadNotifications = clone $notifications;
        $unreadNotifications->where('notifiable_type', 'users')->whereNull('read_at')->update(['read_at' => now()]);

        //对消息进行排序
        $notifications->orderBy('created_at', 'desc');

        return $notifications;
    }
}
