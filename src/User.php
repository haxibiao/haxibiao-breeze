<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\BaseUser;

class User extends BaseUser
{
    protected $guarded = [];
    protected $hidden  = [
        'password', 'remember_token',
    ];

    /**
     * 默认用户名
     * @deprecated 用getDefaultName()获取env里配置的
     */
    const DEFAULT_USER_NAME = '匿名答友';
    const DEFAULT_NAME      = '匿名用户';
    // 默认头像(这是要求cos都存了默认头像图片...)
    const AVATAR_DEFAULT = '/images/avatar.jpg';
    //默认签名
    const INTRODUCTION = '这个人暂时没有 freestyle';

    /**
     * 答题的用户状态 -2:禁用(禁止提现) -1:禁言 0:正常启用
     */
    const DISABLE_STATUS    = -2;
    const MUTE_STATUS       = -1;
    const ENABLE_STATUS     = 0;
    const DEREGISTER_STATUS = -3;

    /**
     * 编辑身份
     */
    const USER_STATUS   = 0;
    const EDITOR_STATUS = 1;
    const ADMIN_STATUS  = 2;
    // FIXME 虚拟账户这样的身份很负，应该用-1
    const VEST_STATUS = 3;

    public static function getRolesMap()
    {
        return [
            User::USER_STATUS   => '用户',
            User::EDITOR_STATUS => '编辑',
            User::ADMIN_STATUS  => '管理',
            User::VEST_STATUS   => '马甲',
        ];
    }

    /**
     * 用户角色中文显示
     */
    public function getRoleNameAttribute()
    {
        return static::getRolesMap()[$this->role_id] ?? '用户';
    }

    // 用户状态 - 答题外其他的APP
    const STATUS_ONLINE = 0;
    // FIXME: 封禁状态？应该异常状态<0
    const STATUS_OFFLINE = 1;
    // 暂时冻结的账户
    const STATUS_FREEZE = -1;
    // 注销状态
    const STATUS_DESTORY = -2;

    public static function getStatusMap()
    {
        return [
            User::STATUS_OFFLINE => '封禁',
            User::STATUS_ONLINE  => '正常',
            User::STATUS_FREEZE  => '状态异常系统封禁',
            User::STATUS_DESTORY => '注销',
        ];
    }

    /**
     * 用户状态中文显示
     */
    public function getStatusNameAttribute()
    {
        return static::getStatusMap()[$this->status] ?? '正常';
    }

    //用户激励视频奖励
    const VIDEO_REWARD_GOLD       = 10;
    const VIDEO_REWARD_TICKET     = 10;
    const VIDEO_REWARD_CONTRIBUTE = 6;

    /**
     * 性别
     */
    const MALE_GENDER   = 0;
    const FEMALE_GENDER = 1;
    public static function getGenders()
    {
        return [
            self::MALE_GENDER   => '男',
            self::FEMALE_GENDER => '女',
        ];
    }

}
