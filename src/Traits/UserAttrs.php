<?php

namespace Haxibiao\Breeze\Traits;

use App\Invitation;
use App\UserProfile;
use App\Wallet;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\User;
use Haxibiao\Breeze\UserData;
use Haxibiao\Breeze\UserRetention;
use Haxibiao\Sns\Follow;
use Haxibiao\Task\Contribute;
use Haxibiao\Task\RewardCounter;
use Haxibiao\Wallet\Exchange;
use Haxibiao\Wallet\Gold as AppGold;
use Haxibiao\Wallet\Withdraw;
use Illuminate\Support\Carbon;

trait UserAttrs
{
    public function getInviterAttribute()
    {
        $invitation = Invitation::withoutGlobalScope('hasInvitedUser')->where('be_inviter_id', $this->id)->first();
        return data_get($invitation, 'user');
    }

    public function getIsAssociateMasterAccountAttribute()
    {
        if (currentUser(false)) {
            if ($this->master_id && $this->role_id == User::VEST_STATUS) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getEnableTipsAttribute()
    {
        return $this->profile && $this->profile->enable_tips;
    }

    public function getTaAttribute()
    {
        return $this->isSelf() ? '我' : '他';
    }

    //属性

    /**
     * 用户资料
     */
    public function getProfileAttribute()
    {
        if ($profile = $this->attributes['profile'] ?? null) {
            return $profile;
        }

        $profile     = UserProfile::firstOrNew(['user_id' => $this->id]);
        $app_version = request()->header('version', null);
        //用户开始新增时激活的版本号
        if ($app_version) {
            $profile->app_version = $app_version;
            $profile->save();
        }
        return $profile;
    }

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
        return User::DISABLE_STATUS == $this->status;
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
                if ($user) {
                    if ($user->status == $this->ENABLE_STATUS) {
                        return true;
                    }
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

    public function getAvatarAttribute($value)
    {
        $avatar = $value;
        if (str_contains(((string) $avatar), "http")) {
            return $avatar;
        }
        //FIXME: 答赚的 user->avatar 字段存的还不是标准的 cos_path, 答妹已修复 “cos:%” ...
        if (empty($avatar)) {
            $avatar_url = sprintf('https://cos.haxibiao.com/avatars/avatar-%d.png', mt_rand(1, 15));
        } else {
            $avatar_url = cdnurl($avatar);
        }

        //一分钟内的更新头像刷新cdn
        if ($this->updated_at > now()->subSeconds(60)) {
            $avatar_url = $avatar_url . '?t=' . now()->timestamp;
        }

        return $avatar_url;
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

    public function getIsStoreAttribute()
    {
        return $this->stores()->exists();
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
        return $this->remember('followable_id', 0, function () {
            if ($user = currentUser()) {
                $follow = Follow::where([
                    'user_id'         => $user->id,
                    'followable_type' => 'users',
                    'followable_id'   => $this->id,
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
            return User::INTRODUCTION;
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
        return $this->unreads('chats');
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
        return $this->remember('count_followers', 0, function () {
            return $this->followers()->count();
        });
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
        //兼容答赚
        $gender = $this->getRawOriginal('gender');

        //兼容工厂
        if (is_null($gender)) {
            if ($this->id) {
                $gender = data_get($this, 'profile.gender', null);
            }
        }
        //默认
        if (is_null($gender)) {
            $gender = 0;
        }
        return $gender;
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

    public function getTodayContributesAttribute()
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

    public function getDDZUser()
    {
        return null;
    }

    public function getForceAlertAttribute()
    {
        return $this->isWithdrawBefore();
    }

    public function getTitlePhoneAttribute()
    {
        if (null !== $this->phone) {
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

    public function getFollowedStatusAttribute()
    {
        $user = currentUser();

        if (!is_null($user)) {
            return $user->isFollow('users', $this->id);
        }

        return null;
    }

    public function getCountWordsAttribute()
    {
        return $this->profile->count_words;
    }
}
