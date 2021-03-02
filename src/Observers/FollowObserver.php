<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Breeze\Events\NewFollow;
use Haxibiao\Sns\Follow;

class FollowObserver
{
    /**
     * Handle the follow "created" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function created(Follow $follow)
    {
        event(new NewFollow($follow));
        //同步用户关注数
        $user                            = $follow->user;
        $user->profile->count_followings = $user->followings()->count();
        $user->profile->save();

        //同步被关注着的粉丝数
        $count = Follow::where('followable_type', $follow->followable_type)->where("followable_id", $follow->followable_id)->count();
        if ($follow->followable_type == 'users') {
            $follow->followable->profile->update(['count_follows' => $count]);
        } else {
            $follow->followable->update(['count_follows' => $count]);
        }
    }

    /**
     * Handle the follow "updated" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function updated(Follow $follow)
    {
        //
    }

    /**
     * Handle the follow "deleted" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function deleted(Follow $follow)
    {
        //同步用户关注数
        $user                            = $follow->user;
        $user->profile->count_followings = $user->followings()->count();
        $user->profile->save();

        //同步被关注着的粉丝数
        $count = Follow::where('followable_type', $follow->followable_type)->where("followable_id", $follow->followable_id)->count();
        if ($follow->followable_type == 'users') {
            $follow->followable->profile->update(['count_follows' => $count]);
        } else {
            $follow->followable->update(['count_follows' => $count]);
        }
    }

    /**
     * Handle the follow "restored" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function restored(Follow $follow)
    {
        //
    }

    /**
     * Handle the follow "force deleted" event.
     *
     * @param  \App\Follow  $follow
     * @return void
     */
    public function forceDeleted(Follow $follow)
    {
        //
    }
}
