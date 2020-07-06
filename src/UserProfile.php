<?php

namespace Haxibiao\Base;

use Haxibiao\Base\Model;

/**
 * 提供用户基础资料和公开统计数据，为task何dimension提供方便的数据获取
 */
class UserProfile extends Model
{
    protected $guarded = [];
    //首先兼容答赚...
    protected $table = 'user_profiles';
}
