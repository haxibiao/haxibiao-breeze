<?php

namespace Haxibiao\Breeze\Traits;

use App\Profile;
use Haxibiao\Breeze\Exceptions\ErrorCode;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\OAuth;
use Haxibiao\Breeze\SignIn;
use Haxibiao\Breeze\User;
use Haxibiao\Breeze\UserProfile;
use Haxibiao\Breeze\VerificationCode;
use Haxibiao\Helpers\utils\BadWordUtils;
use Haxibiao\Helpers\utils\SMSUtils;
use Haxibiao\Helpers\utils\WechatUtils;
use Haxibiao\Question\Helpers\Redis\DailyAdRewardCounter;
use Haxibiao\Question\Question;
use Haxibiao\Task\Contribute;
use Haxibiao\Wallet\Gold;
use Haxibiao\Wallet\JDJR;
use Haxibiao\Wallet\Wallet;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * 答题部分的特性
 */
trait PlayWithQuestion
{
    /**
     * 排行榜-用户 财富，答题
     */
    public static function getUsersByRank($rank)
    {
        $builder = User::whereStatus(0);
        if ($rank) {

            //总提现收入排行(成功过提现)
            if ($rank == 'TOTAL_WITHDRAW') {
                $wallet = Wallet::orderBy('total_withdraw_amount', 'desc')
                    ->take(100)
                    ->get();
                $userIds     = $wallet->pluck('user_id')->toArray();
                $ids_ordered = implode(',', $userIds);
                $builder     = $builder->whereIn('id', $userIds)->orderByRaw(DB::raw("FIELD(id, $ids_ordered)"));
            }

            //答题连续答对数排行
            if ($rank == 'DOUBLE_HIT_ANSWER') {
                $profiles = UserProfile::orderBy('answers_count_today', 'desc')
                    ->take(100)
                    ->get();
                $userIds     = $profiles->pluck('user_id')->toArray();
                $ids_ordered = implode(',', $userIds);
                $builder     = $builder->whereIn('id', $userIds)->orderByRaw(DB::raw("FIELD(id, $ids_ordered)"));
            }
        } else {
            //方便调试环境是否连上prod db
            $builder = $builder->latest('id');
        }
        return $builder;
    }

    /**
     * 用户的题目
     */
    public function resolveUserQuestions($root, array $args, $context, $info)
    {
        $order  = data_get($args, 'order');
        $filter = data_get($args, 'filter');
        return User::listQuestions($root, $order, $filter);
    }

    public static function listQuestions($user, $order, $filter)
    {
        //只要不是用户已删除的问题都看到
        $qb = $user->questions()->with('video')->latest();

        if (isset($order)) {
            $qb->orderByDesc($order);
        }

        if (isset($filter)) {
            $qb->whereSubmit($filter);
        } else {
            $qb->where('submit', '<>', Question::DELETED_SUBMIT);
        }
        return $qb;
    }

    /**
     * 查询用户详情
     */
    public static function resolveUser($root, array $args, $context, $info)
    {
        app_track_event('用户页', '访问他人主页');
        if (isset($args['id'])) {
            return User::visitById($args['id']);
        }
    }

    /**
     * 访问他人主页
     */
    public static function visitById($id): User
    {
        $user = User::find($id);
        if (str_contains(request()->get('query'), "UserInfoQuery")) {
            $user->profile->increment('visited_count');
        }
        return $user;
    }

