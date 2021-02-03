<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 提供用户基础资料和公开统计数据，为task何dimension提供方便的数据获取
 */
class UserProfile extends Model
{
    protected $guarded = [];
    //首先兼容答赚...
    protected $table = 'user_profiles';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
