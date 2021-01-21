<?php

namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\BlackList;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\User;
use Haxibiao\Breeze\UserData;
use Haxibiao\Breeze\UserProfile;
use Haxibiao\Breeze\UserRetention;
use Haxibiao\Sns\Follow;
use Haxibiao\Task\Contribute;
use Haxibiao\Task\RewardCounter;
use Haxibiao\Wallet\Exchange;
use Haxibiao\Wallet\Gold as AppGold;
use Haxibiao\Wallet\Wallet;
use Haxibiao\Wallet\Withdraw;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

trait UserAttrs
{

    //属性

    /**
     * 用户资料
     */
    public function getProfileAttribute()
    {
        if ($profile = $this->user_profile) {
            return $profile;
        }
        $profile = UserProfile::firstOrCreate(['user_id' => $this->id]);
        return $profile;
    }

    // public function getProfileAttribute()
    // {
    //     if ($profile = $this->hasOne(Profile::class)->first()) {
    //         return $profile;
    //     }
    //     if (empty($this) || empty($this->id)) {
    //         return;
    //     }
    //     //确保profile数据完整
    //     $profile          = new Profile();
    //     $profile->user_id = $this->id;
    //     $profile->save();
    //     return $profile;
    // }

    /**
     * 用户数据
     */
    public function getDataAttribute()
    {
        if ($data = $this->user_data) {
            return $data;
        }
        $data = UserData::firstOrCreate(['user_id' => $this->id]);
        return $data;
    }

    /**
     * 留存档案
     */
    public function getRetentionAttribute()
    {
        if ($retention = $this->user_retention) {
            return $retention;
        }
        //补刀创建
        return UserRetention::firstOrCreate(['user_id' => $this->id]);
    }

    /**
     * 用户是否被禁用
     *
     * @return bool
     */
    public function getIsDisableAttribute()
    {
        return $this->status == User::DISABLE_STATUS;
    }

