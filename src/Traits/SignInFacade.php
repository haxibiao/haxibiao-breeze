<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/19
 * @Version
 */
namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\SignIn;
use Haxibiao\Task\Contribute;
use Haxibiao\Wallet\Gold;

trait SignInFacade
{
    public static function checkYesterdaySignIned($profile)
    {
        $keepSignDays      = $profile->keep_signin_days;
        $yesterDaySignIned = false;
        //昨日是否签到
        if ($keepSignDays >= 1) {
            $yesterDaySignIned = SignIn::yesterdaySigned($profile->user_id);
            $todaySigned       = SignIn::todaySigned($profile->user_id);
            if (!$todaySigned) {
                //未签到或者签到超出最大天数 && 清除
                if (!$yesterDaySignIned || $keepSignDays >= SignIn::MAX_SIGNIN_DAYS) {
                    $profile->update(['keep_signin_days' => 0]);
                }
            }
        }

        return $yesterDaySignIned;
    }

    public static function getSignInReward($day)
    {
        $goldReward       = 0;
        $contributeReward = 0;
        switch ($day) {
            case 1:
                $goldReward = 10;
                break;
            case 2:
                $goldReward = 30;
                break;
            case 3:
                $goldReward = 40;
                break;
            case 4:
                $goldReward = 40;
                break;
            case 5:
                $goldReward = 50;
                break;
            case 6:
                $goldReward = 60;
                break;
            case 7:
                $goldReward = 70;
                // JIRA:DZ-1630 区分新老用户贡献点
                $contributeReward = 10;
                break;
        }

        $rewards = [
            'gold_reward'       => $goldReward,
            'contribute_reward' => $contributeReward,
        ];

        return $rewards;
    }

    public static function keepSignInReward(SignIn $signIn, $keepSignDays)
    {
        $user = $signIn->user;

        //奖励天数0-6
        $rewards          = self::getSignInReward($keepSignDays);
        $goldReward       = $rewards['gold_reward'];
        $contributeReward = $rewards['contribute_reward'];

        //七天奖励10贡献额度
        if ($goldReward == 0 && $keepSignDays >= 7) {
            $contributeReward = SignIn::CONTRIBUTE_REWARD;
            Contribute::rewardSignIn($user, $signIn, $contributeReward);
        } else {
            Gold::makeIncome($user, $goldReward, '连续签到奖励');
        }
        //保存奖励
        $signIn->gold_reward       = $goldReward;
        $signIn->contribute_reward = $contributeReward;
        $signIn->save();
    }
}
