<?php

namespace Haxibiao\Breeze\Traits;

use App\Comment;
use App\Message;
use App\Post;
use App\SignIn;
use App\UserProfile;
use App\UserRetention;

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
            if (is_null($item['name'])) {
                $item['name'] = '未知';
            }
        }
        return $this->buildPartitionResponse($data, '下载版本分布');
    }

    public function resolveSourcePartition($root, $args, $context, $info)
    {
        return $this->buildPartitionResponse($this->groupByPartition(new UserProfile, 'source')->toArray(), '下载渠道分布');
    }

    public function resolveMessagesTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);

        return $this->buildTrendResponse($this->groupByDayTrend(new Message, $range), '新私信趋势');
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
        $dimensionKeys = data_get($args, 'dimensions', []);

        return $this->getDimensions($dimensionKeys);
    }

    public function resolveMockTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        $data  = $this->initTrendData($range);

        return $this->buildTrendResponse($data, 'mock trend:' . $info->fieldName);
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
                'yesterday' => $data[today()->subDay()->toDateString()] ?? 0,
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
