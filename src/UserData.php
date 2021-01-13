<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Model;

/**
 * 专注用户数据分析统计用，profile之外的数据
 */
class UserData extends Model
{
    protected $guarded = [];

    //FIX:升级laravel5.8后这里不一样了，data不自动带s
    protected $table = 'user_datas';

}
