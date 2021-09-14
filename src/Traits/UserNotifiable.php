<?php
namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\User;
use Illuminate\Support\Facades\Cache;

/**
 * 用户通知部分特性
 */
trait UserNotifiable
{

    /**
     * 未读消息数(分组计数，兼容网页消息功能)
     */
    public function unreads($type = null, $num = null)
    {
        //缓存未命中
        $unreadNotifications = \App\Notification::where([
            'read_at'       => null,
            'notifiable_id' => $this->id,
        ])->get();
        $unreads = [
            'comments' => null,
            'likes'    => null,
            'follows'  => null,
            'tips'     => null,
            'others'   => null,
            'chats'    => null,
        ];
        //下列通知类型是进入了notification表的
        $unreadNotifications->each(function ($item) use (&$unreads) {
            $notify_type = short_notify_type($item->type);
            switch ($notify_type) {

                //评论的通知（group）
                case 'ArticleCommented':
                case 'CommentedNotification':
                case 'ReplyCommentNotification':
                    $unreads['comments']++;
                    break;

                //喜欢点赞通知(group: 文章，评论)
                case 'ArticleLiked':
                case 'CommentLiked':
                case 'LikedNotification':
                    $unreads['likes']++;
                    break;

                //关注用户通知 - 网页用
                case 'UserFollowed':
                    $unreads['follows']++;
                    break;

                //打赏文章通知 - 网页用
                case 'ArticleTiped':
                    $unreads['tips']++;
                    break;
                //打赏文章通知 - 网页用
                case 'ChatNewMessage':
                    $unreads['chats']++;
                    break;

                //其他类型的通知
                default:
                    $unreads['others']++;
                    break;
            }
        });

        //聊天消息数
        $unreads['chats'] = $this->chats->sum(function ($item) {
            return $item->pivot->unreads;
        });
        //投稿请求数
        $unreads['requests'] = $this->adminCategories()->sum('new_requests');

        //write cache
        Cache::put('unreads_' . $this->id, $unreads, 60);

        if ($num) {
            $unreads[$type] = $num;
            //write cache
            Cache::put('unreads_' . $this->id, $unreads, 60);
        }
        if ($type) {
            return $unreads[$type] ? $unreads[$type] : null;
        }

        return $unreads;
    }

    /**
     * 清空消息未读数
     */
    public function forgetUnreads()
    {
        Cache::forget('unreads_' . $this->id);
    }

    public static function getAppNotificationUnreads(User $user, $type)
    {
        $notifications       = \App\Notification::where('notifiable_type', 'users')->where('notifiable_id', $user->id);
        $unreadNotifications = \App\Notification::where('notifiable_type', 'users')->where('notifiable_id', $user->id)->whereNull('read_at');
        $namespace           = "Haxibiao\\Breeze\\Notifications\\";
        switch ($type) {
            case 'GROUP_COMMENT':
                //评论类通知
                $types = [
                    $namespace . 'ReplyCommentNotification',
                    $namespace . 'FeedbackCommentNotification',
                    $namespace . 'ArticleCommented',
                    $namespace . 'QuestionCommented',
                    $namespace . 'CommentedNotification',
                ];
                $qb = $notifications->orderBy('created_at', 'desc')
                    ->whereIn('type', $types);
                //mark as read
                $unread_notifications = $unreadNotifications
                    ->whereIn('type', $types)->get();
                $unread_notifications->markAsRead();
                break;
            case 'GROUP_LIKES':
                //点赞类通知
                $types = [
                    $namespace . 'ArticleLiked',
                    $namespace . 'CommentLiked',
                    $namespace . 'LikedNotification',
                ];
                $qb = $notifications->orderBy('created_at', 'desc')
                    ->whereIn('type', $types);
                //mark as read
                $unread_notifications = $unreadNotifications
                    ->whereIn('type', $types)->get();
                $unread_notifications->markAsRead();
                break;

            case 'GROUP_OTHERS':
                //其他 - 审核 反馈 求片
                $types = [
                    $namespace . 'AddAssociateNotification',
                    $namespace . 'AddStaffNotification',
                    $namespace . 'MeetupApproved',
                    $namespace . 'CollectionFollowed',
                    $namespace . 'CategoryFollowed',
                    $namespace . 'ArticleApproved',
                    $namespace . 'ArticleRejected',
                    $namespace . 'CommentAccepted',
                    $namespace . 'BreezeNotification',
                    $namespace . 'FeedbackCommentNotification',
                    $namespace . 'RewardNotification',
                    $namespace . 'LuckyUserNotification',
                    $namespace . 'NewMedalsNotification',
                    $namespace . 'WithdrawNotification',
                    $namespace . 'AuditQuestionResultNotification',
                    $namespace . 'CurationRewardNotification',
                    $namespace . 'ReportSucceedNotification',
                    $namespace . 'LevelUpNotification',
                ];
                $qb = $notifications->orderBy('created_at', 'desc')
                    ->whereIn('type', $types);

                //mark as read
                $unread_notifications = $unreadNotifications
                    ->whereIn('type', $types)->get();
                $unread_notifications->markAsRead();
                break;
            default:
                //type中只有3个是group，其余的都是单个类型的通知(目前gql里enum value就是full class)
                $class = $type;
                $qb    = $notifications->orderBy('created_at', 'desc')->where('type', $class);
                //mark as read
                $unread_notifications = $unreadNotifications->where('type', $class)->get();
                $unread_notifications->markAsRead();
                break;
        }
        //清理未读数缓存
        $user->forgetUnreads();
        return $qb;
    }
}
