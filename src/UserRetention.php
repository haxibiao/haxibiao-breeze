<?php
namespace Haxibiao\Base;

use Illuminate\Database\Eloquent\Model;

class UserRetention extends Model
{
    const CACHE_FORMAT = 'day%s_at_%s';

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * 获取某日，n日留存的数据缓存结果
     */
    public static function getCachedValue($day_n_at, $date)
    {
        $key = sprintf(UserRetention::CACHE_FORMAT, $day_n_at, $date);
        return cache()->store('database')->get($key, 0);
    }

    /**
     * 记录用户的留存
     */
    public static function recordUserRetention($user)
    {
        $retention = $user->retention;

        $diffDay = today()->startOfDay()->diffInDays($user->created_at->startOfDay());
        $diffDay = $diffDay + 1; //次留算day2
        $diffDay = $diffDay >= 29 ? 30 : $diffDay; //超过29天能活跃，就算月留存
        switch ($diffDay) {
            case 2: //次日留
                $retention->day2_at = $retention->day2_at ?? now();
                break;
            case 3: //3日留
                $retention->day3_at = $retention->day3_at ?? now();
                break;
            case 4:
                $retention->day4_at = $retention->day4_at ?? now();
                break;
            case 5: //5日留
                $retention->day5_at = $retention->day5_at ?? now();
                break;
            case 6:
                $retention->day6_at = $retention->day6_at ?? now();
                break;
            case 7: //7日留
                $retention->day7_at = $retention->day7_at ?? now();
                break;
            case 30: //月留
                $retention->day30_at = $retention->day30_at ?? now();
                break;
            default:
                break;
        }
        try {
            $retention->save();
        } catch (\Exception $ex) {}

    }
}
