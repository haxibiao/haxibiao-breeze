<?php

namespace Haxibiao\Breeze\Observers;

use Haxibiao\Breeze\UserProfile;

class ProfileObserver
{
    /**
     * Handle the profile "updated" event.
     *
     * @return void
     */
    public function updated(UserProfile $profile)
    {
        $user = $profile->user;
        $user->reviewTasksByClass(get_class($profile));
    }
}
