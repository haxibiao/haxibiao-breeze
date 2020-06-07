<?php

namespace haxibiao\user;

use Illuminate\Foundation\Auth\User;

abstract class HXBUser extends User
{
    use AuthHelper;
    use AvatarHelper;
    use UserResolvers;

    /**
     * 用户状态 -2:禁用(禁止提现) -1:禁言 0:正常启用
     */
    const DISABLE_STATUS = -2;
    const MUTE_STATUS    = -1;
    const ENABLE_STATUS  = 0;

}
