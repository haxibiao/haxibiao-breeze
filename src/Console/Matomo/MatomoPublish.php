<?php
namespace Haxibiao\Breeze\Console\Matomo;

use Illuminate\Console\Command;

class MatomoPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matomo:publish {--force : 覆盖旧config文件}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布matomo的config';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // 旧配置文件自定义后，不方便覆盖，更新需要单独
        // vendor:publish --tag=matomo-config --force=true

        $this->call('vendor:publish', [
            '--tag'   => 'matomo-config',
            '--force' => false,
        ]);
    }
}
