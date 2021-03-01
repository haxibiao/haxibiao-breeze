<?php

namespace Haxibiao\Breeze\Nova\Metrics;

use Haxibiao\Breeze\UserRetention;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class UserRetentionTrend extends Trend
{
    public $name = '留存率变化趋势(%)';

    public function calculate(Request $request)
    {
        $range    = $request->range;
        $endOfDay = Carbon::today();
        $data     = [];
        for ($i = 0; $i < 30; $i++) {
            $data[$endOfDay->toDateString()] = UserRetention::getCachedValue($range, $endOfDay->toDateString());
            $endOfDay                        = $endOfDay->subDay(1);
        }
        $data = array_reverse($data);

        $arr = $data;
        array_pop($arr);
        $yesterday = last($arr);
        $max       = max($arr);

        return (new TrendResult())->trend($data)->showLatestValue()
            ->suffix("前一天: $yesterday% 最大: $max%");
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            2  => '次日留存率',
            3  => '三日留存率',
            5  => '五日留存率',
            7  => '七日留存率',
            30 => '三十日留存率',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'user-retention-rate-per-day';
    }
}
