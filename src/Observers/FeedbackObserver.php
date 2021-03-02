<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Sns\Feedback;
use Haxibiao\Sns\Notice;
use Haxibiao\Task\Assignment;

class FeedbackObserver
{
    //
    public function updated(Feedback $feedback)
    {
        $user        = $feedback->user;
        $commentTask = $user->tasks()->whereName('应用商店好评')->first();
        //更新好评任务的状态
        $assignment = $commentTask->assignments->where('user_id', $user->id)->first();
        $status     = $feedback->status;
        //好评任务
        if ($feedback->type == Feedback::COMMENT_TYPE) {
            if ($status == Feedback::STATUS_PROCESSED) {
                $assignment->status = Assignment::TASK_REACH;
                //好评审核通过，发送系统通知
                Notice::addNotice(
                    [
                        'title'      => '应用商店好评任务：审核通过',
                        'content'    => '您提交的应用商店好评任务审核已通过，不要忘记领取奖励哦',
                        'to_user_id' => $feedback->user_id,
                        'user_id'    => 1,
                        'type'       => Notice::OTHERS,
                    ]
                );

            } else if ($status == Feedback::STATUS_REJECT) {
                $assignment->status = Assignment::TASK_REVIEW;
                //好评审核未通过
                Notice::addNotice(
                    [
                        'title'      => '应用商店好评任务：审核未通过',
                        'content'    => '您的评论内容不符合15字好评要求或者上传了无关截图，请修改评价后重新截图上传',
                        'to_user_id' => $feedback->user_id,
                        'user_id'    => 1,
                        'type'       => Notice::OTHERS,
                    ]
                );
            }
        }
        $assignment->save();
    }

}