    public static function resolveCheckAccountExists($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "查询账号是否存在");
        return User::checkAccountExists($args['account']);
    }

    public static function checkAccountExists($account)
    {
        return User::where('account', $account)->exists();
    }

    public function resolveInitModule($root, array $args, $context, $info)
    {
        return ['id' => uniqid()];
    }

    public static function resolveInitJDJR($root, array $args, $context, $info)
    {
        $user   = getUser(false);
        $result = !is_null($user);
        if (!is_null($user)) {
            $jdjr = JDJR::init($user->id, $user->account);

        }
        return $result;
    }

    public static function resolveSignInWithToken($root, array $args, $context, $info)
    {
        $token = data_get($args, 'token');
        if ($token) {
            $user = User::where('api_token', $token)->first();
            //账号已注销
            throw_if(!is_null($user) && $user->isDegregister(), UserException::class, '操作失败,账户已注销!', ErrorCode::DEREGISTER_USER);
            return $user;
        }
    }

    public static function resolveResetPassword($root, array $args, $context, $info)
    {
        $code     = data_get($args, 'code');
        $password = data_get($args, 'password');
        $account  = data_get($args, 'account');
        return User::resetPassword($account, $code, $password);
    }

    public static function resetPassword(string $account, string $code, string $password): User
    {
        $user = User::whereAccount($account)->first();
        if ($user) {
            $field = account($account);
            if ($code !== null && in_array($field, ['phone', 'email'])) {
                $verify = VerificationCode::where('account', $account)
                    ->where('code', $code)
                    ->whereNull('deleted_at')
                    ->byValid(VerificationCode::CODE_TIME_OUT)
                    ->orderby('id', 'desc')
                    ->first();
                if (!$verify) {
                    throw new UserException('验证码错误或者已失效!');
                }
                $verify->delete();
                //重置密码
                $user->setPassword($password);
                $user->save();
                return $user;
            } else {
                throw new UserException('没有此账号!');
            }
        }
        throw new UserException('验证码有误或者账号格式错误!');
    }

    public static function resolveUpdatePassword($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "更新密码");
        $user        = getUser();
        $oldPassword = data_get($args, 'old_password');
        $newPassword = data_get($args, 'new_password');
        return User::updatePassword($oldPassword, $newPassword, $user);
    }

    public static function updatePassword($oldPassword, $newPassword, $user): User
    {

        if ($oldPassword == $newPassword) {
            throw new UserException('修改失败,新旧密码请勿相同');
        }

        if (!$user->verifyPassword($oldPassword)) {
            throw new UserException('密码错误,修改失败');
        }

        $user->setPassword($newPassword);
        $user->save();

        return $user;
    }

    public static function resolveUpdateUserName($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "更新昵称");
        $user = getUser();
        $user->rename($args['name']);
        $user->save();
        return $user;
    }

    public static function resolveUpdateUserAvatar($root, array $args, $context, $info)
    {
        $user = getUser();
        return $user->saveAvatar($args['avatar']);
    }

    public static function resolveSetUserInfo($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "设置用户资料");

        $user   = getUser();
        $result = User::setUserInfo($user, $args['data']);

        return $result;
    }

    public static function setUserInfo(User $user, $data): User
    {
        throw_if(empty($data), UserException::class, '设置失败,信息错误!');
        $profile = Profile::exclude(['golds', 'answers'])
            ->firstOrNew(['user_id' => $user->id]);

        $userInfoFillable = User::getUserInfoFillable();

        foreach ($data as $attribute => $value) {
            throw_if(BadWordUtils::check($value), UserException::class, '含有非法关键词,请重新输入!');

            //更新users字段
            if (in_array($attribute, $userInfoFillable['user'])) {
                $user->$attribute = $value;
                continue;
            }

            //更新user_profiles 字段
            if (in_array($attribute, $userInfoFillable['profile'])) {
                //空字符串不影响birthday = null,不然给''会sql出错
                if ($attribute == "birthday" && empty($value)) {
                    continue;
                }
                $profile->$attribute = $value;
                continue;
            }
        }
        //这个先保存 不然不会触发任务检测
        $profile->save();
        $user->save();

        return $user;
    }

    public static function resolveUuidBind($root, array $args, $context, $info)
    {
        $user     = getUser();
        $account  = data_get($args, 'account');
        $password = data_get($args, 'password');
        if (!empty($user->uuid)) {
            //兼容一下2.1前端参数问题
            if (!is_phone_number($user->account)) {
                $userExisted = User::where('account', $account)->exists();
                if ($userExisted) {
                    throw new UserException('绑定失败,该账户已存在');
                }
            } else {
                $account = $user->account;
            }

            User::uuidBind($user, $account, $password);
            return $user;
        }
    }

    public static function resolveSetUserPaymentInfo($root, array $args, $context, $info)
    {
        $user       = getUser();
        $payAccount = data_get($args, 'pay_account');
        $real_name  = data_get($args, 'real_name');

        return User::changePaymentInfo($user, $payAccount, $args['code'], $real_name);
    }

    public static function changePaymentInfo(User $user, $payAccount, $code, $real_name): User
    {
        $userProfile = UserProfile::select('verified_at')->firstOrNew(['user_id' => $user->id]);

        throw_if(is_email($payAccount), UserException::class, '设置失败,禁止使用邮箱绑定支付宝!');
        throw_if(empty($real_name), UserException::class, '请填写真实姓名!');

        $wallet = $user->wallet;

        if (!is_null($wallet)) {
            throw_if($user->isShuaZi, UserException::class, '账户异常,修改失败');
            throw_if($wallet->available_pay_info_change_count < 0, UserException::class, '提现资料变更次数已达上限,请联系客服人工修改');
        }

        //账号未验证
        if (empty($userProfile->verified_at)) {
            throw_if(!isset($code), UserException::class, '验证码不能为空');
            //获取最后一条验证记录
            $verify = VerificationCode::where('account', $user->account)
                ->where('code', $code)
                ->byValid(VerificationCode::CODE_TIME_OUT)
                ->orderby('id', 'desc')
                ->first();
            throw_if(!$verify, UserException::class, '验证码错误或者已失效!');

            $userProfile->verified_at = now();
            $verify->delete();
        }

        //钱包不存在,创建新钱包
        if (is_null($user->wallet)) {
            $wallet = $user->wallet()->firstOrNew(['user_id' => $user->id]);
        }

        //提现账号
        if (!empty($payAccount)) {
            $wallet->pay_account = $payAccount;
        }

        //填充提现信息
        $wallet->fill([
            'pay_account' => $payAccount,
            // 'code'        => $code,
        ]);

        //中文判断
        $isCNRealName = !preg_match_all("/([\x{4e00}-\x{9fa5}]+)/u", $real_name);
        throw_if($isCNRealName, UserException::class, '姓名输入不合法,请重新输入!');

        //增长提现信息修改次数
        if (!empty($wallet->pay_infos)) {
            $wallet->pay_info_change_count++;
        }

        $user->save();
        $userProfile->save();
        $wallet->save();
        return $user;
    }

    public function resolveBindWechatMutation($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "绑定微信");
        return WechatUtils::bindWechat(getUser(), data_get($args, 'union_id'), data_get($args, 'code'), 'v2');
    }

    //天天出题绑定微信专用 为了不修改helpers
    public function resolveTTCTBindWechatMutation($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "天天出题绑定微信");
        return User::ttctBindWechat(getUser(), data_get($args, 'union_id'), data_get($args, 'code'), 'v2');
    }

    public static function ttctBindWechat(User $user, $unionId = null, $code = null, $version = 'v1')
    {
        throw_if(empty($code), UserException::class, '绑定失败,参数错误!');
        //获取微信token
        $accessTokens = User::accessTokenTTCT($code);
        throw_if(!Arr::has($accessTokens, ['unionid', 'openid']), UserException::class, '授权失败,请稍后再试!');

        $oAuth = OAuth::store($user->id, 'wechat', $accessTokens['openid'], $accessTokens['unionid'], Arr::only($accessTokens, ['openid', 'refresh_token'], 1));
        throw_if($oAuth->user_id != $user->id, UserException::class, '绑定失败,该微信已绑定其他账户!');

        //同步wallet OpenId
        $wallet          = Wallet::firstOrNew(['user_id' => $user->id]);
        $wallet->open_id = $accessTokens['openid'];
        $wallet->save();

        return $oAuth;
    }

    public function resolveSendVerificationCode($root, array $args, $context, $info)
    {
        app_track_event("个人中心", "发送验证码");

        $verify = SMSUtils::getVerifyLog($args['account']);

        if (!is_null($verify)) {
            return $verify;
        }

        return SMSUtils::sendVerificationCode($args['account'], null, $args['action']);
    }

    public function resolveUpdateUserAccount($root, array $args, $context, $info)
    {
        app_track_event("个人中心", '新的账户(手机号)', $args['account']);

        $user        = getUser();
        $userProfile = UserProfile::select('verified_at')->firstOrNew([
            'user_id' => $user->id,
        ]);
        return User::updateAccount($user, $args['account'], $userProfile);
    }

    public static function updateAccount($user, $account)
    {
        $userProfile = UserProfile::select('verified_at')->firstOrNew([
            'user_id' => $user->id,
        ]);

        if ($userProfile->verified_at) {
            throw new UserException('账号已验证成功,无法修改!');
        }

        if (User::whereAccount($account)->first()) {
            throw new UserException('该账号已存在,请尝试使用其他账号！');
        }

        if (!preg_match("/^1\d{10}$/", $account)) {
            throw new UserException('请输入正确的手机号码！');
        }

        $user->account            = $account;
        $userProfile->verified_at = now();
        $user->save();
        $userProfile->save();

        return $user;
    }

    public static function resolveUserReward($root, array $args, $context, $info)
    {
        $user   = getUser();
        $reward = $args['reward'];

        $rewardValues = data_get(self::getUserRewardEnum(), $reward . '.value');
        $rewardReason = data_get(self::getUserRewardEnum(), $reward . '.description');

        app_track_event("奖励", $rewardReason);
        return User::questionUserReward($user, $rewardValues);

    }

    public static function questionUserReward(User $user, array $reward)
    {
        $action = Arr::get($reward, 'action');
        $adType = Arr::get($reward, 'ad_type');

        //限制奖励领取次数,防止老用户大量重复刷取广告
        User::rewardRestrictions($action, $user->id);
        // 更新广告奖励领取次数
        if (!empty($adType)) {
            DailyAdRewardCounter::updateCounter($adType);
        }
        // 老用户判定:提现 >= 7次
        $isOldUser = $user->withdrawCount >= 7;

        $result = [
            'gold'       => 0,
            'ticket'     => 0,
            'contribute' => 0,
        ];

        // 新版本 开宝藏奖励 是随机抽取一个奖励发放
        if ($action == 'OPEN_TREASURE') {
            $removalRewards = Arr::random(['gold', 'ticket', 'contribute'], 2);
            foreach ($removalRewards as $removedKey) {
                if (array_key_exists($removedKey, $reward)) {
                    unset($reward[$removedKey]);
                }
            }
        }

        //智慧点奖励
        if (isset($reward['gold'])) {
            Gold::makeIncome($user, $reward['gold'], $reward['remark']);

            $result['gold'] = $reward['gold'];
        }

        //精力点奖励
        if (isset($reward['ticket'])) {
            if ($action != 'SUCCESS_ANSWER_VIDEO_REWARD' && $action != 'FAIL_ANSWER_VIDEO_REWARD') {
                $user->increment('ticket', $reward['ticket']);
                $result['ticket'] = $reward['ticket'];
            }
        }

        //贡献奖励
        if (isset($reward['contribute'])) {
            // JIRA:DZ-1630 区分新老用户贡献点
            $contribute = $isOldUser ? Arr::get($reward, 'old_user.contribute', $reward['contribute']) : $reward['contribute'];

            if ($action == 'DRAW_FEED_ADVIDEO_REWARD') {
                //draw feed点击
                Contribute::rewardClickDrawFeed($user, $contribute);
            } else if ($action == 'CLICK_DRAW_FEED') {
                // feed点击
                Contribute::rewardClickFeed($user, $contribute);
            } else {
                Contribute::rewardVideoPlay($user, $contribute);
            }
            $result['contribute'] = $contribute;
        }

        //签到额外奖励
        if ($action == 'SIGNIN_VIDEO_REWARD') {
            $signRewards = SignIn::getSignInReward($user->profile->keep_signin_days);
            //智慧点
            if (isset($signRewards['gold_reward'])) {
                Gold::makeIncome($user, $signRewards['gold_reward'], '签到额外奖励');
                $result['gold'] = $signRewards['gold_reward'];
            }
            //贡献
            if (Arr::get($signRewards, 'contribute_reward', 0) > 0) {
                Contribute::rewardSignInAdditional($user, $signRewards['contribute_reward']);
                $result['contribute'] = $signRewards['contribute_reward'];
            }
        }

        //签到双倍奖励
        if ($action == 'DOUBLE_SIGNIN_REWARD') {
            $rewardRate = 2;
            $signIn     = SignIn::todaySigned($user->id);
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
                Gold::makeIncome($user, $result['gold'], '签到翻倍奖励');
            }

            //双倍贡献
            if ($signIn->contribute_reward > 0) {
                Contribute::rewardSignInDoubleReward($user, $signIn, $result['contribute']);
            }

            // // 冗余点击双倍奖励的用户智慧点, 为数据分析做准备 = guowei 可以解释
            // RecordUserAction::firstOrCreate([
            //     'user_id'   => $user->user_id,
            //     'name'      => '签到翻倍奖励',
            //     'value_int' => $result['gold'],
            // ]);
        }

        return $result;
    }

    /**
     * 用户主动注销（删号不玩了）
     */
    public static function resolveRemoveUser($root, array $args, $context, $info)
    {
        $user         = getUser();
        $user->status = User::DEREGISTER_STATUS;
        $user->save();
        return $user;
    }

    /**
     * 签到 闯关 奖励
     */
    public static function resolveCheckPointReward($root, array $args, $context, $info)
    {
        $user         = getUser();
        $correctCount = Arr::get($args, 'correctAnswerCount', 0);
        $result       = [
            'gold'       => 0,
            'ticket'     => 0,
            'contribute' => 0,
        ];
        $rewards = [
            '600'  => 8,
            '1200' => 7,
            '2400' => 6,
            '3600' => 5,
            '4800' => 4,
            '6000' => 3,
        ];

        $goldPoint = 0;
        foreach ($rewards as $k => $v) {
            if ($user->gold <= $k) {
                $goldPoint = $v;
            }
        }

        if ($correctCount == 1) {
            $goldPoint += 2;
        } else if ($correctCount > 0) {
            $goldPoint *= $correctCount;
        }

        if ($goldPoint > 0) {
            Gold::makeIncome($user, $goldPoint, '闯关红包奖励');
            $result['gold'] = $goldPoint;
        }

        return $result;
    }

}
