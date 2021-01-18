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

        $this->info('复制 stubs ...');
        copyStubs(__DIR__, $force);

        $this->info('安装子模块...');
        $this->installModules($force);

    }

    public function installModules($force)
    {
        $this->call("config:install", ['--force' => $force]);
        $this->call("media:install", ['--force' => $force]);
        $this->call("content:install", ['--force' => $force]);
        $this->call("sns:install", ['--force' => $force]);
        $this->call("cms:install", ['--force' => $force]);
    }
}
