<?php

namespace Haxibiao\Breeze\Traits;

use App\MediaTrack;
use App\PangleReport;
use App\User;
use App\UserRetention;
use Illuminate\Support\Arr;

trait DimensionAnalyzer
{
    public function getDimensions(array $dimensionKeys)
    {
        $allDimensions = [
            'NEW_USERS_YESTERDAY'                                     => [
                'name'  => '昨日新增用户',
                'value' => function () {
                    return User::yesterDay()->count();
                },
                'tips'  => function () {
                    return '累计用户:' . User::count();
                },
                'style' => 1,
            ],
            'ADCODE_REVENUE_YESTERDAY'                                => [
                'name'  => '昨日收益(元)',
                'value' => function () {
                    $value  = 0;
                    $appIds = config('ad.pangle.appIds');
                    if (count($appIds)) {
                        $value = PangleReport::whereIn('app_id', $appIds)
                            ->whereDate('reported_date', today()->subDay()->format('Y-m-d'))
                            ->sum('revenue');
                    }

                    return $value;
                },
                'tips'  => function () {
                    $amount = 0;
                    $appIds = config('ad.pangle.appIds');
                    if (count($appIds)) {
                        $amount = PangleReport::whereIn('app_id', $appIds)->sum('revenue');
                    }

                    return '累计收益:' . $amount;
                },
                'style' => 2,
            ],
            'USER_RETENTION_YESTERDAY'                                => [
                'name'  => '次日留存',
                'value' => function () {
                    return UserRetention::getCachedValue(2, today()->subDay()->format('Y-m-d'));
                },
                'tips'  => function () {
                    $yesterdayValue = UserRetention::getCachedValue(2, today()->subDay()->format('Y-m-d'));
                    $compareValue   = UserRetention::getCachedValue(2, today()->subDay(2)->format('Y-m-d'));
                    $percent        = (($compareValue - $yesterdayValue) * 100) . '%';
                    return '前日同期:' . $percent;
                },
                'style' => 3,
            ],
            'TOTAL_USERS'                                             => [
                'name'  => '累积用户数',
                'value' => function () {
                    return User::count();
                },
                'tips'  => '',
                'style' => 3,
            ],
            'NEW_USERS_TODAY'                                         => [
                'name'  => '今日新增用户',
                'value' => function () {
                    return User::today()->count();
                },
                'tips'  => '',
                'style' => 3,
            ],
            'ACTIVE_USERS_TODAY'                                      => [
                'name'  => '今日活跃数',
                'value' => function () {
                    return User::where('updated_at', '>=', today())->count();
                },
                'tips'  => '',
                'style' => 3,
            ],
            'TOTAL_AD_REVENUE'                                        => [
                'name'  => '累积广告收益',
                'value' => function () {
                    $value  = 0;
                    $appIds = config('ad.pangle.appIds');
                    if (count($appIds)) {
                        $value = PangleReport::whereIn('app_id', $appIds)->sum('revenue');
                    }

                    return $value;
                },
                'tips'  => '',
                'style' => 3,
            ],
            'YESTERDAY_AD_REVENUE'                                    => [
                'name'  => '昨日广告收益',
                'value' => function () {
                    $value  = 0;
                    $appIds = config('ad.pangle.appIds');
                    if (count($appIds)) {
                        $value = PangleReport::whereDate('reported_date', today()->subDay())
                            ->whereIn('app_id', $appIds)
                            ->sum('revenue');
                    }

                    return $value;
                },
                'tips'  => '',
                'style' => 3,
            ],
            'YESTERDAY_AD_IPMCNT'                                     => [
                'name'  => '昨日广告展示次数',
                'value' => function () {
                    $value  = 0;
                    $appIds = config('ad.pangle.appIds');
                    if (count($appIds)) {
                        $value = PangleReport::whereDate('reported_date', today()->subDay())
                            ->whereIn('app_id', $appIds)
                            ->sum('ipm_cnt');
                    }

                    return $value;
                },
                'tips'  => '',
                'style' => 3,
            ],
            'USER_WATCH_MOVIE_DURATION'                               => [
                'name'  => '用户观影时长',
                'value' => function () {
                    return MediaTrack::where('media_type', 'movies')->sum('track_seconds');
                },
                'tips'  => '',
                'style' => 3,
            ],
            'MOVIE_PLAY_COUNT'                                        => [
                'name'  => '视频播放次数',
                'value' => function () {
                    return MediaTrack::where('media_type', 'movies')->count();
                },
                'tips'  => '',
                'style' => 3,
            ],
            'BOUNCE_RATE'                                             => [
                'name'  => '跳出率',
                'value' => '10%',
                'tips'  => '',
                'style' => 3,
            ],
            'USER_RETENTION_YESTERDAY_AND_RECENT_SEVEN_DAY_RETENTION' => [
                'name'  => '用户次日留存率',
                'value' => function () {
                    return UserRetention::getCachedValue(2, today()->subDay()->format('Y-m-d'));
                },
                'tips'  => function () {
                    return '七日留存率:' . UserRetention::getCachedValue(7, today()->subDay()->format('Y-m-d'));
                },
                'style' => 3,
            ],
        ];

        $data = Arr::only($allDimensions, $dimensionKeys);

        return $data;
    }

    public function groupByPartition($model, $byColumn)
    {
        return $model->selectRaw("$byColumn,count(1) as count ")
            ->groupBy($byColumn)
            ->get();
    }

    public function groupByDayTrend($model, $range, $dateColumn = 'created_at')
    {
        $data = $this->initTrendData($range);

        $model->selectRaw("distinct(date_format($dateColumn,'%m-%d')) as daily,count(1) as count ")
            ->where($dateColumn, '>=', today()->subDay($range - 1))
            ->groupBy('daily')
            ->get()
            ->each(function ($item) use (&$data) {
                $data[$item->daily] = $item->count;
            });

        if (count($data) < $range) {
            $data[date('m-d')] = '0';
        }

        return $data;
    }

    public function initTrendData($range)
    {
        for ($j = $range - 1; $j >= 0; $j--) {
            $intervalDate        = date('m-d', strtotime(now() . '-' . $j . 'day'));
            $data[$intervalDate] = '0';
        }

        return $data;
    }
}
