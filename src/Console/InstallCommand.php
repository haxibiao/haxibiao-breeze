<?php

namespace Haxibiao\Breeze\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'breeze:install {--force}';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '安装 haxibiao/breeze';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $force = $this->option('force');
        if ($force) {
            if (!$this->confirm("强制重新安装breeze?")) {
                return;
            }
        } else {
            $this->info("安装Breeze");
        }

        $this->info(' - 复制 stubs ...');
        copyStubs(__DIR__, $force);

        $this->info(' - migrate基础数据库结构 ...');
        $this->callSilent('migrate');

        $this->info(' - seed 基础站点和用户 ');
        $this->callSilent("db:seed", ['--class' => "Haxibiao\Breeze\Seeders\UserSeeder"]);
        $this->callSilent("db:seed", ['--class' => "Haxibiao\Breeze\Seeders\SiteSeeder"]);

        $this->info(' - 安装子模块...');
        $this->installModules($force);

        $this->info(' - 发布资源...');
        $this->callSilent('breeze:publish', ['--force' => $force]);

        $this->comment("完成安装");
    }

    public function installModules($force)
    {
        $this->callSilent("config:install", ['--force' => $force]);
        $this->callSilent("media:install", ['--force' => $force]);
        $this->callSilent("content:install", ['--force' => $force]);
        $this->callSilent("sns:install", ['--force' => $force]);
        $this->callSilent("cms:install", ['--force' => $force]);
        $this->callSilent("task:install", ['--force' => $force]);
        $this->callSilent("dimension:install", ['--force' => $force]);
        $this->callSilent("wallet:install", ['--force' => $force]);

    }
}
