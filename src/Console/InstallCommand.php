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
        $this->info("开始安装Breeze, 强制=" . $force);

        $this->info(' - 复制 stubs ...');
        copyStubs(__DIR__, $force);

        $this->info(' - migrate基础数据库结构 ...');
        $this->call('migrate');

        $this->info(' - seed 基础站点和用户 ');
        $this->call("db:seed", ['--class' => "Haxibiao\Breeze\Seeders\UserSeeder"]);
        $this->call("db:seed", ['--class' => "Haxibiao\Breeze\Seeders\SiteSeeder"]);

        $this->info('- 安装子模块...');
        $this->installModules($force);

    }

    public function installModules($force)
    {
        $this->call("config:install", ['--force' => $force]);
        $this->call("media:install", ['--force' => $force]);
        $this->call("content:install", ['--force' => $force]);
        $this->call("sns:install", ['--force' => $force]);
        $this->call("cms:install", ['--force' => $force]);
        $this->call("task:install", ['--force' => $force]);
        $this->call("dimension:install", ['--force' => $force]);
        $this->call("wallet:install", ['--force' => $force]);

    }
}
