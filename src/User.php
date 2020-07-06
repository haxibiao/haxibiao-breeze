<?php

namespace Haxibiao\Base;

use Haxibiao\Base\Traits\AuthHelper;
use Haxibiao\Base\Traits\AvatarHelper;
use Haxibiao\Base\Traits\UserResolvers;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * 默认用户名
     * @deprecated 用getDefaultName()获取env里配置的
     */
    const DEFAULT_USER_NAME = '匿名答友';

    // ==================== 下面是工厂APP的 ====================

    /**
     * 性别：男
     */
    const MALE_GENDER = 0;
    /**
     * 性别：女
     */
    const FEMALE_GENDER = 1;

    /**
     * 正常状态
     */
    const STATUS_ONLINE = 0;

    /**
     * 封禁状态
     */
    const STATUS_OFFLINE = 1;

    /**
     * 暂时冻结的账户
     */
    const STATUS_FREEZE = -1;

    /**
     * 注销状态
     */
    const STATUS_DESTORY = -2; //这个注销状态值太诡异

    /**
     * 默认头像
     * @deprecated 默认头像应该为null,然后随机显示20个默认头像中的一个
     */
    const AVATAR_DEFAULT = 'storage/avatar/avatar-1.jpg';

    /**
     * 默认用户名 - 工厂APP
     * @deprecated 用getDefaultName()获取env里配置的
     */
    const DEFAULT_NAME = '匿名用户';

    /**
     * 普通身份
     */
    const USER_STATUS = 0;
    /**
     * 编辑身份
     */
    const EDITOR_STATUS = 1;
    /**
     * 管理身份
     */
    const ADMIN_STATUS = 2;

    //关系

    public function user_profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function user_data(): HasOne
    {
        return $this->hasOne(UserData::class);
    }

    //属性

    /**
     * 用户资料
     */
    public function getProfileAttribute()
    {
        if ($profile = $this->user_profile) {
            return $profile;
        }
        $profile = UserProfile::firstOrCreate(['user_id' => $this->id]);
        return $profile;
    }

    /**
     * 用户数据
     */
    public function getDataAttribute()
    {
        if ($data = $this->user_data) {
            return $data;
        }
        $data = UserData::firstOrCreate(['user_id' => $this->id]);
        return $data;
    }

}
