<?php

namespace Haxibiao\Breeze\Nova\Metrics;

use Haxibiao\Breeze\Dimension;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Metrics\TrendResult;

class SiteTrafficTrend extends Trend
{
    public $name = '站点搜索量 趋势';
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

        for($i = 9;$i >= 3;$i--){
            $date = today()->subDay($i)->toDateString();
            $vlaues = DB::table('dimensions')
            ->where('group', $range)
            ->where('name','like','%搜索量%')
            ->where('date',$date)
            ->selectRaw('sum(value) as sum')
            ->first();
            if($vlaues->sum){
                $data[$date] = $vlaues->sum;
            }else{
                $data[$date] = 0;
            }

        }

        $max = max($data);
        $yesterday = $data[today()->subDay(4)->toDateString()];

        return (new TrendResult(end($data)))->trend($data)
        ->suffix("上一天: $yesterday  最大: $max");
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        $Dimensions = Dimension::distinct('group')->get();
        $data = [];
        foreach ($Dimensions as $Dimension) {
            if($Dimension->group){
                $data[$Dimension->group] = $Dimension->group;
            }
        }
        return $data;
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
        return 'site-traffic-trend';
    }
}
