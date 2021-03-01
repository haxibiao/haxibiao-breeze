<?php

namespace Haxibiao\Breeze\Nova\Metrics;

use App\Withdraw;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class SecondWithdrawTrend extends Trend
{
    public $name = '提现0.5元趋势(笔)';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $qb     = Withdraw::where('amount', 0.5);
        $result = $this->countByDays($request, $qb);
        $arr    = $result->trend;
        array_pop($arr);
        $yesterday = last($arr);
        $max       = max($arr);

        return $result->showLatestValue()
            ->suffix("昨日: $yesterday 笔 最大: $max 笔");

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
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'second-withdraw-trend';
    }
}