    /**
     * 是否为刷子
     *
     * @return bool
     */
    public function getIsShuaZiAttribute()
    {
        //UT时跳过
        if (is_testing_env()) {
            return false;
        }

        if ($wallet = $this->wallet) {

            //未进行绑定支付宝
            if (empty($wallet->pay_account)) {
                return false;
            }
            //当前账号多次提现 并且钱包不一致
            $withdraws = Withdraw::with(['wallet'])->where(function ($query)
                 use ($wallet) {
                    $query->where('to_account', $this->account)
                        ->orWhere('to_account', $wallet->pay_account);
                })->where('wallet_id', '!=', $wallet->id)
                ->get();
            foreach ($withdraws as $withdraw) {
                $user = $withdraw->user;
                if ($user->status == $this->ENABLE_STATUS) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getAmountNeedDayContributes($amount)
    {
        return $amount * 60;
    }

    public function getNextRewardVideoTimeAttribute()
    {
        return 0;
    }

    public function getAvatarAttribute()
    {
        $avatar = $this->getRawOriginal('avatar');
        if (is_null($avatar)) {
            return self::getDefaultAvatar();
        }

        if (str_contains($avatar, 'http')) {
            return $avatar;
        }
        return cdnurl($avatar);
    }

    /**
     *
     * 高额提现限量抢成功率 2倍
     *
     * @return int
     */
    public function getDoubleHighWithdrawCardsCountAttribute()
    {
        return $this->countByHighWithdrawCardsRate();
    }
    /**
     *
     * 高额提现限量抢成功率 5倍
     *
     * @return int
     */
    public function getFiveTimesHighWithdrawCardsCountAttribute()
    {
        return $this->countByHighWithdrawCardsRate();
    }

    /**
     *
     * 高额提现限量抢成功率 10倍
     *
     * @return int
     */
    public function getTenTimesHighWithdrawCardsCountAttribute()
    {
        return $this->countByHighWithdrawCardsRate();
    }
    /**
     *
     * 高额提现令牌数  3元
     *
     * @return mixed
     */
    public function getThreeYuanWithdrawBadgesCountAttribute()
    {
        return $this->countByHighWithdrawBadgeCount(3);
    }
    /**
     *
     * 高额提现令牌数  5元
     *
     * @return mixed
     */
    public function getFiveYuanWithdrawBadgesCountAttribute()
    {
        return $this->countByHighWithdrawBadgeCount(5);
    }
    /**
     *
     * 高额提现令牌数  10元
     *
     * @return mixed
     */
    public function getTenYuanWithdrawBadgesCountAttribute()
    {
        return $this->countByHighWithdrawBadgeCount(10);
    }

    //用户激励视频的计数器
    public function getRewardCounterAttribute()
    {
        $counter = $this->hasOne(RewardCounter::class)->first();
        if (!$counter) {
            $counter = RewardCounter::firstOrCreate([
                'user_id' => $this->id,
            ]);
        }
        return $counter;
    }

    public function getGoldAttribute()
    {
        return $this->goldWallet->goldBalance;
    }

    //rmb钱包，默认钱包
    public function getWalletAttribute()
    {
        if ($wallet = $this->wallets()->whereType(Wallet::RMB_WALLET)->first()) {
            return $wallet;
        }

        return Wallet::rmbWalletOf($this);
    }

    public function getIsExchangeTodayAttribute()
    {
        return $this->exchanges()->whereDate('created_at', \Carbon\Carbon::today())->count() > 0;
    }

    //兼容前端:下一版去掉
    public function getIsWalletAttribute()
    {
        $wallet = $this->wallets()->whereType(0)->first();

        return $wallet;
    }

    //金币钱包
    public function getGoldWalletAttribute()
    {
        if ($wallet = $this->wallets()->whereType(Wallet::GOLD_WALLET)->first()) {
            return $wallet;
        }
        return Wallet::goldWalletOf($this);
    }

    //TODO 临时过渡
    public function getCashAttribute()
    {
        $transaction = $this->transactions()
            ->latest('id')->first();
        if (!$transaction) {
            return 0;
        }
        return $transaction->balance;
    }

    public function checkAdmin()
    {
        return $this->is_admin;
    }

    public function checkEditor()
    {
        return $this->is_editor || $this->is_admin;
    }

    public function isBlack()
    {

        $black    = BlackList::where('user_id', $this->id);
        $is_black = $black->exists();
        return $is_black;
    }

    public function link()
    {
        return '<a href="/user/' . $this->id . '">' . $this->name . '</a>';
    }

    public function at_link()
    {
        return '<a href="/user/' . $this->id . '">@' . $this->name . '</a>';
    }

    public function ta()
    {
        return $this->isSelf() ? '我' : '他';
    }

    public function isSelf()
    {
        return Auth::check() && Auth::id() == $this->id;
    }

    public function getExchangeRateAttribute()
    {
        return Exchange::RATE;
    }

    public function getTotalContributionAttribute()
    {
        //TODO: sum比取last费时，可以用total替代这个...
        return $this->contributes()->sum('amount');
    }

    public function getIsFollowedAttribute()
    {
        //FIXME: fixme
        return false;
    }

    public function getIsEditorAttribute()
    {
        return $this->role_id >= 1;
    }

    public function getIsAdminAttribute()
    {
        return $this->role_id >= 2;
    }

    public function getTokenAttribute()
    {
        return $this->api_token;
    }

    public function getBalanceAttribute()
    {
        $balance = 0;
        $wallet  = $this->wallet;
        if (!$wallet) {
            return 0;
        }

        $last = $wallet->transactions()->orderBy('id', 'desc')->first();
        if ($last) {
            $balance = $last->balance;
        }
        return $balance;
    }

    public function getFollowedIdAttribute()
    {
        return $this->remember('followed_id', 0, function () {
            if ($user = checkUser()) {
                $follow = Follow::where([
                    'user_id'       => $user->id,
                    'followed_type' => 'users',
                    'followed_id'   => $this->id,
                ])->select('id')->first();
                if (!is_null($follow)) {
                    return $follow->id;
                }
            }
            return null;
        });
    }

    // public function getQqAttribute()
    // {
    //     return $this->profile->qq;
    // }

    // public function getJsonAttribute()
    // {
    //     return $this->profile->Json;
    // }

    public function getIntroductionAttribute()
    {
        return $this->remember('introduction', 0, function () {
            $introduction = optional($this->profile)->introduction;
            if ($introduction) {
                return $introduction;
            }
            return '这个人很懒，一点介绍都没留下...';
        });
    }

    //unreads
    public function getUnreadCommentsAttribute()
    {
        return $this->unreads('comments');
    }

    public function getUnreadLikesAttribute()
    {
        return $this->unreads('likes');
    }

    public function getUnreadChatAttribute()
    {
        return $this->unreads('chat');
    }
    public function getUnreadFollowsAttribute()
    {
        return $this->unreads('follows');
    }

    public function getUnreadRequestsAttribute()
    {
        return $this->unreads('requests');
    }

    public function getUnreadTipsAttribute()
    {
        return $this->unreads('tips');
    }

    public function getUnreadOthersAttribute()
    {
        return $this->unreads('others');
    }

    public function getCountPostsAttribute()
    {
        return $this->remember('count_posts', 0, function () {
            return $this->posts()->count();
        });
    }

    public function getCountProductionAttribute()
    {
        return $this->articles()->count();
    }

    public function getCountFollowersAttribute()
    {
        return $this->count_follows;
    }

    public function getCountFollowingsAttribute()
    {
        return $this->remember('count_followings', 0, function () {
            return $this->followingUsers()->count();
        });
    }

    public function getCountDraftsAttribute()
    {
        return $this->drafts()->count();
    }

    public function getRewardAttribute()
    {
        return $this->getBalanceAttribute();
        //临时过渡使用
        //        $gold = $this->gold;
        //        if($gold<600){
        //            return 0;
        //        }
        //        return intval($gold/600);
    }

    //TODO: 这些可以后面淘汰，前端直接访问 user->profile->atts 即可
    public function getCountArticlesAttribute()
    {
        return $this->allArticles()->where("status", ">", 0)->count();
    }

    public function getCountFollowsAttribute()
    {
        return $this->remember('count_follows', 0, function () {
            return $this->profile->count_follows;
        });
    }

    public function getCountCollectionsAttribute()
    {
        return $this->profile->count_collections;
    }

    public function getCountFavoritesAttribute()
    {
        return $this->remember('count_favorites', 0, function () {
            return $this->profile->count_favorites;
        });
    }

    public function getGenderAttribute()
    {
        return $this->profile->gender;
    }

    public function getGenderMsgAttribute()
    {
        return $this->remember('gender', 0, function () {
            switch ($this->profile->gender) {
                case User::MALE_GENDER:
                    return '男';
                    break;
                case User::FEMALE_GENDER:
                    return '女';
                    break;
                default:
                    return "女";
            }
        });
    }

    public static function getGenderNumber($gender)
    {
        switch ($gender) {
            case '男':
                return User::MALE_GENDER;
                break;
            case '女':
                return User::FEMALE_GENDER;
                break;
            default:
                return User::FEMALE_GENDER;
        }
    }

    public function getAgeAttribute()
    {
        return $this->remember('age', 0, function () {
            $birthday = Carbon::parse($this->birthday);
            return $birthday->diffInYears(now(), false);
        });
    }

    public function getBirthdayMsgAttribute()
    {
        return date('Y-m-d', strtotime($this->birthday));
    }

    public function getBirthdayAttribute()
    {
        return $this->profile->birthday;
    }

    public function getContributeAttribute()
    {
        return $this->profile->count_contributes;
    }

    public function getTodayContributeAttribute()
    {
        $amount = Contribute::where('user_id', $this->id)->where('amount', '>', 0)->whereDate('created_at', today())->sum('amount');
        if ($amount <= 0) {
            return 0;
        }
        return $amount;
    }

    //默认都自动绑定懂得赚
    public function getIsBindDongdezhuanAttribute()
    {
        return true;
    }

    public function getDongdezhuanUserAttribute()
    {
        return $this->getDDZUser();
    }

    public function getForceAlertAttribute()
    {
        return $this->isWithdrawBefore();
    }

    public function getTitlePhoneAttribute()
    {
        if ($this->phone !== null) {
            return substr_replace($this->phone, '****', 3, 4);
        }
        return null;
    }

    //检查用户异常获取 积分的行为
    public function checkRewardCount($user, $remark)
    {

        $todayCount = AppGold::where([
            'remark'  => $remark,
            'user_id' => $user->id,
        ])->whereBetween('created_at', [today(), now()])->count();

        switch ($remark) {
            case "观看激励视频奖励":
            case "点击激励视频奖励":
                $maxCount = 8;
                break;
            case "双倍签到奖励":
            case "签到视频观看奖励":
                $maxCount = 1;
                break;
            default:
                $maxCount = 1;
        }
        throw_if($todayCount >= $maxCount, GQLException::class, '今天的次数已经用完了哦');

        $lastReward = $user->golds()
            ->select('created_at')
            ->where('remark', $remark)
            ->latest('id')
            ->first();

        // 上次看激励视频与本次间隔 < 60 秒
        if ($lastReward && now()->diffInSeconds(Carbon::parse($lastReward->created_at)) < 2) {
            $user->update(['status' => User::STATUS_FREEZE]);
            throw new GQLException('行为异常,详情咨询QQ群:326423747');
        }
    }
}