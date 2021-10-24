<?php

namespace Haxibiao\Breeze\Console\Dimension;

use Illuminate\Console\Command;

class ArchiveAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:all {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天凌晨归档所有维度(新增，留存，广告，提现，变现)';

    protected $startedTime = null;

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
        $date = $this->option('date') ?? today()->toDateString();

        // //每日归档新用户数据
        $this->recordStarted();
        $this->call("archive:user", ['--date' => $date, '--newuser' => true]);
        $this->recordCompleted();

        //每日归档新老用户数据
        $this->recordStarted();
        $this->call("archive:user", ['--date' => $date, '--categoryuser' => true]);
        $this->recordCompleted();

        //每日归档新用户激活漏斗
        $this->recordStarted();
        $this->call("archive:user", ['--date' => $date, '--newUserActivation' => true]);
        $this->recordCompleted();

        //每日更新新用户激活漏斗中的次日留存率
        $this->recordStarted();
        $this->call("archive:user", ['--date' => $date, '--updateNewUserActivation' => true]);
        $this->recordCompleted();

        //每日更新分类用户平均答题数
        $this->recordStarted();
        $this->call("archive:user", ['--date' => $date, '--avg' => true]);
        $this->recordCompleted();

        //每日归档留存率
        $this->recordStarted();
        $this->call("archive:retention", ['--date' => $date]);
        $this->recordCompleted();

        //每日归档提现数据
        $this->recordStarted();
        $this->call("archive:withdraw", ['--date' => $date]);
        $this->recordCompleted();
    }

    public function recordStarted()
    {
        $this->startedTime = now();
        $this->info(str_pad(" run ready now:$this->startedTime ", 60, '=', STR_PAD_BOTH));
    }

    public function recordCompleted()
    {
        $now            = now();
        $processSeconds = $this->getPorcessSeconds($now);
        $this->info(str_pad(" run completed now:$now total_seconds:$processSeconds ", 60, '=', STR_PAD_BOTH));
    }

    public function getPorcessSeconds($time)
    {
        $seconds = !is_null($this->startedTime) ? $time->diffInSeconds($this->startedTime) : 0;

        return $seconds;
    }

}
