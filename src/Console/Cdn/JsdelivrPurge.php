<?php

namespace Haxibiao\Breeze\Console\Cdn;

use Illuminate\Console\Command;

class JsdelivrPurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdn:jsdelivr:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'purge jsdelivr cdn';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("purge js 缓存 ...");
        $rs = @file_get_contents('https://purge.jsdelivr.net/gh/haxibiao/haxibiao-media@latest/public/js/media.min.js');
        $this->comment($rs);
        $rs = @file_get_contents('https://purge.jsdelivr.net/gh/haxibiao/haxibiao-media@latest/public/js/media.js');
        $this->comment($rs);

        $this->info("purge css 缓存 ...");
        $rs = @file_get_contents('https://purge.jsdelivr.net/gh/haxibiao/haxibiao-media@latest/public/css/media.min.css');
        $this->comment($rs);
        $rs = @file_get_contents('https://purge.jsdelivr.net/gh/haxibiao/haxibiao-media@latest/public/css/media.css');
        $this->comment($rs);

        return 0;
    }

}
