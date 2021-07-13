<?php

namespace Haxibiao\Breeze;

use App\User;
use App\Model;
use Illuminate\Support\Carbon;
use Haxibiao\Breeze\UserRetention;
use Illuminate\Support\Facades\DB;
use Haxibiao\Breeze\Traits\DimensionAnalyzer;
use Haxibiao\Breeze\Traits\DimensionTrendResolver;

class Dimension extends Model
{
    use DimensionTrendResolver;
    use DimensionAnalyzer;

    protected $gurded = [];

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
