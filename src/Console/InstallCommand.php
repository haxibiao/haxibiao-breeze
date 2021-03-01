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
            $this->comment("安装Breeze");
        }
        $this->info(' - 安装子模块');
        $this->installModules($force);

        $this->info(' - 更新代码');
        copyStubs(__DIR__, $force);

        //修复laralve 8 新安装默认的 Auth Config 差异
        $auth_config = \file_get_contents(config_path('auth.php'));
        $auth_config = str_replace("App\Models\User", "App\User", $auth_config);
        file_put_contents(config_path('auth.php'), $auth_config);

        $this->info(' - 更新数据库结构');
        $this->callSilent('migrate');

        $this->info(' - 初始化基础数据');
        $this->callSilent("db:seed", ['--class' => "BreezeSeeder"]);

        $this->comment('发布资源');
        $this->call('breeze:publish', ['--force' => $force]);

        $this->comment("完成安装");
    }

    public function installModules($force)
    {
//        $this->callSilent("config:install", ['--force' => $force]);
        $this->callSilent("media:install", ['--force' => $force]);
        $this->callSilent("content:install", ['--force' => $force]);
        $this->callSilent("sns:install", ['--force' => $force]);
//        $this->callSilent("cms:install", ['--force' => $force]);
        $this->callSilent("task:install", ['--force' => $force]);
//        $this->callSilent("dimension:install", ['--force' => $force]);
        $this->callSilent("wallet:install", ['--force' => $force]);
        $this->callSilent("question:install", ['--force' => $force]);

    }
}
