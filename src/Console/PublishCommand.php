<?php

namespace Haxibiao\Breeze\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PublishCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'breeze:publish {--force}';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '发布 haxibiao/breeze 配置和资源...';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $force = $this->option('force');

        $this->info(' - 发布 lighthouse 和 playground');

        $this->callSilent("vendor:publish", ["--provider" => "Nuwave\Lighthouse\LighthouseServiceProvider", '--force' => $force]);
        $this->callSilent("vendor:publish", ["--provider" => "MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider", '--force' => $force]);

        $this->info(' - 发布 breeze 子模块的资源配置');

        $this->callSilent("vendor:publish", ['--tag' => 'cms-config', '--force' => $force]);
        $this->callSilent("vendor:publish", ['--tag' => 'cms-resources', '--force' => $force]);

        $this->callSilent("vendor:publish", ['--tag' => 'content-config', '--force' => $force]);
        $this->callSilent("vendor:publish", ['--tag' => 'content-graphql', '--force' => $force]);
        $this->callSilent("vendor:publish", ['--tag' => 'content-nova', '--force' => $force]);
        $this->callSilent("vendor:publish", ['--tag' => 'content-resources', '--force' => $force]);

        $this->callSilent("vendor:publish", ['--tag' => 'matomo-resources', '--force' => $force]);

        $this->callSilent("vendor:publish", ['--tag' => 'media-config', '--force' => $force]);
        $this->callSilent("vendor:publish", ['--tag' => 'media-applist', '--force' => $force]);
        $this->callSilent("vendor:publish", ['--tag' => 'media-graphql', '--force' => $force]);

        $this->callSilent("vendor:publish", ['--tag' => 'sns-config', '--force' => $force]);
        $this->callSilent("vendor:publish", ['--tag' => 'sns-graphql', '--force' => $force]);

        $this->info(' - Breeze的前端资源');
        $this->callSilent("vendor:publish", ['--tag' => 'breeze-resources', '--force' => $force]);
        // $this->publishVendorAssets($force);

    }

    public function publishVendorAssets($force)
    {
        // if (!is_dir(public_path('vendor/breeze/css/movie'))) {
        //     mkdir(public_path('vendor/breeze/css/movie'), 0777, true);
        // }
        // if (!is_dir(public_path('vendor/breeze/js/movie'))) {
        //     mkdir(public_path('vendor/breeze/js/movie'), 0777, true);
        // }
        // //默认图片
        // if (!is_dir(public_path('vendor/images'))) {
        //     mkdir(public_path('vendor/images'), 0777, true);
        // }

    }
}
