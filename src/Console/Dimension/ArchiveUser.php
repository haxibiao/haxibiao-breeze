<?php

namespace Haxibiao\Breeze\Console\Dimension;

use App\Answer;
use App\User;
use App\UserActivation;
use App\UserProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Haxibiao\Breeze\Dimension;
use Illuminate\Support\Facades\DB;
use xin\helper\Arr;

class ArchiveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:user {--date=} {--hour=}
    {--newuser : 新用户首日数据}
    {--categoryuser : 新老用户分类数据}
    {--newUserActivation : 新用户激活漏斗}
    {--updateNewUserActivation : 更新新用户激活漏斗次日留存率}
    {--avg : 平均答题趋势}';

    /**
     * 接受短信手机号
     */
    protected $phones = [13327347331, 17692625821, 17872635502, 13825294765, 15575464607];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '按日，小时统计归档用户新增留存信息';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $intervalHours 统计时间
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->option('date') ?? today()->toDateString();
        $hour = $this->option('hour') ?? now()->format('H');

        //必须凌晨执行，新用户首日行为数据才准确，避免次日留存用户又活跃了，更新了counts
        if ($this->option('newuser')) {
            $this->info("维度归档统计: 新用户首日行为数据 ..." . $date);
            return $this->firstDayUser($date);
        }

        if ($this->option('categoryuser')) {
            $this->info("维度归档统计: 新老用户数据 ..." . $date);
            return $this->userCategoriesByDay($date);
        }

        if ($this->option('newUserActivation')) {
            $this->info("维度归档统计: 新用户激活漏斗 ..." . $date);
            return $this->newUserActivation($date);
        }

        if ($this->option('avg')) {
            $this->info('维度归档统计：分类用户平均答题趋势 ...' . $date);
            return $this->avgAnswersByUserCreatedAt($date);
        }

        if ($this->option('updateNewUserActivation')) {
            $this->info("更新新用户激活数据次日留存率..." . $date);
            return $this->updateNewUserActivation($date);
        }

        //默认归档当前小时新增
        if (is_null($this->option('date'))) {
            $this->info("统计${hour}时新增:" . $date);
            return $this->newUsersByHour($date, $hour);
        }

        //归档以前某天的每小时新增
        if (is_null($this->option('hour')) && $this->option('date')) {
            if ($this->option('date') >= today()->toDateString()) {
                $this->error('必须指定过去的日期才能统计整天每小时新增数据');
                return;
            }
            $this->info("统计整天的每小时新增:" . $date);
            for ($i = 1; $i <= 24; $i++) {
                $this->newUsersByHour($date, $i);
            }
        }
    }

    /**
     * 统计每小时的新增用户数
     */
    public function newUsersByHour($date, $hour)
    {
        $timeTo = $date . " " . $hour . ":00:00";
        if ($hour == 24) {
            $timeTo = $date . " 23:59:59";
        }
        $timeFrom = $date . " " . ($hour - 1) . ":00:00";

        $newUserCount = User::whereBetween('created_at', [$timeFrom, $timeTo])
            ->count('id');

        $dimension = Dimension::firstOrCreate([
            'group' => '新增',
            'name'  => '每小时新增用户',
            'hour'  => $hour,
            'date'  => $date,
        ]);
        $dimension->value = $newUserCount;
        $dimension->save();
        $this->info($timeFrom . " " . $timeTo . "," . $dimension->name . " " . $dimension->value);
    }

    /**
     * 计算 新用户首日 信息，如 平均智慧点 平均答题数 最高答题数 (每日凌晨统计)
     *
     * @return void
     */
    public function firstDayUser($date)
    {
        $day   = today();
        $dates = [(clone $day)->subDay(), $day];

        $qb_first_day = DB::table('users')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            //开启下面留存条件就是次日流失用户了...
            // ->join('user_retentions', 'users.id', '=', 'user_retentions.user_id')
            // ->whereNull('user_retentions.day2_at')
            ->whereBetween('users.created_at', $dates);

        $avgGold   = $qb_first_day->avg('gold') ?? 0;
        $dimension = Dimension::firstOrNew([
            'group' => '新用户首日',
            'name'  => '平均智慧点',
            'date'  => $date,
        ]);
        $dimension->value = $avgGold;
        $dimension->save();
        echo '新用户首日 - 平均智慧点:' . $avgGold . ' 日期:' . $date . "\n";

        $avg_answers_count = $qb_first_day->avg('user_profiles.answers_count') ?? 0;
        $dimension         = Dimension::firstOrNew([
            'group' => '新用户首日',
            'name'  => '平均答题数',
            'date'  => $date,
        ]);
        $dimension->value = $avg_answers_count;
        $dimension->save();
        echo '新用户首日 - 平均答题数:' . $avg_answers_count . ' 日期:' . $date . "\n";

        $max_answers_count = $qb_first_day->max('user_profiles.answers_count') ?? 0;
        $dimension         = Dimension::firstOrNew([
            'group' => '新用户首日',
            'name'  => '最高答题数',
            'date'  => $date,
        ]);
        $dimension->value = $max_answers_count;
        $dimension->save();
        echo '新用户首日 - 最高答题数:' . $max_answers_count . ' 日期:' . $date . "\n";

        $zero_answers_count = $qb_first_day->where('user_profiles.answers_count', 0)->count();
        $dimension          = Dimension::firstOrNew([
            'group' => '新用户首日',
            'name'  => '零答题行为人数',
            'date'  => $date,
        ]);
        $dimension->value = $zero_answers_count;
        $dimension->save();
        echo '新用户首日 - 零答题行为人数:' . $zero_answers_count . ' 日期:' . $date . "\n";

        $zero_gold_count = DB::table('users')
            ->where('gold', 300)
            ->whereBetween('users.created_at', $dates)->count();
        $dimension = Dimension::firstOrNew([
            'group' => '新用户首日',
            'name'  => '零账单变动人数',
            'date'  => $date,
        ]);
        $dimension->value = $zero_gold_count;
        $dimension->save();
        echo '新用户首日 - 零账单变动人数:' . $zero_gold_count . ' 日期:' . $date . "\n";
    }

    /**
     * 每天根据提现次数分类用户
     */
    public function userCategoriesByDay($date)
    {

        // 归档昨天的数据
        $date = Carbon::parse($date)->subDay(1)->toDateString();

        // 没有提现过的用户
        $pureNewUserQuery = DB::select('SELECT count(1) as pureNewUserCount FROM user_profiles where transaction_sum_amount =  0 and created_at >= "2018-12-12";');
        $pureNewUserCount = current($pureNewUserQuery)->pureNewUserCount;

        $dimension = Dimension::firstOrNew([
            'group' => '新老用户分类活跃数',
            'name'  => '纯新用户',
            'date'  => $date,
        ]);
        $dimension->value = $pureNewUserCount;
        $dimension->save();
        echo '新老用户分类活跃数 - 纯新用户:' . $pureNewUserCount . ' 日期:' . $date . "\n";


        // 提现一次用户
        $NewUserQuery = DB::select('SELECT count(1) as newUserCount FROM ( SELECT count(1) as num, wallet_id FROM withdraws WHERE created_at >= "2018-12-12" GROUP BY wallet_id HAVING num = 1) AS b;');
        $newUserCount = current($NewUserQuery)->newUserCount;

        $dimension = Dimension::firstOrNew([
            'group' => '新老用户分类活跃数',
            'name'  => '新用户',
            'date'  => $date,
        ]);
        $dimension->value = $newUserCount;
        $dimension->save();
        echo '新老用户分类活跃数 - 新用户:' . $newUserCount . ' 日期:' . $date . "\n";

        // 提现2-7次用户
        $OldUserQuery = DB::select('SELECT count(1) as oldUserCount FROM ( SELECT count(1) as num, wallet_id FROM withdraws WHERE created_at >= "2018-12-12" GROUP BY wallet_id HAVING num BETWEEN 2 and 8) AS b;');
        $oldUserCount = current($OldUserQuery)->oldUserCount;

        $dimension = Dimension::firstOrNew([
            'group' => '新老用户分类活跃数',
            'name'  => '老用户',
            'date'  => $date,
        ]);
        $dimension->value = $oldUserCount;
        $dimension->save();
        echo '新老用户分类活跃数 - 老用户:' . $oldUserCount . ' 日期:' . $date . "\n";

        // 提现7次以上
        $pureOldUserQuery = DB::select('SELECT count(1) as pureOldUserCount FROM ( SELECT count(1) as num, wallet_id FROM withdraws WHERE created_at >= "2018-12-12" GROUP BY wallet_id HAVING num > 7) AS b;');
        $pureOldUserCount = current($pureOldUserQuery)->pureOldUserCount;

        $dimension = Dimension::firstOrNew([
            'group' => '新老用户分类活跃数',
            'name'  => '纯老用户',
            'date'  => $date,
        ]);
        $dimension->value = $pureOldUserCount;
        $dimension->save();
        echo '新老用户分类活跃数 - 纯老用户:' . $pureOldUserCount . ' 日期:' . $date . "\n";
    }

    /**
     * 新用户激活漏斗数据归档
     */
    public function newUserActivation($date)
    {
        // 归档昨天的数据
        $date_format = Carbon::make($date);
        $day   = $date_format->toDateTimeString();
        $dates = [$date_format->subDay()->toDateTimeString(), $day];

        $qb_first_day = DB::table('user_profiles')
            ->whereBetween('created_at', $dates);

        // 首次登陆
        $fistLoginCount = DB::table('users')
            ->whereBetween('created_at', $dates)
            ->count();

        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '首次登陆',
            'date'  => $date,
        ]);
        $dimension->value = $fistLoginCount;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '首次登陆',
            'remark' => '当日新用户',
            'all_conversion_rate' => '100%',
            'link_conversion_rate' => '100%',
        ]);
        $activation->action_count = $fistLoginCount;
        $activation->save();

        echo '新用户激活漏斗 - 首次登陆:' . $fistLoginCount . ' 日期:' . $date . "\n";

        // 领取新人红包
        $redPacketCount = DB::table('gold')
            ->where('remark', '新人注册奖励')
            ->whereBetween('created_at', $dates)
            ->count();

        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '领取新人红包',
            'date'  => $date,
        ]);
        $dimension->value = $redPacketCount;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '领取新人红包',
            'remark' => '新人注册奖励',
        ]);

        $activation->all_conversion_rate = round($redPacketCount / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($redPacketCount / $fistLoginCount, 2) * 100 . '%';
        $activation->action_count = $redPacketCount;

        $activation->save();

        echo '新用户激活漏斗 - 领取新人红包:' . $redPacketCount . ' 日期:' . $date . "\n";

        // 领取签到奖励
        $signInCount = DB::table('users')
            ->where('gold', '!=', 300)
            ->whereBetween('created_at', $dates)
            ->count();

        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '领取签到奖励',
            'date'  => $date,
        ]);
        $dimension->value = $signInCount;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '领取签到奖励',
            'remark' => 'gold!=300',
        ]);

        $activation->all_conversion_rate = round($signInCount / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($signInCount / $redPacketCount, 2) * 100 . '%';
        $activation->action_count = $signInCount;

        $activation->save();

        echo '新用户激活漏斗 - 领取签到奖励:' . $signInCount . ' 日期:' . $date . "\n";

        // 开始答题
        $answers_begin = (clone $qb_first_day)->where('answers_count', '>=', 1)->count();
        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '开始答题',
            'date'  => $date,
        ]);
        $dimension->value = $answers_begin;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '开始答题',
            'remark' => '新用户-答题0题以上',
        ]);

        $activation->all_conversion_rate = round($answers_begin / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($answers_begin / $signInCount, 2) * 100 . '%';
        $activation->action_count = $answers_begin;

        $activation->save();
        echo '新用户激活漏斗 - 开始答题:' . $answers_begin . ' 日期:' . $date . "\n";

        // 完成 5 题
        $answers_5 = (clone $qb_first_day)->where('answers_count', '>=', 5)->count();
        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '完成5题',
            'date'  => $date,
        ]);
        $dimension->value = $answers_5;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '完成5题',
            'remark' => '新用户-答题5题以上',
        ]);

        $activation->all_conversion_rate = round($answers_5 / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($answers_5 / $answers_begin, 2) * 100 . '%';
        $activation->action_count = $answers_5;

        $activation->save();
        echo '新用户激活漏斗 - 完成 5 题:' . $answers_5 . ' 日期:' . $date . "\n";

        // 完成 6 题
        $answers_6 = (clone $qb_first_day)->where('answers_count', '>=', 6)->count();
        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '完成6题',
            'date'  => $date,
        ]);
        $dimension->value = $answers_6;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '完成6题',
            'remark' => '新用户-答题6题以上',
        ]);

        $activation->all_conversion_rate = round($answers_6 / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($answers_6 / $answers_5, 2) * 100 . '%';
        $activation->action_count = $answers_6;

        $activation->save();
        echo '新用户激活漏斗 - 完成 6 题:' . $answers_6 . ' 日期:' . $date . "\n";


        // 完成 10 题
        $answers_10 = (clone $qb_first_day)->where('answers_count', '>=', 10)->count();
        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '完成10题',
            'date'  => $date,
        ]);
        $dimension->value = $answers_10;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '完成10题',
            'remark' => '新用户-答题10题以上',
        ]);

        $activation->all_conversion_rate = round($answers_10 / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($answers_10 / $answers_6, 2) * 100 . '%';
        $activation->action_count = $answers_10;

        $activation->save();
        echo '新用户激活漏斗 - 完成 10 题:' . $answers_10 . ' 日期:' . $date . "\n";

        // 绑定提现账号
        $newUserId = DB::table('users')
            ->whereBetween('created_at', $dates)
            ->pluck('id');

        $bindOauthCount = DB::table('o_auths')
            ->whereIn('user_id', $newUserId)
            ->whereBetween('created_at', $dates)
            ->where('oauth_type', '!=', 'damei')
            ->where('oauth_type', '!=', 'dongdezhuan')
            ->count();

        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '绑定提现账号',
            'date'  => $date,
        ]);
        $dimension->value = $bindOauthCount;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '绑定提现账号',
            'remark' => '支付宝、微信',
        ]);

        $activation->all_conversion_rate = round($bindOauthCount / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($bindOauthCount / $answers_10, 2) * 100 . '%';
        $activation->action_count = $bindOauthCount;

        $activation->save();

        echo '新用户激活漏斗 - 绑定提现账号:' . $bindOauthCount . ' 日期:' . $date . "\n";

        $filed = '';
        if (config('app.name') == 'datizhuanqian') {
            $filed = 'user_id';
        } else {
            $filed = 'wallet_id';
            //这里其实是wallet_id
            $newUserId =  $newUserId = DB::table('wallets')
                ->whereBetween('created_at', $dates)
                ->pluck('id');
        }

        // 完成提现
        $withdraws = DB::table('withdraws')
            ->whereIn($filed, $newUserId)
            ->whereBetween('created_at', $dates)
            ->count();

        $dimension = Dimension::firstOrNew([
            'group' => '新用户激活漏斗',
            'name'  => '完成提现',
            'date'  => $date,
        ]);
        $dimension->value = $withdraws;
        $dimension->save();

        // 计算 环节转化率、整体转化率
        $activation = UserActivation::firstOrNew([
            'date' => $date,
            'action' => '完成提现',
            'remark' => '提现0.3元',
        ]);

        $activation->all_conversion_rate = round($withdraws / $fistLoginCount, 2) * 100 . '%';
        $activation->link_conversion_rate = round($withdraws / $bindOauthCount, 2) * 100 . '%';
        $activation->action_count = $withdraws;

        $activation->save();

        echo '新用户激活漏斗 - 完成提现:' . $withdraws . ' 日期:' . $date . "\n";
    }

    /**
     * 更新新用户激活数据次日留存率
     *
     * @param $date 用户创建时间
     */
    public function updateNewUserActivation($date)
    {
        // 格式化时间
        $date_format = Carbon::make($date);

        // 新用户创建日期
        $newUserDates = [(clone $date_format)->subDay(2)->toDateTimeString(), (clone $date_format)->subDay()->toDateTimeString()];

        // 次日统计时间
        $dates = [(clone $date_format)->subDay()->toDateTimeString(), (clone $date_format)->toDateTimeString()];

        $qb_first_day = DB::table('user_profiles')
            ->whereBetween('created_at', $newUserDates);

        // 重新计算转化率
        // 开始答题
        // 前日注册并开始答题的用户主键
        $answers_begin_user_ids = $qb_first_day
            ->where('answers_count', '>=', 1)
            ->pluck('user_id');

        // 获取前日注册用户在昨日还在答题的用户数量
        $answers_begin_second = DB::table('sign_ins')
            ->whereIn('user_id', $answers_begin_user_ids)
            ->whereBetween('created_at', $dates)
            ->count();

        // 获取前日开始答题环节用户数
        $answers_begin = (clone $qb_first_day)->where('answers_count', '>=', 1)->count();

        $link_conversion_rate = round($answers_begin_second / $answers_begin, 2) * 100 . '%';

        DB::update('update user_activation set second_link_conversion_rate = ? where `date` = ? and `action` = ?', [$link_conversion_rate, $date, '开始答题']);
        echo '更新新用户激活漏斗 - 开始答题:' . $answers_begin . ' 日期:' . $date . "\n";

        // 完成 5 题
        // 前日注册用户并完成 5 题的用户主键
        $answers_5_user_ids = DB::table('user_profiles')
            ->whereBetween('created_at', $newUserDates)
            ->where('answers_count', '>=', 5)
            ->pluck('user_id');

        // 获取前日注册用户在昨日还在答题的用户数量
        $answers_5_second = DB::table('sign_ins')
            ->whereIn('user_id', $answers_5_user_ids)
            ->whereBetween('created_at', $dates)
            ->count();

        $answers_5 = (clone $qb_first_day)->where('answers_count', '>=', 5)->count();

        $link_conversion_rate = round($answers_5_second / $answers_5, 2) * 100 . '%';

        DB::update('update user_activation set second_link_conversion_rate = ? where `date` = ? and `action` = ?', [$link_conversion_rate, $date, '完成5题']);
        echo '更新新用户激活漏斗 - 完成 5 题:' . $answers_5 . ' 日期:' . $date . "\n";


        // 完成 6 题
        // 前日注册用户并完成 6 题的用户主键
        $answers_6_user_ids = DB::table('user_profiles')
            ->whereBetween('created_at', $newUserDates)
            ->where('answers_count', '>=', 6)
            ->pluck('user_id');

        // 获取前日注册用户在昨日还在答题的用户数量
        $answers_6_second = DB::table('sign_ins')
            ->whereIn('user_id', $answers_6_user_ids)
            ->whereBetween('created_at', $dates)
            ->count();

        $answers_6 = (clone $qb_first_day)->where('answers_count', '>=', 6)->count();

        $link_conversion_rate = round($answers_6_second / $answers_6, 2) * 100 . '%';

        DB::update('update user_activation set second_link_conversion_rate = ? where `date` = ? and `action` = ?', [$link_conversion_rate, $date, '完成6题']);
        echo '更新新用户激活漏斗 - 完成 6 题:' . $answers_6 . ' 日期:' . $date . "\n";


        // 完成 10 题
        // 前日注册用户并完成 10 题的用户主键
        $answers_10_user_ids = DB::table('user_profiles')
            ->whereBetween('created_at', $newUserDates)
            ->where('answers_count', '>=', 10)
            ->pluck('user_id');

        // 获取前日注册用户在昨日还在答题的用户数量
        $answers_10_second = DB::table('sign_ins')
            ->whereIn('user_id', $answers_10_user_ids)
            ->whereBetween('created_at', $dates)
            ->count();
        $answers_10 = (clone $qb_first_day)->where('answers_count', '>=', 10)->count();

        $link_conversion_rate = round($answers_10_second / $answers_10, 2) * 100 . '%';

        DB::update('update user_activation set second_link_conversion_rate = ? where `date` = ? and `action` = ?', [$link_conversion_rate, $date, '完成10题']);
        echo '更新新用户激活漏斗 - 完成 10 题:' . $answers_10 . ' 日期:' . $date . "\n";

        // 完成提现
        // 前日注册用户并完成提现的用户主键
        //damei withdraws table  does not have the user_ID field
        $filed = '';
        $queryIds = null;
        if (config('app.name') == 'datizhuanqian') {
            $queryIds = DB::table('users')
                ->whereBetween('created_at', $newUserDates)
                ->pluck('id');
            $filed = 'user_id';
        } else {
            $queryIds = DB::table('wallets')
                ->whereBetween('created_at', $newUserDates)
                ->pluck('id');
            $filed = 'wallet_id';
        }

        $withdraws_user_ids = DB::table('withdraws')
            ->whereIn($filed, $queryIds)
            ->whereBetween('created_at', $newUserDates)
            ->distinct()
            ->pluck($filed);

        // 获取次日仍然提现的用户数量
        $withdraws = DB::table('withdraws')
            ->whereIn($filed, $withdraws_user_ids)
            ->whereBetween('created_at', $dates)
            ->count();

        // 获取前日提现用户数量
        $before_withdraws = DB::table('withdraws')
            ->whereIn($filed, $withdraws_user_ids)
            ->whereBetween('created_at', $newUserDates)
            ->count();

        $link_conversion_rate = round($withdraws / $before_withdraws, 2) * 100 . '%';

        DB::update('update user_activation set second_link_conversion_rate = ? where `date` = ? and `action` = ?', [$link_conversion_rate, $date, '完成提现']);

        echo '更新新用户激活漏斗 - 完成提现:' . $withdraws . ' 日期:' . $date . "\n";
    }


    /**
     * 用户平均答题数统计(筛选条件: 新老用户)
     *
     * note: 用户筛选条件为新老用户, 暂定创建时间节点为 7 月 19 日
     * @param $date
     */
    public function avgAnswersByUserCreatedAt($date)
    {
        $success_withdraw_type = [0, 1, 2, 3];
        $group_names = ['纯新用户', '新用户', '老用户', '纯老用户'];
        // 归档前天的数据
        $yesterday = Carbon::parse($date)->subDay(1)->toDateString(); // 昨天
        $before_yesterday = Carbon::parse($date)->subDay(2)->toDateString(); // 前天

        for ($int = 0; $int < count($success_withdraw_type); $int++) {

            // 纯新用户为 null ，因此区分了 SQL 语句
            if (empty($success_withdraw_type[$int])) {
                $users_count_db = DB::select('select count(DISTINCT(user_id)) as users_count from answer where user_id in (select id from user_profiles where success_withdraw_counts is null and created_at >= "2020-07-19") and created_at BETWEEN ? and ?;', [$before_yesterday . " 00:00:00", $yesterday . " 00:00:00"]);

                // 获取答题数量
                $answer_count_db = DB::select('select COUNT(*) as answer_count from answer where user_id in (select id from user_profiles where success_withdraw_counts is null and created_at >= "2020-07-19") and created_at BETWEEN ? and ?;', [$before_yesterday . " 00:00:00", $yesterday . " 00:00:00"]);
            } else {
                $users_count_db = DB::select('select count(DISTINCT(user_id)) as users_count from answer where user_id in (select id from user_profiles where success_withdraw_counts = ? and created_at >= "2020-07-19") and created_at BETWEEN ? and ?;', [$success_withdraw_type[$int], $before_yesterday . " 00:00:00", $yesterday . " 00:00:00"]);

                // 获取答题数量
                $answer_count_db = DB::select('select COUNT(*) as answer_count from answer where user_id in (select id from user_profiles where success_withdraw_counts = ? and created_at >= "2020-07-19") and created_at BETWEEN ? and ?;', [$success_withdraw_type[$int], $before_yesterday . " 00:00:00", $yesterday . " 00:00:00"]);
            }

            $users_count = current($users_count_db)->users_count;

            $sum_answer_count = current($answer_count_db)->answer_count;

            // 持久化
            $avg = round($sum_answer_count / $users_count);

            $dimension = Dimension::firstOrNew([
                'group' => '用户平均答题趋势',
                'name'  => $group_names[$int],
                'date'  => $before_yesterday,
            ]);
            $dimension->value = $avg;
            $dimension->save();
            $this->info($group_names[$int] . "平均答题统计完成🍺");
        }
    }


    // 每三小时发一次新增和提现统计短信 - 暂时停用
    public function smsAlert()
    {
        // if (!is_night()) {
        //         $withdrawAmountSum = Withdraw::whereBetween('created_at', [now()->subHours($intervalHours), now()])
        // ->where('status', Withdraw::SUCCESS_WITHDRAW)
        // ->sum('amount');
        //     $currentHour   = (int) now()->format('H');
        //     $intervalHours = 3;
        //     // 每三小时发一次新增和提现统计短信
        //     if ($currentHour / $intervalHours == 0) {
        //         $newUserCount      = User::whereBetween('created_at', [now()->subHours($intervalHours), now()])->count('id');
        //         $withdrawAmountSum = Withdraw::whereBetween('created_at', [now()->subHours($intervalHours), now()])
        //             ->where('status', Withdraw::SUCCESS_WITHDRAW)
        //             ->sum('amount');
        //         $sendData = [
        //             '1' => '3小时',
        //             '2' => $newUserCount . '位',
        //             '3' => $withdrawAmountSum,
        //         ];
        //         foreach ($this->phones as $phone) {
        //             try {
        //                 SMSUtils::sendNovaMessage($phone, 'NOVA_NEW_USER_WITHDRAW', $sendData);
        //             } catch (\Exception $exception) {
        //                 $this->error($exception->getMessage());
        //                 info("sms发送失败: {$phone}");
        //             }
        //         }
        //     }
        // }

    }
}
