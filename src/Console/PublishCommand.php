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

        // lighthouse 和 playground
        // gqlp配置 后面自己手动修改
        $this->callSilent("vendor:publish", ["--provider" => "Nuwave\Lighthouse\LighthouseServiceProvider", '--force' => $force]);
        $this->callSilent("vendor:publish", ["--provider" => "MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider", '--force' => $force]);

        // 清理breeze分散的gqls
        File::deleteDirectory(base_path('graphql'));

        // breeze子模块的资源配置
        $this->callSilent('media:publish', ['--force' => $force]);
        $this->callSilent('content:publish', ['--force' => $force]);
        $this->callSilent('sns:publish', ['--force' => $force]);
        $this->callSilent('task:publish', ['--force' => $force]);
        $this->callSilent('wallet:publish', ['--force' => $force]);
        $this->callSilent('question:publish', ['--force' => $force]);

        $this->callSilent("vendor:publish", ['--provider' => 'Haxibiao\Breeze\BreezeServiceProvider', '--force' => true]);

    }

}
