<?php

namespace Haxibiao\Breeze\Nova\Metrics;

use Haxibiao\Breeze\Dimension;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class NewUserByHour extends Trend
{

    public $name = '新用户增长趋势(小时)';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $result = Dimension::where('name', '每小时新增用户')
            ->where('date', $request->range)
            ->get();
        $data = [];
        foreach ($result as $item) {
            $data[$item->hour . ' 时'] = $item->value;
        }
        $max = !empty($data) ? max($data) : 0;
        return (new TrendResult(end($data)))->trend($data)->suffix(array_key_last($data) . " 最大:" . $max);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        $ranges = [];
        for ($i = 0; $i < 7; $i++) {
            $day          = now()->subDay($i)->toDateString();
            $ranges[$day] = $day;
        }
        return $ranges;
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return null;
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'user-new-user-by-hour';
    }
}
