<?php

namespace Haxibiao\Breeze\Traits;

use Haxibiao\Breeze\BlackList;
use Haxibiao\Breeze\CheckIn;
use Haxibiao\Breeze\Dimension;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\OAuth;
use Haxibiao\Breeze\User;
use Haxibiao\Breeze\UserProfile;
use Haxibiao\Breeze\UserRetention;
use Haxibiao\Breeze\Verify;
use Haxibiao\Helpers\utils\PhoneUtils;
use Haxibiao\Helpers\utils\WechatUtils;
use Haxibiao\Task\Contribute;
use Haxibiao\Wallet\Exchange;
use Haxibiao\Wallet\Gold;
use Haxibiao\Wallet\Transaction;
use Haxibiao\Wallet\Wallet;
use Haxibiao\Wallet\Withdraw;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UserRepo
{
    public function link()
    {
        return '<a href="/user/' . $this->id . '">' . $this->name . '</a>';
    }

    public function at_link()
    {
        return '<a href="/user/' . $this->id . '">@' . $this->name . '</a>';
    }

    /**
     * @deprecated 用 getTaAttribute
     *
     * @return string
     */
    public function ta()
    {
        return $this->isSelf() ? '我' : '他';
    }

    public function checkAdmin()
    {
        return $this->role_id >= User::ADMIN_STATUS;
    }

    public function checkEditor()
    {
        return $this->role_id >= User::EDITOR_STATUS;
    }

    public function isBlack()
    {
        $black    = BlackList::where('user_id', $this->id);
        $is_black = $black->exists();
        return $is_black;
    }

    /**
     * 保存用户头像到cloud
     */
    public function saveDownloadImage($file)
    {
        if ($file) {
            $cloud_path = 'storage/app-' . env('APP_NMAE') . '/avatars/' . $this->id . '_' . time() . '.png';
            Storage::put($cloud_path, file_get_contents($file->path()));
            return $cloud_path;
        }
    }

    //标记上次提现提交的时间
    public function withdrawAt()
    {
        $this->withdraw_at = now();
        $this->save();
    }

    public function startExchageChangeToWallet()
    {
        //账户异常
        if ($this->isShuaZi) {
            return;
        }
        //注意:此处默认为双精度 根据默认10000:1的兑换率  50智慧点上下浮动兑换会出现差距0.01
        $wallet      = $this->wallet;
        $amount      = Exchange::computeAmount($this->gold);
        $amount      = floor($amount * 100) / 100;
        $gold        = $amount * Exchange::RATE;
        $goldBalance = $this->gold - $gold;

        //兑换条件:金币余额 >= 0 && 兑换金额 > 0 && 钱包已存在
        $canExchange = $goldBalance >= 0 && $gold > 0 && !is_null($wallet);
        if (!$canExchange) {
            return null;
        }

        /**
         * 开启事务、锁住智慧点记录
         */
        DB::beginTransaction();
        //兑换状态
        try {
            //扣除智慧点
            Gold::makeOutcome($this, $gold, "兑换余额");
            //添加兑换记录
            Exchange::exchangeOut($this, $gold);
            //添加流水记录
            Transaction::makeIncome($wallet, $amount, '智慧点兑换');

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack(); //数据库回滚
            \Yansongda\Supports\Log::error($ex);
        }
    }

    /**
     * 成功提现总金额数
     *
     * @return int
     */
    public function getSuccessWithdrawAmountAttribute()
    {
        return $this->withdraws()
            ->where('withdraws.status', '>', 0)
            ->sum('withdraws.amount');
    }

    public static function userReward(\App\User $user, array $reward)
    {
        $action = Arr::get($reward, 'action');
        $result = [
            'gold'       => 0,
            'ticket'     => 0,
            'contribute' => 0,
        ];

        //记录该维度数据给运营看
        if (in_array($action, ['WATCH_REWARD_VIDEO', 'CLICK_REWARD_VIDEO', 'VIDEO_PLAY_REWARD'])) {
//            if ($action == 'VIDEO_PLAY_REWARD') {
            //                Dimension::setDimension($user, $action, $reward['gold']);
            //            } else {
            //                Dimension::setDimension($user, $action, $reward['contribute']);
            //            }
        }

        //检查是否发放过新人奖励了
        if ('NEW_USER_REWARD' == $action || 'NEW_YEAR_REWARD' == $action) {
            $hasReward = self::hasReward($user->id, $action);
            throw_if(!$hasReward, \App\Exceptions\UserException::class, '领取失败,奖励只能领取一次哦!');

        }

        //奖励次数校验
        $user->checkRewardCount($user, $reward['remark']);

        //智慧点奖励
        if (isset($reward['gold'])) {
            Gold::makeIncome($user, $reward['gold'], $reward['remark']);
            $result['gold'] = $reward['gold'];
        }

        //精力点奖励
        if (isset($reward['ticket'])) {
            if ('SUCCESS_ANSWER_VIDEO_REWARD' != $action && 'FAIL_ANSWER_VIDEO_REWARD' != $action) {
                $user->increment('ticket', $reward['ticket']);
                $result['ticket'] = $reward['ticket'];
            }
        }

        //贡献奖励
        if (isset($reward['contribute'])) {
            Contribute::rewardUserAction($user, $reward['contribute']);
            $result['contribute'] = $reward['contribute'];
        }
        //统计激励视频当天
        $profile = $user->profile;
        if ('WATCH_REWARD_VIDEO' == $action) {
            if ($profile->last_reward_video_time < today()) {
                $profile->today_reward_video_count = 1;
                $profile->last_reward_video_time   = now();
                $profile->save();
            } else {
                $profile->increment('today_reward_video_count');
                $profile->last_reward_video_time = now();
                $profile->save();
            }
            //触发分享任务
            $user->reviewTasksByClass('Contribute');
        }

        //获取今日签到奖励-连续签到奖励
        if ('KEEP_SIGNIN_REWARD' == $action) {

            $signIn = CheckIn::todaySigned($user->id);
            throw_if(is_null($signIn), UserException::class, '领取失败,请先完成签到!');

            if ($signIn->gold_reward > 0) {
                Gold::makeIncome($user, $signIn->gold_reward, $reward['remark']);
            }
            $result = [
                'gold'       => $signIn->gold_reward,
                'contribute' => $signIn->contribute_reward,
            ];

        }

        //签到额外奖励
        if ('SIGNIN_VIDEO_REWARD' == $action) {
            $signRewards = CheckIn::getSignInReward($profile->keep_checkin_days);
            //智慧点
            if (isset($signRewards['gold_reward'])) {
                Gold::makeIncome($user, $signRewards['gold_reward'], $reward['remark']);
                $result['gold'] = $signRewards['gold_reward'];
            }
            //贡献
            if (Arr::get($signRewards, 'contribute_reward', 0) > 0) {
                Contribute::rewardSignInAdditional($user, $signRewards['contribute_reward']);
                $result['contribute'] = $signRewards['contribute_reward'];
            }
        }

        //签到双倍奖励
        if ('DOUBLE_SIGNIN_REWARD' == $action) {
            $rewardRate = 2;
            $signIn     = CheckIn::todaySigned($user->id);
            throw_if(is_null($signIn), UserException::class, '领取失败,请先完成签到!');
            throw_if($signIn->reward_rate >= $rewardRate, UserException::class, '领取失败,签到双倍奖励已领取过!');

            //更新奖励倍率
            $signIn->reward_rate = $rewardRate;
            $signIn->save();
            $result = [
                'gold'       => $signIn->gold_reward,
                'contribute' => $signIn->contribute_reward,
            ];

            //双倍智慧
            if ($signIn->gold_reward > 0) {
                Gold::makeIncome($user, $result['gold'], $reward['remark']);
            }

            //双倍贡献
            if ($signIn->contribute_reward > 0) {
                Contribute::rewardSignInDoubleReward($user, $signIn, $result['contribute']);
            }
        }

        return $result;
    }

    public function getLatestWatchRewardVideoTime()
    {
        return Dimension::where('user_id', $this->id)
            ->whereIn('name', ['WATCH_REWARD_VIDEO', 'CLICK_REWARD_VIDEO'])
            ->max('updated_at');
    }
    public function smsSignIn($sms_code, $phone)
    {

        throw_if(!is_phone_number($phone), UserException::class, '手机号格式不正确!');
        throw_if(empty($sms_code), UserException::class, '验证码不能为空!');

        $qb = User::wherePhone($phone);
        Verify::checkSMSCode($sms_code, $phone, Verify::USER_LOGIN);
        if ($qb->exists()) {
            $user = $qb->first();
            if ($user->status === User::STATUS_OFFLINE) {
                throw new GQLException('登录失败！账户已被封禁');
            } else if ($user->status === User::STATUS_DESTORY) {
                throw new GQLException('登录失败！账户已被注销');
            }
            return $user;
        } else {
            //新用户注册账号
            $user          = User::getDefaultUser();
            $user->phone   = $phone;
            $user->account = $phone;
            $user->save();
            return $user;
        }
    }

    public function authSignIn($code, $type)
    {

        throw_if(!method_exists(User::class, $type), GQLException::class, '暂时只支持手机号和微信一键登录');
        $user = User::$type($code);
        return $user;
    }
    //手机号一键登录
    public function mobile($code)
    {
        $accessTokens = PhoneUtils::getInstance()->accessToken($code);

        Log::info('移动获取号码接口参数', $accessTokens);

        $token = $accessTokens['msisdn'];
        if ('103000' != $accessTokens['resultCode'] || !array_key_exists('msisdn', $accessTokens)) {
            throw new GQLException("获取手机号一键登录授权失败");
        }

        $oAuth = OAuth::firstOrNew(['oauth_type' => 'phone', 'oauth_id' => $token]);
        //已授权的老用户
        if (isset($oAuth->id)) {
            return $oAuth->user;
        }
        $qb = User::wherePhone($token);
        if ($qb->exists()) {
            $user = $qb->first();
            if ($user->status === User::STATUS_OFFLINE) {
                throw new GQLException('登录失败！账户已被封禁');
            } else if ($user->status === User::STATUS_DESTORY) {
                throw new GQLException('登录失败！账户已被注销');
            }
        }
        $user = $qb->first();
        //初次授权的新用户
        if (!isset($user)) {
            $user = User::firstOrNew([
                'phone' => $token,
            ]);
            $suffix          = strval(time());
            $user->name      = "手机用户" . $suffix;
            $user->api_token = str_random(60);
            $user->avatar    = \App\User::AVATAR_DEFAULT;
            $user->phone     = $token;
            $user->account   = $token;
            $user->save();
        }
        //初次授权的老用户
        $oAuth->user_id = $user->id;
        $oAuth->save();
        return $user;
    }

    //微信号一键登录
    public function wechat($code)
    {
        try {
            $accessTokens = WechatUtils::getInstance()->accessToken($code);
            Log::info("微信用户登录接口回参", $accessTokens);
            if (!is_array($accessTokens) || !array_key_exists('unionid', $accessTokens) || !array_key_exists('openid', $accessTokens)) {
                throw new \App\Exceptions\GQLException("获取微信登录授权失败");
            }
            $token = $accessTokens['unionid'];

            $oAuth = \App\OAuth::firstOrNew(['oauth_type' => 'wechat', 'oauth_id' => $token]);
            //已授权的老用户
            if (isset($oAuth->id)) {
                $user = $oAuth->user;
                if (isset($user)) {
                    if ($user->status === User::STATUS_OFFLINE) {
                        throw new GQLException('登录失败！账户已被封禁');
                    } else if ($user->status === User::STATUS_DESTORY) {
                        throw new GQLException('登录失败！账户已被注销');
                    }
                    return $user;
                }
            }
            //初次授权的新用户
            $oAuth->data = Arr::only($accessTokens, ['openid', 'refresh_token']);
            $oAuth->save();
            $user                   = $this->getDefaultUser();
            $wallet                 = \App\Wallet::firstOrNew(['user_id' => $user->id]);
            $wallet->wechat_account = $token;
            $wallet->save();
            //绑定微信信息
            $wechatUserInfo = WechatUtils::getInstance()->userInfo($accessTokens['access_token'], $accessTokens['openid']);
            Log::info("微信用户信息接口回参", $wechatUserInfo);
            if ($wechatUserInfo && Str::contains($user->name, \App\User::DEFAULT_NAME)) {
                WechatUtils::getInstance()->syncWeChatInfo($wechatUserInfo, $user);
                Log::info("oauth", $oAuth->data);
                $wechatData  = array_merge($oAuth->data, $wechatUserInfo);
                $oAuth->data = $wechatData;
                $oAuth->save();
            }
            $oAuth->user_id = $user->id;
            $oAuth->save();
            return $user;
        } catch (\Exception $e) {
            Log::info('异常信息' . $e->getMessage());
        }

    }

    //创建默认用户
    public static function getDefaultUser()
    {
        return User::firstOrCreate([
            'name'      => User::DEFAULT_NAME,
            'api_token' => str_random(60),
        ]);
    }

    public function followedUserIds($userIds)
    {
        return $this->follows()->select('followable_id')
            ->whereIn('followable_type', $userIds)
            ->where('followable_type', 'users')
            ->get()
            ->pluck('followable_id');
    }

    /**
     *
     * 计算高额提现倍率
     *
     * @param User $user
     * @return int
     */
    public function countByHighWithdrawCardsRate()
    {
        return 0;
    }

    /**
     *
     * 获取高额提现令牌数
     *
     * @param int $amount
     * @return int
     */
    public function countByHighWithdrawBadgeCount(int $amount)
    {
        return 0;
    }

    /**
     * 创建用户 - web game 等场景调用了
     */
    public static function createUser($name, $account, $password)
    {
        $user = new \App\User();

        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            $user->email = $account;
        }
        if (is_phone_number($account)) {
            $user->phone = $account;
        }
        $user->account   = $account;
        $user->name      = $name;
        $user->password  = bcrypt($password);
        $user->api_token = str_random(60);
        $user->save();

        $profile = UserProfile::create([
            'user_id' => $user->id,
        ]);

        // //FIXME: 记录用户的APP版本号
        // $profile->app_version = request()->header('version', null);
        // $profile->save();

        return $user;
    }

    public function canJoinChat($chatId)
    {
        $chats = $this->chats()->where('chat_id', $chatId)->first();
        if (!is_null($chats)) {
            return true;
        }
        return false;
    }

    //重写用户的重置密码邮件通知
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * 每个用户个人主页的背景图片自定义
     *
     * @param UploadFile $file
     * @return string
     */
    public function saveBackground($file)
    {
        //判断是否为空
        if (empty($file) || !$file->isValid()) {
            return null;
        }

        $extension  = $file->getClientOriginalExtension();
        $filename   = $this->id . '_' . time() . '.' . $extension;
        $cloud_path = 'storage/app-' . env('APP_NAME') . '/background/' . $filename;
        try {
            Storage::cloud()->put($cloud_path, file_get_contents($file->getRealPath()));
            $background_url = cdnurl($cloud_path);

            //save profile
            $profile             = $this->profile;
            $profile->background = $background_url;
            $profile->save();
        } catch (\Exception $e) {
            return null;
        }
        return $background_url;
    }

    public function fillForJs()
    {
        $this->introduction = $this->introduction;
        $this->avatar       = $this->avatarUrl;
    }

    public function blockedUsers()
    {
        $json = json_decode($this->json, true);
        if (empty($json)) {
            $json = [];
        }

        $blocked = [];
        if (isset($json['blocked'])) {
            $blocked = $json['blocked'];
        }
        return $blocked;
    }

    public function blockUser($user_id)
    {
        $user = User::findOrFail($user_id);
        $json = json_decode($this->json, true);
        if (empty($json)) {
            $json = [];
        }

        $blocked = [];
        if (isset($json['blocked']) && is_array($json['blocked'])) {
            $blocked = $json['blocked'];
        }

        $blocked = new \Illuminate\Support\Collection($blocked);

        if ($blocked->contains('id', $user_id)) {
            //unbloock
            $blocked = $blocked->filter(function ($value, $key) use ($user_id) {
                return $value['id'] != $user_id;
            });
        } else {
            $blocked[] = [
                'id'     => $user->id,
                'name'   => $user->name,
                'avatar' => $user->avatarUrl,
            ];
        }

        $json['blocked'] = $blocked;
        $this->json      = json_encode($json, JSON_UNESCAPED_UNICODE);
        $this->save();
    }

    public function report($type, $reason, $comment_id = null)
    {
        $this->count_reports = $this->count_reports + 1;

        $json = json_decode($this->json);
        if (!$json) {
            $json = (object) [];
        }

        $user    = getUser();
        $reports = [];
        if (isset($json->reports)) {
            $reports = $json->reports;
        }

        $report_data = [
            'type'   => $type,
            'reason' => $reason,
        ];
        if ($comment_id) {
            $report_data['comment_id'] = $comment_id;
        }
        $reports[] = [
            $user->id => $report_data,
        ];

        $json->reports = $reports;
        $this->json    = json_encode($json, JSON_UNESCAPED_UNICODE);
        $this->save();
    }

    public function transfer($amount, $to_user, $log_mine = '转账', $log_theirs = '转账', $relate_id = null, $type = "打赏")
    {
        if ($this->balance < $amount) {
            return false;
        }

        DB::beginTransaction();

        try {
            Transaction::create([
                'user_id'      => $this->id,
                'relate_id'    => $relate_id,
                'from_user_id' => $this->id,
                'to_user_id'   => $to_user->id,
                'type'         => $type,
                'log'          => $log_mine,
                'amount'       => $amount,
                'status'       => '已到账',
                'balance'      => $this->balance - $amount,
            ]);
            Transaction::create([
                'user_id'      => $to_user->id,
                'relate_id'    => $relate_id,
                'from_user_id' => $this->id,
                'to_user_id'   => $to_user->id,
                'type'         => $type,
                'log'          => $log_theirs,
                'amount'       => $amount,
                'status'       => '已到账',
                'balance'      => $to_user->balance + $amount,
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return false;
        }

        DB::commit();
        return true;
    }

    public function makeQQAvatar()
    {
        //尝试读取qq头像
        if (empty($this->email)) {
            $qq = $this->qq;
            if (empty($qq)) {
                $pattern = '/(\d+)\@qq\.com/';
                if (preg_match($pattern, strtolower($this->email), $matches)) {
                    $qq = $matches[1];
                }
            }
            if (!empty($qq)) {
                $avatar_path = '/storage/avatar/' . $this->id . '.qq.jpg';
                $qq_pic      = get_qq_pic($qq);
                $qq_img_data = @file_get_contents($qq_pic);
                if ($qq_img_data) {
                    file_put_contents(public_path($avatar_path), $qq_img_data);
                    $hash = md5_file(public_path($avatar_path));
                    if (md5_file(public_path('/images/qq_default.png')) != $hash && md5_file(public_path('/images/qq_tim_default.png')) != $hash) {
                        $this->avatar = $avatar_path;
                    }
                }
            }
        }
        $this->save();

        return $this->avatar;
    }

    public function bannedAccount()
    {
        $this->status = User::STATUS_OFFLINE;
        $this->save();
    }

    public function isAdmin()
    {
        //允许内部编辑身份登录后台
        if ($this->is_admin || $this->is_editor) {
            return true;
        }

        if (ends_with($this->email, '@haxibiao.com') || ends_with($this->account, '@haxibiao.com')) {
            return true;
        }
        return false;
    }

    public function isWithdrawBefore(): bool
    {
        $wallet_id = $this->wallet->id;
        return Withdraw::where('wallet_id', $wallet_id)->exists();
    }

    public function hasWithdrawOnDDZ(): bool
    {
        if ($ddzUser = $this->getDDZUser()) {
            $wallet = $ddzUser->getWalletAttribute();

            if (null !== $wallet->is_withdraw_before) {
                return $wallet->is_withdraw_before;
            }

            if ($this->getWalletAttribute()->withdraws()->exists()) {
                // fix data.. 工厂内提现过直接更新懂得赚账号是否提现标识
                $ddzUser->getWalletAttribute()->update(['is_withdraw_before' => true]);
            }

        }
        return false;
    }

    public function checkWithdraw($amount)
    {
        $contribute      = $this->profile->count_contributes;
        $need_contribute = $amount * 10;
        if ($contribute >= $need_contribute) {
            return true;
        }

        return false;
    }

    /**
     * 消耗贡献值提现
     * @return void
     * @author zengdawei
     */
    public function consumeContributeToWithdraw($amount, $type, $id)
    {
        Contribute::create([
            'user_id'          => $this->id,
            'amount'           => -($amount * Contribute::WITHDRAW_DATE),
            'remark'           => '提现兑换',
            'contributed_id'   => $id,
            'contributed_type' => $type,
        ]);
        UserProfile::where('user_id', $this->id)->decrement('count_contributes', $amount * Contribute::WITHDRAW_DATE);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \Haxibiao\Breeze\Notifications\VerifyEmail);
    }

    public function hasWithdrawToday(): bool
    {
        return $this->wallet->withdraws()
            ->whereDate('created_at', today())
            ->whereIn('status', [Withdraw::SUCCESS_WITHDRAW, Withdraw::WAITING_WITHDRAW])
            ->exists();
    }

    //nova后台提现金额排行前十的用户
    public static function getTopWithDraw($number = 5)
    {
        $data          = [];
        $ten_top_users = Wallet::select(DB::raw('total_withdraw_amount,real_name,user_id'))
            ->where('type', 0)
            ->orderBy('total_withdraw_amount', 'desc')
            ->take($number)->get()->toArray();

        foreach ($ten_top_users as $top_user) {
            $user = User::find($top_user["user_id"]);
            //显示真实名字
            //$data['name'][] = $user ? $user->name : '空';
            $data['name'][] = $top_user["real_name"] ? $top_user["real_name"] : '空';
            $data['data'][] = $top_user["total_withdraw_amount"];
        }
        return $data;
    }

    public function isEditorRole()
    {
        return User::EDITOR_STATUS == $this->role_id;
    }

    public function isAdminRole()
    {
        return User::ADMIN_STATUS == $this->role_id;
    }

    public function isHighRole()
    {
        return $this->role_id >= User::EDITOR_STATUS;
    }

    public function getUserSuccessWithdrawAmount()
    {
        return $this->wallet->total_withdraw_amount;
    }

    public function isDegregister()
    {
        return User::DEREGISTER_STATUS == $this->status;
    }

    public static function hasReward($user_id, $remark)
    {
        $remarkValue = data_get(self::getUserRewardEnum(), $remark . '.value.remark');
        $gold        = \App\Gold::where('remark', $remarkValue)
            ->where('user_id', $user_id)
            ->get();
        if ($gold->isEmpty()) {
            $hasReward = true;
        } else {
            $hasReward = false;

        }
        return $hasReward;
    }

    public function usedTicket($ticket)
    {
        $this->ticket -= $ticket;
        //保证精力点不少于0
        $this->ticket = $this->ticket > 0 ? $this->ticket : 0;
    }

    public function rewardExpAndLevelUp()
    { //兼容接口
    }

    //兼容wallet提现
    public function withdrawWallet($type)
    {
        if ($type == Withdraw::INVITE_ACTIVITY_TYPE) {
            return $this->invitationWallet;
        } else if ($type == Withdraw::LUCKYDRAW_TYPE) {
            return $this->luckyDrawWallet;
        } else {
            return $this->wallet;
        }
    }

    public function isBad($strict = false)
    {
        $user      = $this;
        $isBadUser = $user->is_disable;
        if (!$isBadUser && $strict) {
            $isBadUser = is_null($user->wallet);
        }

        if (!$isBadUser) {
            $isBadUser = $user->isShuaZi;
        }

        return $isBadUser;
    }

    /**
     * 隔日恢复精力
     */
    public function restoreTicket()
    {
        if ($this->ticket_restore_at) {
            $canRestore = $this->ticket_restore_at <= today();
            if ($canRestore) {
                $this->ticket            = $this->level->ticket_max;
                $this->ticket_restore_at = now();

                // - 记录留存情况
                UserRetention::recordUserRetention($this);

                // - 恢复禁言
                if ($this->status == User::MUTE_STATUS) {
                    $this->status = User::ENABLE_STATUS;
                }

                // - 清空日贡献
                $this->today_contributes = 0;
                $this->save();

                // - 异步同步用户的一些当日计数... TODO:目前是一个jobs 在答赚上 没有抽出来
                // dispatch(new SyncUserProfiles($this->id));
            }
            return $canRestore;
        }
    }

    public function makeCustomerInviteCode(){
        $index  = $this->id % 53;
        $suffix = (int) ($this->id / 53);
        return substr($this->api_token, $index, 8).dechex($suffix);
    }

    public function deCustomerInviteCode($code){
        $prefix_code  = substr($code, 0, 8);
        $adminUsers   = User::where('role_id', User::ADMIN_STATUS)->get();
        foreach ($adminUsers as $admin) {
            $index = strpos($admin->api_token, $prefix_code);
            if($index !== false){
                try{
                    $suffix  = hexdec(substr($code, 8));
                }catch(\Exception $e){
                    return null;
                }
                $user_id = $index + $suffix * 53;
                if($user_id == $admin->id){
                    return $user_id;
                }
            }
        }
        return null;
    }
}
