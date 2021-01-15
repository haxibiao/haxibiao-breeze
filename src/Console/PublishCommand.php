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

        $this->info('先发布 lighthouse 和 playground..');

        $this->call("vendor:publish", ["--provider" => "Nuwave\Lighthouse\LighthouseServiceProvider"]);
        $this->call("vendor:publish", ["--provider" => "MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider"]);

        $this->info('再发布breeze的子模块..');

        $this->call("vendor:publish", ['--tag' => 'cms-config']);
        $this->call("vendor:publish", ['--tag' => 'cms-resources']);

        $this->call("vendor:publish", ['--tag' => 'content-config']);
        $this->call("vendor:publish", ['--tag' => 'content-graphql']);
        $this->call("vendor:publish", ['--tag' => 'content-nova']);
        $this->call("vendor:publish", ['--tag' => 'content-resources']);

        $this->call("vendor:publish", ['--tag' => 'matomo-resources']);

        $this->call("vendor:publish", ['--tag' => 'media-config']);
        $this->call("vendor:publish", ['--tag' => 'media-applist']);
        $this->call("vendor:publish", ['--tag' => 'media-graphql']);

        $this->call("vendor:publish", ['--tag' => 'sns-config']);
        $this->call("vendor:publish", ['--tag' => 'sns-graphql']);

    }
}
