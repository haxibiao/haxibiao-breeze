<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/19
 * @Version
 */

namespace Haxibiao\Breeze\Traits;

use App\Notice;
use App\User;
use Carbon\Carbon;
use Haxibiao\Breeze\SignIn;

trait SignInRepo
{
    /**
     * 签到
     * @param User $user
     * @return mixed
     */
    public static function checkIn(User $user)
    {
        $profile = $user->profile;
        //检查昨日是否签到,未签到则重置连续签到天数
        self::checkYesterdaySignIned($profile);
        $profile->refresh();
        //签到成功
        $signIn       = SignIn::create(['user_id' => $user->id]);
        $keepSignDays = $profile->keep_signin_days + 1;
        $profile->update(['keep_signin_days' => $keepSignDays]);

        //触发连续签到奖励 && 保存奖励
        self::keepSignInReward($signIn, $keepSignDays);
        return $signIn;
    }

    /**
     * 获取签到记录
     * @param User $user
     * @param int $days
     * @return mixed
     * @throws \Exception
     */
    public static function getSignIns(User $user, int $days)
    {
        $profile     = $user->profile;
        $todaySigned = SignIn::todaySigned($user->id);
        //检查昨日是否签到,未签到则重置连续签到天数
        self::checkYesterdaySignIned($profile);
        $profile->refresh();
        $keepSignDays = $profile->keep_signin_days;

        //该用户最近7天的签到日期
        $dates = collect([]);

        //未签到 0   today
        //今天签到 1    today
        //昨日签到 1 今天未签到 today()->subday()
        if ($keepSignDays == 0) {
            $firstDay = today();
        } else if ($keepSignDays == 1 && $todaySigned) {
            $firstDay = today();
        } else {
            if ($keepSignDays > 1) {
                $keepSignDays -= 1;
            }

            $firstDay = today()->subDay($keepSignDays);
        }

        $dates[] = (object) ['created_at' => $firstDay];
        while (count($dates) < $days) {
            $obj = ['created_at' => $dates->last()->created_at->copy()->addDay()];
            $dates->push((object) ($obj));
        }

        //已签到的时间
        $signs       = [];
        $userSignIns = $user->signIns()->where('created_at', '>=', $dates->first()->created_at)->take($days)->get();
        foreach ($userSignIns as $signIn) {
            $createdAt = Carbon::parse($signIn->created_at->toDateString());
            if ($dates->firstWhere('created_at', $createdAt)) {
                $signs[$createdAt->toDateString()] = $signIn;
            }
        }

        //填充未签到的时间数组
        foreach ($dates as $date) {
            if (!isset($signs[$date->created_at->toDateString()])) {
                //diffdays 当天时间一致 = 0 就是代表1天签到,故此+1
                $rewards = self::getSignInReward($date->created_at->diffInDays($firstDay) + 1);
                $signIn  = [
                    'created_at' => $date->created_at,
                    'date'       => $date->created_at->toDateString(),
                    'signed'     => isset($date->id) ? true : false,
                    'year'       => $date->created_at->year,
                    'month'      => $date->created_at->month,
                    'day'        => $date->created_at->day,
                ];

                $signs[$date->created_at->toDateString()] = array_merge($signIn, $rewards);
            }
        }

        Notice::pushUnReadNotice($user);

        //按照时间排序
        ksort($signs);

        //构建返回结果
        $result['signs']            = $signs;
        $result['keep_signin_days'] = $profile->keep_signin_days;
        $result['today_signed']     = (boolean) $todaySigned;
        return $result;
    }
}
