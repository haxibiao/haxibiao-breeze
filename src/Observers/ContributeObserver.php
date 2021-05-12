<?php

namespace Haxibiao\Breeze\Observers;

use App\Invitation;
use Haxibiao\Task\Contribute;

class ContributeObserver
{
    /**
     * Handle the contribute "created" event.
     *
     * @param  \App\Contribute  $contribute
     * @return void
     */
    public function created(Contribute $contribute)
    {
        if (config('app.name') == 'datizhuanqian') {
            //统计贡献
            Contribute::recountUserContributes($contribute->user);
            //检测贡献记录异常行为
            Contribute::detectBadUser($contribute);

            //统计 today_reward_video_count
            if ($contribute->contributed_type == "reward_videos") {
                $user                              = $contribute->user;
                $profile                           = $user->profile;
                $profile->today_reward_video_count = $user->contributes()
                    ->whereBetween('created_at', [
                        today(),
                        today()->addDay(),
                    ])
                    ->where('contributed_type', 'reward_videos')
                    ->count();
                $profile->last_reward_video_time = now();
                $profile->save();

                Invitation::adReward($contribute->user);
            }
        }

        $user = $contribute->user;
        if (!is_null($user)) {
            //更新任务状态
            $user->reviewTasksByClass(get_class($contribute));
        }

    }

    /**
     * Handle the contribute "updated" event.
     *
     * @param  \App\Contribute  $contribute
     * @return void
     */
    public function updated(Contribute $contribute)
    {
    }

    /**
     * Handle the contribute "deleted" event.
     *
     * @param  \App\Contribute  $contribute
     * @return void
     */
    public function deleted(Contribute $contribute)
    {
        //
    }

    /**
     * Handle the contribute "restored" event.
     *
     * @param  \App\Contribute  $contribute
     * @return void
     */
    public function restored(Contribute $contribute)
    {
        //
    }

    /**
     * Handle the contribute "force deleted" event.
     *
     * @param  \App\Contribute  $contribute
     * @return void
     */
    public function forceDeleted(Contribute $contribute)
    {
        //
    }
}
