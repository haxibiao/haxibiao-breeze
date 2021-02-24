<?php

namespace Haxibiao\Breeze\Console;

use App\Invitation;
use Carbon\Carbon;
use Haxibiao\Breeze\UserRetention;
use Haxibiao\Breeze\Dimension;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ArchiveRetention extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:retention {--date=} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '按日归档用户留存信息';

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
        //注意：留存都是看前一天的
        $date = $this->option('date') ?? today()->toDateString();
        $type = $this->option('type');

        if ($type == 'retention') {
            return $this->calculateRetention($date);
        }
        if ($type == 'lost') {
            return $this->secondDayLostUser($date);
        }
        if ($type == 'keep') {
            return $this->secondDayKeepUser($date);
        }
        if ($type == 'contribution') {
            return $this->secondDayContribution($date);
        }

        $this->info("维度归档统计: 统计留存数据 ..." . $date);
        $this->calculateRetention($date);

        $this->info("维度归档统计: 次日流失用户 ..." . $date);
        $this->secondDayLostUser($date);

        $this->info("维度归档统计: 次日留存用户 ..." . $date);
        $this->secondDayKeepUser($date);

        $this->info("归档邀请用户次日留存 ..." . $date);
        $this->secondDayKeepInvitedUser($date);

        $this->info("归档次日贡献用户留存 ..." . $date);
        $this->secondDayContribution($date);
    }

    /**
     * 缓存留存率统计信息
     */
    public function calculateRetention(String $date)
    {
        //注意：留存都是看前一天的
        $this->info('缓存留存率统计信息');

        $this->info('统计次日留存数据中...');
        $result = Dimension::calculateRetention($date, 2, 'day2_at');
        $this->info('统计次日留存数据结果:' . $result);

        $this->info('统计三日留存率...');
        $result = Dimension::calculateRetention($date, 3, 'day3_at');
        $this->info('统计三日留存率结果:' . $result);

        $this->info('统计五日留存率...');
        $result = Dimension::calculateRetention($date, 5, 'day5_at');
        $this->info('统计五日留存率结果:' . $result);

        $this->info('统计七日留存率...');
        $result = Dimension::calculateRetention($date, 7, 'day7_at');
        $this->info('统计七日留存率结果:' . $result);

        $this->info('统计十五日留存率...');
        $result = Dimension::calculateRetention($date, 15, 'day15_at');
        $this->info('统计十五日留存率结果:' . $result);

        $this->info('统计三十日留存率...');
        $result = Dimension::calculateRetention($date, 30, 'day30_at');
        $this->info('统计三十日留存率结果:' . $result);
    }

    /**
     * 计算次日流失用户信息，如平均智慧点  (每日凌晨统计，前一日的)
     *
     * @return void
     */
    public function secondDayLostUser(String $date)
    {
        //注意：留存都是看前一天的
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay(1)->toDateString();
        $day2  = (clone $day)->subDay(2)->toDateString();
        $dates = [$day2, $day1];

        $qb_new_users = DB::table('users')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('user_retentions', 'users.id', '=', 'user_retentions.user_id')
            ->whereBetween('users.created_at', $dates);

        //次日流失的逻辑应该基于留存记录
        $qb_second_day = $qb_new_users->whereNull('user_retentions.day2_at');

        $partitions = $qb_second_day->groupBy('user_profiles.source')->selectRaw('count(*) as num, source');
        foreach ($partitions->get() as $part) {
            $dimension = Dimension::firstOrNew([
                'group' => '次日流失用户分布',
                'name'  => $part->source,
                'date'  => $date,
            ]);
            $dimension->value = $part->num;
            $dimension->save();
            echo '次日流失用户分布 - :' . $part->source . '  ' . $part->num . "\n";
        }

        $avgGold   = $qb_second_day->avg('gold') ?? 0;
        $dimension = Dimension::firstOrNew([
            'group' => '次日流失用户',
            'name'  => '平均智慧点',
            'date'  => $date,
        ]);
        $dimension->value = $avgGold;
        $dimension->save();
        echo '次日流失用户 - 平均智慧点:' . $avgGold . ' 日期:' . $date . "\n";

        $avg_answers_count = $qb_second_day->avg('user_profiles.answers_count') ?? 0;
        $dimension         = Dimension::firstOrNew([
            'group' => '次日流失用户',
            'name'  => '平均答题数',
            'date'  => $date,
        ]);
        $dimension->value = $avg_answers_count;
        $dimension->save();
        echo '次日流失用户 - 平均答题数:' . $avg_answers_count . ' 日期:' . $date . "\n";

        $max_answers_count = $qb_second_day->max('user_profiles.answers_count') ?? 0;
        $dimension         = Dimension::firstOrNew([
            'group' => '次日流失用户',
            'name'  => '最高答题数',
            'date'  => $date,
        ]);
        $dimension->value = $max_answers_count;
        $dimension->save();
        echo '次日流失用户 - 最高答题数:' . $max_answers_count . ' 日期:' . $date . "\n";

    }

    /**
     * 计算次日留存用户信息，如平均智慧点 (每日凌晨统计，前一日的)
     *
     * @return void
     */
    public function secondDayKeepUser(String $date)
    {
        //注意：留存都是看前一天的
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay(1)->toDateString();
        $day2  = (clone $day)->subDay(2)->toDateString();
        $dates = [$day2, $day1];

        $qb_new_users = DB::table('users')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('user_retentions', 'users.id', '=', 'user_retentions.user_id')
            ->whereBetween('users.created_at', $dates);

        //次日留存的逻辑应该基于留存记录
        $qb_second_day = $qb_new_users
            ->whereNotNull('user_retentions.day2_at');

        $partitions = $qb_second_day->groupBy('user_profiles.source')
            ->selectRaw('count(*) as num, source');
        foreach ($partitions->get() as $part) {
            $dimension = Dimension::firstOrNew([
                'group' => '次日留存用户分布',
                'name'  => $part->source,
                'date'  => $date,
            ]);
            $dimension->value = $part->num;
            $dimension->save();
            echo '次日留存用户分布 - :' . $part->source . '  ' . $part->num . "\n";
        }

        $avgGold   = $qb_second_day->avg('gold') ?? 0;
        $dimension = Dimension::firstOrNew([
            'group' => '次日留存用户',
            'name'  => '平均智慧点',
            'date'  => $date,
        ]);
        $dimension->value = $avgGold;
        $dimension->save();
        echo '次日留存用户 - 平均智慧点:' . $avgGold . ' 日期:' . $date . "\n";

        $avg_answers_count = $qb_second_day->avg('user_profiles.answers_count') ?? 0;
        $dimension         = Dimension::firstOrNew([
            'group' => '次日留存用户',
            'name'  => '平均答题数',
            'date'  => $date,
        ]);
        $dimension->value = $avg_answers_count;
        $dimension->save();
        echo '次日留存用户 - 平均答题数:' . $avg_answers_count . ' 日期:' . $date . "\n";

        $max_answers_count = $qb_second_day->max('user_profiles.answers_count') ?? 0;
        $dimension         = Dimension::firstOrNew([
            'group' => '次日留存用户',
            'name'  => '最高答题数',
            'date'  => $date,
        ]);
        $dimension->value = $max_answers_count;
        $dimension->save();
        echo '次日留存用户 - 最高答题数:' . $max_answers_count . ' 日期:' . $date . "\n";

    }

    /**
     * 计算十五天留存用户信息
     *
     * note:支撑留存未提现 与 提现已流失 用户数据分析
     */
    public function fifteenDayKeepUser(String $date)
    {
        //指定日期15天前留存用户
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay(1)->toDateString();
        $day15 = (clone $day)->subDay(15)->toDateString();
        $dates = [$day15, $day1];

        $qb_new_users = DB::table('users')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->join('user_retentions', 'users.id', '=', 'user_retentions.user_id')
            ->whereBetween('users.created_at', $dates);

        //计算15日后的留存逻辑
        $qb_second_day = $qb_new_users->whereNull('user_retentions');
    }

    public function secondDayKeepInvitedUser(String $date)
    {
        //注意：留存都是看前一天的
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay(1)->toDateString();
        $day2  = (clone $day)->subDay(2)->toDateString();
        $dates = [$day2, $day1];

        if (class_exists('\App\Invitation')) {
            $invitedUserNum = Invitation::select('id')->whereBetween('created_at', $dates)->where('invited_user_id', '>', 0)->count();

            $userRetentionNum = UserRetention::whereIn('user_id', function ($query) use ($dates) {
                $query->select('invited_user_id')
                    ->from('invitations')
                    ->whereBetween('created_at', $dates)
                    ->where('invited_user_id', '>', 0);
            })->whereBetween('day2_at', $dates)->count();

            if (0 != $userRetentionNum) {
                $result   = sprintf('%.2f', ($userRetentionNum / $invitedUserNum) * 100);
                $cachekey = sprintf(UserRetention::INVITED_USER_CACHE_FORMAT, $day1);
                // 只保留最近90天的数据
                cache()->store('database')->put($cachekey, $result, today()->addDay(90));
                echo $result;
            }
        }
    }

    /**
     * 归档次日贡献留存
     * note: 例如 $date 传入 2020-09-18 , 那么归档的新用户数据为 2020-09-16 ~ 2020-09-17 的数据
     * @param $date
     */
    public function secondDayContribution($date)
    {
        // 获取查询的时间区间
        $date_format = Carbon::make($date);

        // 前日 19
        $onTheDay = [(clone $date_format)->subDay(3)->toDateTimeString(), (clone $date_format)->subDay(2)->toDateTimeString()];
        // 昨日 20
        $yesterday = [(clone $date_format)->subDay(2)->toDateTimeString(), (clone $date_format)->subDay()->toDateTimeString()];

        // 计算次日贡献留存

        // 获取前日注册用户
        $day_register_counts = DB::table('users')
            ->whereBetween('created_at', $onTheDay)
            ->pluck('id');

        // 获取前日注册用户在昨日获得的贡献值人数
        $yesterday_has_contribution_counts = DB::table('gold')
            ->select('user_id')
            ->distinct()
            ->whereIn('user_id', $day_register_counts)
            ->whereBetween('created_at', $yesterday)
            ->get();
        $contribution = round(count($yesterday_has_contribution_counts) / count($day_register_counts), 2) * 100;

        $dimension = Dimension::firstOrNew([
            'date'  => (clone $date_format)->subDay()->toDateString(),
            'name'  => '次日贡献留存',
            'value' => $contribution,
            'group' => '次日留存用户',
        ]);

        $dimension->save();
        echo '次日留存 - 贡献:' . $contribution . "\n";
    }
}
