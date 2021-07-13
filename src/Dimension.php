<?php

namespace Haxibiao\Breeze;

use App\Model;
use App\User;
use Haxibiao\Breeze\UserRetention;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Dimension extends Model
{
    protected $gurded = [];

    //resolvers - 待重构

    public function resolveUsersTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        $data  = get_users_trend($range);

        return [
            'name'    => '用户增长趋势',
            'summary' => [
                'max'       => max($data),
                'yesterday' => array_values($data)[$range - 2],
            ],
            'data'    => $data,
        ];
    }

    public function resolvePostsTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        $data  = get_posts_trend($range);

        return [
            'name'    => '动态增长趋势',
            'summary' => [
                'max'       => max($data),
                'yesterday' => array_values($data)[$range - 2],
            ],
            'data'    => $data,
        ];
    }

    public function resolveCommentsTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        $data  = get_comments_trend($range);

        return [
            'name'    => '评论增长趋势',
            'summary' => [
                'max'       => max($data),
                'yesterday' => array_values($data)[$range - 2],
            ],
            'data'    => $data,
        ];
    }

    public function resolveActiveUsersTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        $data  = $this->initData($range);
        $items = SignIn::selectRaw("distinct(date_format(created_at,'%Y-%m-%d')) as daily,count(1) as count ")
            ->where('created_at', '>=', now()->subDay($range - 1))
            ->groupBy('daily')
            ->get();

        $items->each(function ($item) use (&$data) {
            $data[$item->daily] = $item->count;
        });

        if (count($data) < $range) {
            $data[now()->toDateString()] = 0;
        }

        return $this->buildTrend($data, '活跃用户趋势');

    }

    public function resolveMockTrend($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        $data  = $this->initData($range);

        return $this->buildTrend($data, 'mock trend:' . $info->fieldName);
    }

    public function resolveMockPartition($root, $args, $context, $info)
    {
        $range = data_get($args, 'range', 7);
        for ($j = $range - 1; $j >= 0; $j--) {
            $intervalDate = date('Y-m-d', strtotime(now() . '-' . $j . 'day'));
            $data[]       = [
                'name'  => $intervalDate,
                'value' => mt_rand(1,30),
            ];
        }
        $result = [
            'name' => 'mock partition:' . $info->fieldName,
            'data' => $data,
        ];

        return $result;
    }

    public function initData($range)
    {
        for ($j = $range - 1; $j >= 0; $j--) {
            $intervalDate        = date('Y-m-d', strtotime(now() . '-' . $j . 'day'));
            $data[$intervalDate] = 0;
        }

        return $data;
    }

    public function buildTrend(array $data, $name = '')
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

    //repo

    //维度统计（广告）
    public static function trackAdView($name = "激励视频", $group = "广告展示")
    {
        Dimension::track($name, 1, $group);
    }

    public static function trackAdClick($name = "激励视频", $group = "广告点击")
    {
        Dimension::track($name, 1, $group);
    }

    public static function trackInviteView($name = "邀请页面", $group = "邀请展示")
    {
        Dimension::track($name, 1, $group);
    }

    public static function trackInviteClick($name = "邀请页面", $group = "邀请点击")
    {
        Dimension::track($name, 1, $group);
    }

    /**
     * 更新今日维度统计结果，主要汇总数据接口
     *
     * @param string $name 名称，如：百度蜘蛛抓取
     * @param integer $value 数值，如：10020
     * @param string $group 分组，如：SEO蜘蛛分析
     * @return void
     */
    public static function track($name, int $value = 1, $group = null)
    {
        $date = today()->toDateString();
        //每天一个维度统计一到一个记录里
        $dimension = Dimension::whereGroup($group)
            ->whereName($name)
            ->where('date', $date)
            ->first();
        if (!$dimension) {
            $dimension = Dimension::create([
                'date'  => $date,
                'group' => $group,
                'name'  => $name,
                'value' => $value,
            ]);
        } else {
            //更新数值和统计次数
            $dimension->value = $dimension->value + $value;
            $dimension->count = ++$dimension->count;
            $dimension->save();
        }

        return $dimension;
    }

    /**
     * 计算留存
     */
    public static function calculateRetention($date, $subDay, $column, $isSave = true)
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        } else {
            $date = $date->copy();
        }

        $next_day_key = $column . '_' . $date->toDateString();
        $startDay     = $date->copy()->subDay($subDay);
        $endDay       = $startDay->copy()->addDay();
        $dateRange    = [$startDay, $endDay];

        $userModel          = new User;
        $userRetentionModel = new UserRetention;
        $newRegistedNum     = User::whereBetween('created_at', $dateRange)->count(DB::raw(1));
        $userRetentionNum   = User::whereBetween($userModel->getTable() . '.created_at', $dateRange)
            ->join($userRetentionModel->getTable(), function ($join) use ($userModel, $userRetentionModel) {
                $join->on($userModel->getTable() . '.id', $userRetentionModel->getTable() . '.user_id');
            })->whereBetween($userRetentionModel->getTable() . '.' . $column, [$endDay, $date])
            ->count(DB::raw(1));
        if (0 != $userRetentionNum) {
            $next_day_result = sprintf('%.2f', ($userRetentionNum / $newRegistedNum) * 100);
            if ($isSave) {
                cache()->store('database')->forever($next_day_key, $next_day_result);
            }
            return $next_day_result;
        }
    }
}
