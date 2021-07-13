<?php

namespace Haxibiao\Breeze\Traits;

trait DimensionAnalyzer
{
    public function groupByPartition($model, $byColumn, $asColumn = 'name')
    {
        return $model::selectRaw("$byColumn as $asColumn,count(1) as count ")
            ->groupBy($asColumn)
            ->get();
    }

    public function groupByDayTrend($model, $range, $dateColumn = 'created_at')
    {
        $data = $this->initTrendData($range);

        $model::selectRaw("distinct(date_format($dateColumn,'%m-%d')) as daily,count(1) as count ")
            ->where($dateColumn, '>=', now()->subDay($range - 1))
            ->groupBy('daily')
            ->get()
            ->each(function ($item) use (&$data) {
                $data[$item->daily] = $item->count;
            });

        if (count($data) < $range) {
            $data[now()->toDateString()] = 0;
        }

        return $data;
    }

    public function initTrendData($range)
    {
        for ($j = $range - 1; $j >= 0; $j--) {
            $intervalDate        = date('m-d', strtotime(now() . '-' . $j . 'day'));
            $data[$intervalDate] = 0;
        }

        return $data;
    }
}
