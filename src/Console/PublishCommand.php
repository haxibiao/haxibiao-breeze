<?php

namespace Haxibiao\Breeze\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PublishCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'breeze:publish {--force} {--gql}';

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

        if ($gql = $this->option('gql')) {
            $this->info("只发布gql...");

            // 清理breeze分散的gqls
            if (is_dir(base_path('graphql/article'))) {
                File::deleteDirectory(base_path('graphql'));
            }

            $this->call('vendor:publish', ['--tag' => 'media-graphql', '--force' => $force]);
            $this->call('vendor:publish', ['--tag' => 'store-graphql', '--force' => $force]);
            $this->call('vendor:publish', ['--tag' => 'content-graphql', '--force' => $force]);
            $this->call('vendor:publish', ['--tag' => 'sns-graphql', '--force' => $force]);
            $this->call('vendor:publish', ['--tag' => 'task-graphql', '--force' => $force]);
            $this->call('vendor:publish', ['--tag' => 'wallet-graphql', '--force' => $force]);
            $this->call('vendor:publish', ['--tag' => 'question-graphql', '--force' => $force]);
            $this->call('vendor:publish', ['--tag' => 'breeze-graphql', '--force' => $force]);
            return;
        }

        // lighthouse 和 playground
        // gqlp配置 后面自己手动修改
        $this->callSilent("vendor:publish", ["--provider" => "Nuwave\Lighthouse\LighthouseServiceProvider", '--force' => $force]);
        $this->callSilent("vendor:publish", ["--provider" => "MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider", '--force' => $force]);

        // breeze子模块的资源配置
        $this->callSilent('media:publish', ['--force' => $force]);
        $this->callSilent('content:publish', ['--force' => $force]);
        $this->callSilent('sns:publish', ['--force' => $force]);
        $this->callSilent('task:publish', ['--force' => $force]);
        $this->callSilent('wallet:publish', ['--force' => $force]);
        $this->callSilent('question:publish', ['--force' => $force]);

        $this->callSilent("vendor:publish", ['--provider' => 'Haxibiao\Breeze\BreezeServiceProvider', '--force' => $force]);

    }

}
