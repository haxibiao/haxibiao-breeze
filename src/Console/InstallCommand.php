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

        $this->info('publish vendor css js');
        //复制所有nova stubs
        $this->publishVendorAssets($force);

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

    public function publishVendorAssets($force)
    {
        if (!is_dir(public_path('vendor/breeze/css/movie'))) {
            mkdir(public_path('vendor/breeze/css/movie'), 0777, true);
        }
        if (!is_dir(public_path('vendor/breeze/js/movie'))) {
            mkdir(public_path('vendor/breeze/js/movie'), 0777, true);
        }

        $pwd = __DIR__;
        foreach (glob($pwd . '/../../public/css/*.css') as $filepath) {
            $filename = basename($filepath);
            $dest     = public_path('vendor/breeze/css/' . $filename);
            if (!file_exists($dest) || $force) {
                copy($filepath, $dest);
            }
        }
        foreach (glob($pwd . '/../../public/css/movie/*.css') as $filepath) {
            $filename = basename($filepath);
            $dest     = public_path('vendor/breeze/css/movie/' . $filename);
            if (!file_exists($dest) || $force) {
                copy($filepath, $dest);
            }
        }

        foreach (glob($pwd . '/../../public/js/*.js') as $filepath) {
            $filename = basename($filepath);
            $dest     = public_path('vendor/breeze/js/' . $filename);
            if (!file_exists($dest) || $force) {
                copy($filepath, $dest);
            }
        }
        foreach (glob($pwd . '/../../public/js/movie/*.js') as $filepath) {
            $filename = basename($filepath);
            $dest     = public_path('vendor/breeze/js/movie/' . $filename);
            if (!file_exists($dest) || $force) {
                copy($filepath, $dest);
            }
        }

    }
}
