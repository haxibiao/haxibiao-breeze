<?php

namespace Haxibiao\Breeze\Nova\Metrics;

use Haxibiao\Breeze\Dimension;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class AdClickPartition extends Partition
{
    public $name = '广告点击分布 (今日)';
    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $qb = Dimension::whereGroup('广告点击')
            ->where('date', '>=', today()->toDateString());
        return $this->sum($request, $qb, 'value', 'name');
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
        return 'ad-click-partition';
    }
}
