<?php

namespace Haxibiao\Breeze\Traits;

trait DimensionAnalyzer
{

    public function groupByDay($model, $range, $byColumn = 'created_at')
    {
        $data = $this->initTrendData($range);

        $model::selectRaw("distinct(date_format(created_at,'%Y-%m-%d')) as daily,count(1) as count ")
            ->where($byColumn, '>=', now()->subDay($range - 1))
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
            $intervalDate        = date('Y-m-d', strtotime(now() . '-' . $j . 'day'));
            $data[$intervalDate] = 0;
        }

        return $data;
    }

    public function initPartitionData($range)
    {
        for ($j = $range - 1; $j >= 0; $j--) {
            $intervalDate = date('Y-m-d', strtotime(now() . '-' . $j . 'day'));
            $data[]       = [
                'name'  => $intervalDate,
                'value' => 0,
            ];
        }

        return $data;
    }
}
