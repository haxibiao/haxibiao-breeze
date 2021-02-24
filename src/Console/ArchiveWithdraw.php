<?php

namespace Haxibiao\Breeze\Console;

use App\Exchange;
use App\User;
use App\Withdraw;
use Carbon\Carbon;
use Haxibiao\Breeze\Dimension;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ArchiveWithdraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:withdraw {--date=} {--type= : 可选level,stay,gold等}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '归档提现相关用户的数据';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("归档提现相关的数据....");
        $date = $this->option('date') ?? today()->toDateString();
        $type = $this->option('type');

        if (is_null($type)) {
            $this->archiveLevel($date);
            $this->archiveStay($date);
            $this->archiveQuestion($date);
            $this->archiveAnswer($date);
            $this->archiveComment($date);
            $this->archiveLike($date);
            $this->archivePost($date);
            $this->archiveGold($date);
            $this->archiveContribute($date);
            $this->archiveTotal($date);
            $this->archiveSecondWithdraw($date);
            $this->archiveThirdWithdraw($date);
        } else {
            $type          = ucfirst($type);
            $archiveAction = "archive${type}";
            $this->$archiveAction($date);
        }
    }

    public function archiveSecondWithdraw($date)
    {
        $group = '新用户次日提现趋势';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->join('user_retentions', 'withdraws.user_id', 'user_retentions.user_id')
            ->whereAmount(0.5)
            ->whereBetween('user_retentions.day2_at', $dates)
            ->whereBetween('withdraws.created_at', $dates);
        $dimension = Dimension::firstOrCreate([
            'date' => $date,
            'name' => $group,
        ]);
        $dimension->value = $qb->count();
        $dimension->save();
        $this->info("$dimension->name $dimension->value");
    }

    public function archiveThirdWithdraw($date)
    {
        $group = '新用户第三日提现趋势';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->join('user_retentions', 'withdraws.user_id', 'user_retentions.user_id')
            ->whereAmount(0.5)
            ->whereBetween('user_retentions.day3_at', $dates)
            ->whereBetween('withdraws.created_at', $dates);
        $dimension = Dimension::firstOrCreate([
            'date' => $date,
            'name' => $group,
        ]);
        $dimension->value = $qb->count();
        $dimension->save();
        $this->info("$dimension->name $dimension->value");
    }

    public function archiveLike($date)
    {
        $group = '重复提现用户点赞量';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = Withdraw::with('user.user_profile')
            ->whereAmount(0.5)
            ->whereBetween('created_at', $dates);
        $result = [];
        $qb->orderBy('user_id')->chunk(100, function ($items) use (&$result, $day) {
            foreach ($items as $item) {
                $user                 = $item->user;
                $profile              = $user->profile;
                $profile->likes_count = $profile->likes_count ?? $user->likes()->count();
                $profile->saveDataOnly();
                $count = $profile->likes_count;
                $g     = '无点赞';
                if ($count >= 1 && $count <= 5) {
                    $g = '1-5次点赞';
                }
                if ($count > 5 && $count <= 10) {
                    $g = '5-10次点赞';
                }
                if ($count > 10) {
                    $g = '10+次点赞';
                }
                if ($count > 100) {
                    $g = '100+次点赞';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();
            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archiveComment($date)
    {
        $group = '重复提现用户评论量';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = Withdraw::with('user.user_profile')
            ->whereAmount(0.5)
            ->whereBetween('created_at', $dates);
        $result = [];
        $qb->orderBy('user_id')->chunk(100, function ($items) use (&$result, $day) {
            foreach ($items as $item) {
                $user                    = $item->user;
                $profile                 = $user->profile;
                $profile->comments_count = $profile->comments_count ?? $user->hasComments()->count();
                $profile->saveDataOnly();
                $count = $profile->comments_count;
                $g     = '无评论';
                if ($count >= 1 && $count <= 5) {
                    $g = '1-5条评论';
                }
                if ($count > 5 && $count <= 10) {
                    $g = '5-10条评论';
                }
                if ($count > 10) {
                    $g = '10+条评论';
                }
                if ($count > 100) {
                    $g = '100+条评论';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();
            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archiveAnswer($date)
    {
        $group = '重复提现用户答题量';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('user_profiles', 'withdraws.user_id', '=', 'user_profiles.user_id')
            ->whereBetween('withdraws.created_at', $dates)
            ->selectRaw('user_profiles.answers_count, user_profiles.user_id, user_profiles.created_at');
        $result = [];
        $qb->orderBy('user_profiles.id')->chunk(100, function ($items) use (&$result) {
            foreach ($items as $item) {
                $count = $item->answers_count;
                $g     = '未答题';
                if ($count >= 1 && $count <= 10) {
                    $g = '答1-10题';
                }
                if ($count >= 10 && $count <= 20) {
                    $g = '答10-20题';
                }
                if ($count > 20 && $count <= 50) {
                    $g = '答20-50题';
                }
                if ($count > 50) {
                    $g = '答50题以上';
                }
                if ($count > 200) {
                    $g = '答200题以上';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
                if ($g == '未答题') {
                    $this->info($item->answers_count . " " . $item->user_id . " " . $item->created_at);
                }
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();

            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archivePost($date)
    {
        $group = '重复提现用户动态量';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('user_profiles', 'withdraws.user_id', '=', 'user_profiles.user_id')
            ->whereBetween('withdraws.created_at', $dates)
            ->selectRaw('user_profiles.user_id, user_profiles.posts_count');
        $result = [];
        $qb->orderBy('user_profiles.id')->chunk(100, function ($items) use (&$result) {
            foreach ($items as $item) {
                $count = !is_null($item->posts_count) ? $item->posts_count :
                User::find($item->user_id)->posts()->count();
                $g = '未发布动态';
                if ($count >= 1 && $count <= 2) {
                    $g = '1-2动态';
                }
                if ($count > 2 && $count <= 5) {
                    $g = '2-5动态';
                }
                if ($count > 5) {
                    $g = '5+动态';
                }
                if ($count > 20) {
                    $g = '20+动态';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();

            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archiveQuestion($date)
    {
        $group = '重复提现用户出题量';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('user_profiles', 'withdraws.user_id', '=', 'user_profiles.user_id')
            ->whereBetween('withdraws.created_at', $dates)
            ->selectRaw('user_profiles.questions_count');
        $result = [];
        $qb->orderBy('user_profiles.id')->chunk(100, function ($items) use (&$result) {
            foreach ($items as $item) {
                $count = $item->questions_count;
                $g     = '未出题';
                if ($count >= 1 && $count <= 2) {
                    $g = '1-2题';
                }
                if ($count > 2 && $count <= 5) {
                    $g = '2-5题';
                }
                if ($count > 5) {
                    $g = '5+题';
                }
                if ($count > 20) {
                    $g = '20+题';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();

            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archiveStay($date)
    {
        $group = '重复提现用户注册时间';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('users', 'withdraws.user_id', '=', 'users.id')
            ->whereBetween('withdraws.created_at', $dates)
            ->selectRaw('users.created_at');
        $result = [];
        $qb->orderBy('users.id')->chunk(100, function ($items) use (&$result, $day) {
            foreach ($items as $item) {
                $stayDays = $day->diffInDays(Carbon::parse($item->created_at)->startOfDay());
                $stay     = '2天内';
                if ($stayDays > 2 && $stayDays <= 7) {
                    $stay = '1周内';
                }
                if ($stayDays > 7 && $stayDays <= 30) {
                    $stay = '1月内';
                }
                if ($stayDays > 30) {
                    $stay = '1月以上';
                }
                if ($stayDays > 60) {
                    $stay = '2月以上';
                }
                $result[$stay] = !isset($result[$stay]) ? 1 : ++$result[$stay];
            }
        });
        foreach ($result as $stay => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $stay,
            ]);
            $dimension->value = $num;
            $dimension->save();

            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archiveLevel($date)
    {
        $group = '重复提现用户等级分布';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('users', 'withdraws.user_id', '=', 'users.id')
            ->whereBetween('withdraws.created_at', $dates);
        $result = $qb->groupBy('users.level_id')
            ->selectRaw('count(*) as num, level_id')
            ->orderByDesc('num')
            ->get();
        foreach ($result as $item) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $item->level_id . '级',
            ]);
            $dimension->value = $item->num;
            $dimension->save();
            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archiveGold($date)
    {
        $group = '重复提现用户资产分布';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('users', 'withdraws.user_id', '=', 'users.id')
            ->whereBetween('withdraws.created_at', $dates)
            ->selectRaw('users.gold');
        $result = [];
        $this->info("人数:" . $qb->count());
        $qb->orderBy('users.id')->chunk(100, function ($items) use (&$result, $day) {
            foreach ($items as $item) {
                $money = ceil($item->gold / Exchange::RATE);
                $g     = '1元内';
                if ($money > 1 && $money < 5) {
                    $g = '1-5元';
                }
                if ($money >= 5 && $money < 20) {
                    $g = '5-20元';
                }
                if ($money >= 20) {
                    $g = '20元以上';
                }
                if ($money >= 100) {
                    $g = '百元土豪';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();
            $this->info("$dimension->name $dimension->value");
        }

    }

    public function archiveContribute($date)
    {
        $group = '重复提现用户日贡献分布';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('users', 'withdraws.user_id', '=', 'users.id')
            ->whereBetween('withdraws.created_at', $dates)
            ->selectRaw('users.today_contributes');
        $result = [];
        $this->info("人数:" . $qb->count());
        $qb->orderBy('users.id')->chunk(100, function ($items) use (&$result, $day) {
            foreach ($items as $item) {
                $num = $item->today_contributes;
                $g   = '<=30(半夜活跃会清零)';
                if ($num > 30 && $num < 70) {
                    $g = '30-70';
                }
                if ($num >= 70 && $num < 200) {
                    $g = '70-200';
                }
                if ($num >= 200) {
                    $g = '200以上';
                }
                if ($num >= 1000) {
                    $g = '1000+';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();
            $this->info("$dimension->name $dimension->value");
        }
    }

    public function archiveTotal($date)
    {
        $group = '重复提现用户总提现分布';
        $this->info(" -  ${group} " . $date);
        $day   = Carbon::parse($date);
        $day1  = (clone $day)->subDay();
        $dates = [$day1, $day];
        $qb    = DB::table('withdraws')
            ->whereAmount(0.5)
            ->join('wallets', 'withdraws.wallet_id', '=', 'wallets.id')
            ->whereBetween('withdraws.created_at', $dates)
            ->selectRaw('wallets.total_withdraw_amount');
        $result = [];
        $this->info("人数:" . $qb->count());
        $qb->orderBy('wallets.id')->chunk(100, function ($items) use (&$result, $day) {
            foreach ($items as $item) {
                $num = $item->total_withdraw_amount;
                $g   = '<5';
                if ($num >= 5 && $num < 10) {
                    $g = '5-10';
                }
                if ($num >= 10 && $num < 20) {
                    $g = '10-20';
                }
                if ($num >= 20) {
                    $g = '20以上';
                }
                if ($num >= 100) {
                    $g = '100+';
                }
                $result[$g] = !isset($result[$g]) ? 1 : ++$result[$g];
            }
        });
        foreach ($result as $g => $num) {
            $dimension = Dimension::firstOrCreate([
                'date'  => $date,
                'group' => $group,
                'name'  => $g,
            ]);
            $dimension->value = $num;
            $dimension->save();
            $this->info("$dimension->name $dimension->value");
        }
    }
}
