<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\BaseUser;

class User extends BaseUser
{

    protected $guarded = [];

    protected $fillable = [
        'name',
        'uuid',
        'phone',
        'account',
        'email',
        'avatar',
        'password',
        'api_token',
        'remember_token',
        'created_at',
        'updated_at',
        'role_id',
        'ticket',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 用户状态 -2:禁用(禁止提现) -1:禁言 0:正常启用
     */
    const DISABLE_STATUS    = -2;
    const MUTE_STATUS       = -1;
    const ENABLE_STATUS     = 0;
    const DEREGISTER_STATUS = -3;

    /**
     * 默认用户名
     * @deprecated 用getDefaultName()获取env里配置的
     */
    const DEFAULT_USER_NAME = '匿名答友';

    // ==================== 下面是工厂APP的 ====================

    /**
     * 性别
     */
    const MALE_GENDER   = 0;
    const FEMALE_GENDER = 1;

    // 正常状态
    const STATUS_ONLINE = 0;

    //以前奇怪的冻结账户，怎么用status=1啊...
    //FIXME: 这个后面要修复为-1, 注销修复为-2, 负数的status都是异常的

    // 封禁状态
    const STATUS_OFFLINE = 1;

    //暂时冻结的账户
    const STATUS_FREEZE = -1;

    // 注销状态
    const STATUS_DESTORY = -2; //这个注销状态值太诡异

    // 默认头像(这是要求cos都存了默认头像图片...)
    const AVATAR_DEFAULT = '/vendor/breeze/images/avatar.jpg';

    const DEFAULT_NAME = '匿名用户';

    /**
     * 编辑身份
     */
    const USER_STATUS   = 0;
    const EDITOR_STATUS = 1;
    const ADMIN_STATUS  = 2;
    const VEST_STATUS   = 3;

    //用户激励视频奖励
    const VIDEO_REWARD_GOLD       = 10;
    const VIDEO_REWARD_TICKET     = 10;
    const VIDEO_REWARD_CONTRIBUTE = 6;

    public static function getGenders()
    {
        return [
            self::MALE_GENDER   => '男',
            self::FEMALE_GENDER => '女',
        ];
    }

}
