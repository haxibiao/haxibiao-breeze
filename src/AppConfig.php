<?php

namespace Haxibiao\Breeze;

use Illuminate\Database\Eloquent\Model;


class AppConfig extends Model
{
    protected $fillable = [
        'group', 'state', 'name', 'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    // 开启
    const STATUS_ON = 1;

    // 关闭
    const STATUS_OFF = 0;

    public function getStatusAttribute()
    {
        switch ($this->state) {
            case self::STATUS_ON:
                return 'on';
            case self::STATUS_OFF:
                return 'off';
            default:
                return "on";
        }
    }
    // 全版本
    public const ALL_VERSION = 'all';
    //版本开关
    public  function isOpen($app_version)
    {
        $appConfig = $this;
        $switch_version = $appConfig->app_version;
        if ($app_version && $switch_version != self::ALL_VERSION) {

            //规定版本则按开关设置生效
            if ($app_version == $switch_version) {
                return $appConfig->status;
            } else {
                //非规定版本则跟开关相反
                return $appConfig->status=='on'?'off':'on';
            }

        } else {
            //全版本开关
            return $appConfig->status;
        }

    }

}
