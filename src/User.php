<?php

namespace Haxibiao\Base;

use Haxibiao\Base\Traits\AuthHelper;
use Haxibiao\Base\Traits\AvatarHelper;
use Haxibiao\Base\Traits\UserResolvers;
use Illuminate\Foundation\Auth\User as BaseUser;

class User extends BaseUser
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
