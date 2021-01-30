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

        $this->info(' - lighthouse 和 playground');

        //gqlp配置容易手动修改
        $this->callSilent("vendor:publish", ["--provider" => "Nuwave\Lighthouse\LighthouseServiceProvider", '--force' => false]);
        $this->callSilent("vendor:publish", ["--provider" => "MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider", '--force' => false]);

        $this->info(' - breeze 子模块的资源配置');

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

        $this->callSilent('wallet:publish', ['--force' => $force]);
        $this->callSilent('question:publish', ['--force' => $force]);

        $this->info(' - Breeze的资源');
        $this->callSilent("vendor:publish", ['--provider' => 'Haxibiao\Breeze\BreezeServiceProvider', '--force' => $force]);

    }

}
