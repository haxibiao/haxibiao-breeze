<?php

namespace Haxibiao\Breeze\Nova\Metrics;

use Haxibiao\Breeze\Dimension;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class AdShowTrend extends Trend
{
    public $name = '广告展示 趋势';
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $qb = Dimension::whereGroup('广告展示');

        $result = $this->sumByDays($request, $qb, 'value', 'date');
        $arr    = $result->trend;
        array_pop($arr);
        $yesterday = last($arr);
        $max       = count($arr);

        return $result->showLatestValue()
            ->suffix("昨日: $yesterday 次 最大: $max 次");

    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30 => '最近30天内',
            7  => '最近7天内',
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
        return 'ad-show-trend';
    }
}
