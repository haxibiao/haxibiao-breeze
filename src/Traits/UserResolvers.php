<?php

namespace Haxibiao\Breeze\Traits;

use App\Gold;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Events\NewAddStaff;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Breeze\Helpers\Redis\RedisHelper;
use Haxibiao\Breeze\Notifications\AddStaffNotification;
use Haxibiao\Breeze\Verify;
use Haxibiao\Content\Category;
use Haxibiao\Content\PostRecommend;
use Haxibiao\Question\Helpers\Redis\RedisSharedCounter;
use Haxibiao\Sns\Visit;
use Haxibiao\Task\Task;
use Haxibiao\Task\UserTask;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait UserResolvers
{
    //添加技师到排钟榜
    public function resolveAddTechnicianUserShifts($root, array $args, $context, $info)
    {
        $uids     = $args['uids'] ?? null;
        $store_id = $args['store_id'] ?? null;
        if (empty($uids) || empty($store_id)) {
            throw new GQLException('参数有误！');
        }
        $redis = RedisHelper::redis();
        foreach ($uids as $uid) {
            $redis->sAdd("store:{$store_id}:technician:shifts", $uid);
        }
        return true;
    }

    //技师排钟榜
    public function resolveLoopTechnicianUserShifts($root, array $args, $context, $info)
    {
        $store_id = $args['store_id'] ?? null;
        $user_id  = $args['user_id'] ?? null;

        if (empty($store_id) || empty(empty($user_id))) {
            throw new GQLException('参数有误！');
        }
        $shifts = "store:{$store_id}:technician:shifts";
        $redis  = RedisHelper::redis();

        //把指定技师先删除，再添加到末尾，实现从第一到最后，一个loop
        if ($redis->srem($shifts, $user_id)) {
            return $redis->sAdd($shifts, $user_id);
        }
        return false;
    }

    //刷新技师排钟榜
    public function resolveShowTechnicianUserShifts($root, array $args, $context, $info)
    {
        $store_id = $args['store_id'] ?? null;
        if (empty($store_id)) {
            throw new GQLException('参数有误！');
        }
        $redis = RedisHelper::redis();
        $uids  = $redis->sMembers("store:{$store_id}:technician:shifts");
        return User::whereIn('id', $uids)->with('technicianProfile')->orderByRaw("field(${uids})");
    }

    public function resolveMe($root, array $args, $context, $info)
    {
        $user = getUser();
        return $user;
    }

    public static function hasRewardResolver($root, array $args, $context, $info)
    {
        $user_id = data_get($args, 'user_id');
        $remark  = data_get($args, 'remark');
        return self::hasReward($user_id, $remark);
    }

    public static function resolveReward($root, array $args, $context, $info)
    {
        $user   = getUser();
        $reason = $args['reason'];

        //FIXME::getUserRewardEnum这个方法答赚本地重写覆盖了了，不一样，有时间再兼容处理
        $rewardValues = data_get(User::getUserRewardEnum(), $reason . '.value');
        $rewardReason = data_get(User::getUserRewardEnum(), $reason . '.description');

        app_track_event("奖励", $rewardReason);
        if (config('app.name') == "datizhuanqian") {
            return User::questionUserReward($user, $rewardValues);
        }
        return User::userReward($user, $rewardValues);

    }

    public static function getUserRewardEnum()
    {
        return [
            'NEW_USER_REWARD'      => [
                'value'       => [
                    'gold'   => Gold::NEW_USER_GOLD,
                    'remark' => '新人注册奖励',
                    'action' => 'NEW_USER_REWARD',
                ],
                'description' => '新人注册奖励',
            ],
            'NEW_YEAR_REWARD'      => [
                'value'       => [
                    'gold'   => Gold::NEW_YEAR_GOLD,
                    'remark' => '新年奖励-牛年',
                    'action' => 'NEW_YEAR_REWARD',
                ],
                'description' => '新年奖励-牛年',
            ],
            'WATCH_REWARD_VIDEO'   => [
                'value'       => [
                    'gold'       => 10,
                    'contribute' => 6,
                    'remark'     => '观看激励视频奖励',
                    'action'     => 'WATCH_REWARD_VIDEO',
                ],
                'description' => '观看激励视频奖励',
            ],
            'SIGNIN_VIDEO_REWARD'  => [
                'value'       => [
                    'remark' => '签到视频观看奖励',
                    'action' => 'SIGNIN_VIDEO_REWARD',
                ],
                'description' => '签到视频观看奖励',
            ],
            'TICKET_SIGNIN_REWARD' => [
                'value'       => [
                    'remark' => '签到精力点奖励',
                    'action' => 'TICKET_SIGNIN_REWARD',
                    'gold'   => 50,
                    'ticket' => 10,
                ],
                'description' => '签到精力点奖励',
            ],
            'GOLD_SIGNIN_REWARD'   => [
                'value'       => [
                    'remark' => '签到金币奖励',
                    'action' => 'GOLD_SIGNIN_REWARD',
                    'gold'   => 100,
                ],
                'description' => '签到金币奖励',
            ],
            'DOUBLE_SIGNIN_REWARD' => [
                'value'       => [
                    'remark' => '双倍签到奖励',
                    'action' => 'DOUBLE_SIGNIN_REWARD',
                ],
                'description' => '双倍签到奖励',
            ],
            'KEEP_SIGNIN_REWARD'   => [
                'value'       => [
                    'remark' => '连续签到奖励',
                    'action' => 'KEEP_SIGNIN_REWARD',
                ],
                'description' => '连续签到奖励',
            ],
            'CLICK_REWARD_VIDEO'   => [
                'value'       => [
                    'ticket'     => \App\User::VIDEO_REWARD_TICKET,
                    'contribute' => User::VIDEO_REWARD_CONTRIBUTE,
                    'gold'       => User::VIDEO_REWARD_GOLD,
                    'remark'     => '点击激励视频奖励',
                    'action'     => 'CLICK_REWARD_VIDEO',
                ],
                'description' => '点击激励视频奖励',
            ],
        ];
    }
    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param $info
     * 第三方账号登录：微信，手机号，支付宝
     */
    public function resolveAuthSignIn($root, array $args, $context, $info)
    {

        $code = $args['code'];
        $type = $args['type'];
        app_track_event("用户登录", "第三方登录", $type);
        return $this->authSignIn($code, $type);

    }
    public function resolveSMSSignIn($root, array $args, $context, $info)
    {

        $sms_code = $args['sms_code'];
        $phone    = $args['phone'];
        app_track_event("用户登录", "验证码登录", $phone);
        return $this->smsSignIn($sms_code, $phone);

    }

    public function me($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($user = getUser()) {

            //注意，前端headers 传的是 deviceHeaders.uniqueId = DeviceInfo.getUniqueID();
            $uuid = request()->header('uniqueId', null);
            if (!empty($uuid)) {
                // 修复旧版本无uuid
                if (empty($user->uuid)) {
                    $user->update(['uuid' => $uuid]);
                }
                // 手机系统升级UUID变更
                if (!empty($user->uuid) && $user->uuid !== $uuid) {
                    $user->update(['uuid' => $uuid]);
                }
            }
            return $user;
        }
    }

    public function resolveRecommendAuthors($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        //TODO: 实现真正的个性推荐算法
        return self::latest('id');
    }

    public function resolveSearchUsers($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        //FXIME: 替换更好的云 + scout ES search
        $keywords = data_get($args, 'keywords', data_get($args, 'keyword'));
        return self::where('name', 'like', '%' . $keywords . '%');
    }

    /**
     * 静默登录，uuid 必须传递，手机号可选
     */
    public static function resolveAutoSignIn($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        app_track_event('用户', '静默登录');
        $phone = data_get($args, 'phone');
        //兼容答赚
        if (blank($phone)) {
            $phone = data_get($args, 'account');
        }
        $uuid = data_get($args, 'uuid');
        $user = AuthHelper::autoSignIn($phone, $uuid);
        return $user;
    }

    /**
     * 手动注册
     */
    public static function resolveSignUp($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        app_track_event('用户', '手动注册');
        $account = data_get($args, 'phone');
        //兼容答赚
        if (blank($account)) {
            $account = data_get($args, 'account');
        }
        $password = data_get($args, 'password');

        $uuid  = data_get($args, 'uuid');
        $email = data_get($args, 'email');
        $name  = data_get($args, 'name') ?? config('auth.default_name', User::DEFAULT_NAME);

        $user = AuthHelper::signUp($account, $password, $uuid, $email, $name);
        return $user;
    }

    /**
     * 手动登录
     */
    public static function resolveSignIn($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        app_track_event('用户', '手动注册');
        $account = data_get($args, 'phone');
        //兼容答赚
        if (blank($account)) {
            $account = data_get($args, 'account');
        }
        //兼容邮箱
        if (blank($account)) {
            $account = $args['email'];
        }

        $password = data_get($args, 'password');
        $uuid     = data_get($args, 'uuid', get_device_id());

        $user = static::signIn($account, $password, $uuid);

        return $user;
    }

    public function updateUserInfo($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

        $islegal  = app('SensitiveUtils')->islegal(Arr::get($args, 'name'));
        $islegal2 = app('SensitiveUtils')->islegal(Arr::get($args, 'introduction'));
        if ($islegal || $islegal2) {
            throw new GQLException('修改的内容中含有包含非法内容,请删除后再试!');
        }

        // 去除 lighthouse 自动传递的参数
        unset($args['directive']);

        if ($user = currentUser()) {
            if (isset($args['phone'])) {
                // 验证手机号
                $flag = preg_match('/^[1](([3][0-9])|([4][5-9])|([5][0-3,5-9])|([6][5,6])|([7][0-8])|([8][0-9])|([9][1,8,9]))[0-9]{8}$/', $args['phone']);
                if (!$flag) {
                    throw new GQLException('修改失败，手机号格式不正确，请检查是否输入正确');
                }

                // 查询是否已经存在
                $flag = User::where('phone', $args['phone'])
                    ->orWhere('account', $args['phone'])->exists();
                if ($flag) {
                    throw new GQLException('该手机号已被绑定，请检查是否输入正确');
                }
                //验证手机号
                if (isset($args['code'])) {
                    Verify::checkSMSCode($args['code'], $args['phone'], Verify::USER_INFO_CHANGE);
                }
            }

            //TODO:暂时不牵涉前端的gql,后期需要修改掉的gql,有关用户信息修改的
            $args_profile_infos = ["age", "gender", "introduction", "birthday"];
            $profile_infos      = [];
            foreach ($args_profile_infos as $profile_info) {
                foreach ($args as $index => $value) {
                    if ($index == $profile_info) {
                        $profile_infos[$index] = $args[$index];
                        if ($index == "gender") {
                            $profile_infos[$index] = User::getGenderNumber($args[$index]);
                        }
                        if ($index == "birthday") {
                            $birthday_str                    = $args[$index];
                            $strs                            = explode("-", str_before($birthday_str, " "));
                            $profile_infos['birth_on_year']  = $strs[0];
                            $profile_infos['birth_on_month'] = $strs[1];
                            $profile_infos['birth_on_day']   = $strs[2];
                            //大于70年的还是继续尊重birthday
                            //小于等于70年1月1日的尊重拆解字段
                            $profile_infos[$index] = $birthday_str;
                            //默认生日未修改的，记录null
                            //FIXME: 生日记录年，月，日字段比较合理，这样不兼容生日为1970年以前的用户了
                            if ($strs[0] < 1970 || Str::contains($birthday_str, "1970-1-1") || Str::contains($birthday_str, "1970-01-01")) {
                                //70年以前的用户就不用birthday字段存生日了，该用拆解的字段
                                $profile_infos[$index] = null;
                            }
                        }
                    }
                }
            }

            if ($args['name'] ?? null) {
                $user_infos['name'] = $args['name'];
            }
            if ($args['phone'] ?? null) {
                $user_infos['phone'] = $args['phone'];
            }
            if ($args['password'] ?? null) {
                $user_infos['password'] = $args['password'];
            }
            if ($args['avatar'] ?? null) {
                $user->saveAvatar($args['avatar']);
            }
            if (!empty($user_infos)) {
                $user->update($user_infos);
            }

            if (!empty($profile_infos)) {
                $profile = $user->profile;
                $profile->update($profile_infos);
            }

            return $user;
        } else {
            throw new GQLException('未登录，请先登录！');
        }
    }

    /**
     * APP用户主动注销账户
     */
    public function resolveRemoveAccount($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($user = getUser()) {
            $user->status = User::STATUS_DESTORY;
            $user->save();
            return true;
        }
    }

    //观看新手教程或采集视频教程任务状态变更
    public function newUserReword($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = currentUser();
        $type = $args['type'];
        if ($type === 'newUser') {
            $task             = Task::where("name", "观看新手视频教程")->first();
            $userTask         = UserTask::where("task_id", $task->id)->where("user_id", $user->id)->first();
            $userTask->status = UserTask::TASK_REACH;
            $userTask->save();
            return 1;
        } else if ($type === 'douyin') {
            $task             = Task::where("name", "观看采集视频教程")->first();
            $userTask         = UserTask::where("task_id", $task->id)->where("user_id", $user->id)->first();
            $userTask->status = UserTask::TASK_REACH;
            $userTask->save();
            return 1;
        }
        return -1;
    }

    public function bindDongdezhuan($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($user = currentUser()) {
            //不允许用户从APP手动指定绑懂得赚账户，必须默认本手机...
            $user->bindDDZ();
            return true;
        }
    }

    /**
     * @param $user
     */
    public function updateProfileAppVersion(User $user): void
    {
        if ($version = request()->header('version', null)) {
            $user->getProfileAttribute()->update([
                'app_version' => $version,
            ]);
        }
    }

    public function resolveUserQuery($root, array $args, $context, $info)
    {
        $user        = getUser(false);
        $loginUserId = data_get($user, 'id');
        if ($loginUserId) {
            if ($loginUserId != data_get($args, 'id')) {
                app_track_event("用户", "查询用户详情", "谁看谁:" . $loginUserId . "-" . $args['id']);
            }
        }
        return \App\User::find(data_get($args, 'id'));
    }

    public static function hasNewUserReward($root, array $args, $context, $info)
    {
        $user_id = data_get($args, 'user_id');
        return self::hasReward($user_id, '新人注册奖励');

    }

    public static function resolveShare($root, array $args, $context, $info)
    {
        $user = getUser();
        if ($args['shared_type'] == 'InCome') {
            Task::refreshTask($user, '晒收入');
        } else if ($args['shared_type'] == 'Category') {
            $category = Category::find($args['shared_id']);
            if ($category) {
                $category->increment('count_shared');
            }
        } else {
            RedisSharedCounter::updateCounter($user->id);
            //触发分享任务
            $user->reviewTasksByClass('Share');
        }

        //随便返回个ID 占个坑
        return [
            'id' => 1,
        ];
    }

    public static function resolveUsersByRank($root, array $args, $context, $info)
    {
        app_track_event("首页", "用户排行榜");
        return PlayWithQuestion::getUsersByRank($args['rank']);
    }
    public function resolveCleanMyVisits($root, array $args, $context, $info)
    {
        //编辑或管理人员才能清空自己的个人访问记录
        if (checkEditor()) {
            $user = currentUser();
            app_track_event("用户操作", "清空个人访问记录", $user->id);
            //清空浏览记录
            Visit::where('user_id', $user->id)->delete();
            //清空视频刷记录
            PostRecommend::where('user_id', $user->id)->delete();
            return true;
        }

        return false;

    }

    public function resolverVestUserLists($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return User::where('role_id', data_get($args, 'role_id'));
    }

    public function resolveAssociateMasterAccount($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $vestIds  = data_get($args, 'vest_ids');
        $masterId = data_get($args, 'master_id');

        $masterUser = User::find($masterId);
        throw_if($masterUser->role_id != User::EDITOR_STATUS, GQLException::class, '这个账户不是运营账户哦！！！');

        foreach ($vestIds as $vestId) {
            $vestUser = User::find($vestId);

            //判断该用户是否已经绑定，绑定就跳过(1个马甲用户只能绑定一个运营账户)
            if ($vestUser->master_id) {
                continue;
            }

            $vestUser->master_id = $masterId;
            $vestUser->save();
        }
        return true;
    }

    /**
     * 添加员工账户
     */
    public function resolveAddStaffAccount($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
//        app_track_event('用户', '关联员工用户');
        $user      = getUser();
        $staffId   = data_get($args, 'staff_id');
        $staffUser = User::find($staffId);

        $staffUser->notify(new AddStaffNotification($staffUser, $user));
        event(new NewAddStaff($staffUser, $user = getUser()));

        throw_if($staffUser->parent_id != 0, GQLException::class, '用户已经绑定了。。。');

        $staffUser->parent_id = getUserId();
        $staffUser->role_id   = User::STAFF_ROLE;
        $staffUser->save();
        return $staffUser;
    }

    /**
     * 添加员工账户
     */
    public function resolveBecomeStaffAccount($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user   = getUser();
        $leadId = data_get($args, 'parent_id');
        throw_if($user->id === $leadId, GQLException::class, '不可绑定自己的邀请ID～');

        $lead = User::find($leadId);
        throw_if(blank($lead), GQLException::class, '该邀请已失效～');
        throw_if($user->parent_id != 0, GQLException::class, '您已经绑定了～');

        $user->parent_id = $leadId;
        $user->save();
        return $user;
    }

    /**
     * 删除员工账户
     */
    public function resolveDeleteStaffAccount($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $staffId   = data_get($args, 'staff_id');
        $staffUser = User::find($staffId);

        //判断该员工账户是否解绑某客户
        if ($staffUser->parent_id == 0) {
            return false;
        }

        $staffUser->parent_id = 0;
        $staffUser->role_id   = User::USER_STATUS;
        $staffUser->save();
        return true;
    }

    /**
     * 员工用户列表
     */
    public function resolveStaffAccountLists($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return User::where('role_id', User::STAFF_ROLE)->where('parent_id', '!=', '0');
    }

    /**
     * 搜索用户uid
     */
    public function resolveSearchUserId($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return User::find(data_get($args, 'id'));
    }

    /**
     * 技师列表
     */
    public function resolveTehnicianUsers($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $store_id = $args['store_id'] ?? null;
        $status   = $args['status'] ?? 'all';
        return User::query()
            ->select('users.*')
            ->with('technicianProfile')
            ->rightJoin('technician_profiles', function ($join) {
                return $join->on('users.id', 'technician_profiles.user_id');
            })
            ->when($store_id, function ($qb) use ($store_id) {
                return $qb->where('technician_profiles.store_id', $store_id);
            })
            ->when(!is_null($status) && $status != "all", function ($qb) use ($status) {
                return $qb->where('technician_profiles.status', $status);
            })
            ->orderByDesc('technician_profiles.status');
    }

    public static function signIn(string $account, string $password, string $uuid = null): User
    {
        throw_if(!is_phone_number($account) && !is_email($account), GQLException::class, '账号格式不正确!');
        //
        $user = User::where('account', $account)->first();

        throw_if(empty($user), UserException::class, '账号不存在,请先注册!');
        if (!password_verify($password, $user->password)) {
            throw new UserException('登录失败,账号或者密码错误');
        }

        if (!empty($uuid) && !strcmp($user->uuid, $uuid)) {
            $user->update(['uuid' => $uuid]);
        }

        //账号已注销
        throw_if($user->isDegregister(), UserException::class, '操作失败,账户已注销!', config('auth.close_account', '9999'));
        return $user;
    }
}
