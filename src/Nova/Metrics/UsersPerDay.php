<?php

namespace Haxibiao\Breeze\Nova\Metrics;

use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class UsersPerDay extends Trend
{

    public $name = '每日新增用户趋势(位)';
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $range = $request->range;
        $data  = [];

        $data = get_users_trend($range);

        $max       = max($data);
        $yesterday = array_values($data)[$range - 2];

        return (new TrendResult(end($data)))->trend($data)
            ->suffix("昨日: $yesterday  最大: $max");
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            7  => '过去7天内',
            30 => '过去30天内',
            60 => '过去60天内',
            90 => '过去90天内',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'users-per-day';
    }
    public function getCustomDateUserCount(array $date)
    {
        return User::query()->whereBetween('created_at', $date)->count();
    }
}
