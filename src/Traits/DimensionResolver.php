<?php

namespace Haxibiao\Breeze\Traits;

use App\Comment;
use App\MediaTrack;
use App\Message;
use App\Movie;
use App\PangleReport;
use App\Post;
use App\SignIn;
use App\UserProfile;
use App\UserRetention;
use Haxibiao\Media\MovieType;

trait DimensionResolver
{
    public function resolveUsersTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);

        return $this->buildTrendResponse($this->groupByDayTrend(new Comment, $range), '用户增长趋势');
    }

    public function resolvePostsTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);

        return $this->buildTrendResponse($this->groupByDayTrend(new Post, $range), '动态增长趋势');
    }

    public function resolveCommentsTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);

        return $this->buildTrendResponse($this->groupByDayTrend(new Comment, $range), '评论增长趋势');
    }

    public function resolveActiveUsersTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);

        return $this->buildTrendResponse($this->groupByDayTrend(new SignIn, $range), '活跃用户趋势');
    }

    public function resolveAppVersionPartition($root, $args, $context, $info)
    {
        $data = $this->groupByPartition(new UserProfile, 'app_version')->toArray();
        foreach ($data as &$item) {
            $version      = empty($item['app_version']) ? '未知' : $item['app_version'];
            $item['name'] = $version;
        }
        return $this->buildPartitionResponse($data, '下载版本分布');
    }

    public function resolveSourcePartition($root, $args, $context, $info)
    {
        $data = $this->groupByPartition(new UserProfile, 'phone_brand')->toArray();
        foreach ($data as &$item) {
            $brand               = empty($item['下载渠道分布']) ? '未知' : $item['下载渠道分布'];
            $item['phone_brand'] = $brand;
        }
        return $this->buildPartitionResponse($data, '下载渠道分布');
    }

    public function resolveMessagesTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);

        return $this->buildTrendResponse($this->groupByDayTrend(new Message, $range), '新私信趋势');
    }

    public function resolveCpmTrend($root, $args, $context, $info)
    {
        $range  = data_get($args, 'range', 7);
        $data   = $this->initTrendData($range);
        $appIds = config('ad.pangle.appIds', );

        PangleReport::selectRaw("distinct(date_format(reported_date,'%m-%d')) as daily,sum(revenue) * 1000 / sum(ipm_cnt) as cpm ")
            ->where('reported_date', '>=', today()->subDay($range - 1))
            ->whereIn('app_id', $appIds ?? [0])
            ->groupBy('daily')
            ->get()
            ->each(function ($item) use (&$data) {
                $data[$item->daily] = number_format($item->cpm, 2);
            });

        if (count($data) < $range) {
            $data[date('m-d')] = '0';
        }

        return $this->buildTrendResponse($data, 'CPM趋势');

    }

    public function resolveAdRevenue($root, $args, $context, $info)
    {
        $range  = data_get($args, 'range', 7);
        $data   = $this->initTrendData($range);
        $appIds = config('ad.pangle.appIds', );

        PangleReport::selectRaw("distinct(date_format(reported_date,'%m-%d')) as daily,sum(revenue) as daily_revenue ")
            ->where('reported_date', '>=', today()->subDay($range - 1))
            ->whereIn('app_id', $appIds ?? [0])
            ->groupBy('daily')
            ->get()
            ->each(function ($item) use (&$data) {
                $data[$item->daily] = number_format($item->daily_revenue, 2);
            });

        if (count($data) < $range) {
            $data[date('m-d')] = '0';
        }

        return $this->buildTrendResponse($data, '广告收益趋势');
    }

    public function resolveAdCodeRevenue($root, $args, $context, $info)
    {
        $range           = data_get($args, 'range', 7);
        $defaultItemData = $this->initTrendData($range);
        $appIds          = config('ad.pangle.appIds', );
        $groupData       = [];
        PangleReport::selectRaw("distinct(date_format(reported_date,'%m-%d')) as daily,code_type,sum(revenue) as daily_revenue ")
            ->where('reported_date', '>=', today()->subDay($range - 1))
            ->groupBy(['daily', 'code_type'])
            ->whereIn('app_id', $appIds ?? [0])
            ->get()
            ->each(function ($item) use (&$groupData) {
                $groupData[$item->code_type_name][$item->daily] = number_format($item->daily_revenue, 2);
            });
        $data = array_values($groupData);
        foreach ($data as &$item) {
            $item = array_replace($defaultItemData, $item);
        }

        $result = [
            'label'  => array_keys($defaultItemData),
            'data'   => array_values($data),
            'legend' => array_keys($groupData),
        ];

        return $result;
    }

    public function resolveUserRetentionTrend($root, $args, $context, $info)
    {
        $range  = data_get($args, 'range', 7);
        $dayNum = data_get($args, 'day_num', 2);
        $data   = $this->initTrendData($range);

        foreach (array_keys($data) as $date) {
            $data[$date] = UserRetention::getCachedValue(2, $date);
        }

        return $this->buildTrendResponse($data, $dayNum . '日留存率');
    }

    public function resolveBusinessDimension()
    {
        return $this->getDimensions(['NEW_USERS_YESTERDAY', 'ADCODE_REVENUE_YESTERDAY', 'USER_RETENTION_YESTERDAY']);
    }

    public function resolveDimensionManager($root, $args, $context, $info)
    {
        $dimensionKeys = data_get($args, 'dimension', []);

        return $this->getDimensions($dimensionKeys);
    }

    public function resolveMockTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        $data  = $this->initTrendData($range);

        return $this->buildTrendResponse($data, 'mock trend:' . $info->fieldName);
    }

    public function resolveMoviePlayTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);

        return $this->buildTrendResponse($this->groupByDayTrend(new MediaTrack, $range), '长视频播放量趋势');
    }

    public function resolveMovieRegionPartition()
    {
        return $this->buildPartitionResponse($this->groupByPartition(Movie::whereIn('id', function ($query) {
            return $query->select('media_id')
                ->from('media_tracks')
                ->where('media_type', 'movies');
        }), 'region')->toArray(), '长视频地区偏好');
    }

    public function resolveMovieTypePartition()
    {
        return $this->buildPartitionResponse($this->groupByPartition(MovieType::whereIn('movie_id', function ($query) {
            return $query->select('media_id')
                ->from('media_tracks')
                ->where('media_type', 'movies');
        })->join('types', 'types.id', 'movie_types.type_id'), 'types.name')->toArray(), '长视频地区偏好');
    }

    public function resolveMockPartition($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        for ($j = $range - 1; $j >= 0; $j--) {
            $intervalDate = date('Y-m-d', strtotime(now() . '-' . $j . 'day'));
            $data[]       = [
                'name'  => $intervalDate,
                'value' => mt_rand(1, 30),
            ];
        }
        $result = [
            'name' => 'mock partition:' . $info->fieldName,
            'data' => $data,
        ];

        return $result;
    }

    public function buildTrendResponse(array $data, $name = '')
    {
        return [
            'name'    => $name,
            'summary' => [
                'max'       => max($data),
                'yesterday' => $data[today()->subDay()->format('m-d')] ?? '0',
            ],
            'data'    => $data,
        ];
    }

    public function buildPartitionResponse(array $data, $name = '')
    {
        return [
            'name' => $name,
            'data' => $data,
        ];
    }

}
