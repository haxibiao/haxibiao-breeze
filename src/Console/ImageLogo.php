<?php

namespace Haxibiao\Breeze\Console;

use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class ImageLogo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:logo {--domain=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成web所需的各尺寸图片logo文件';

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
        $this->info('开始执行 image:logo 命令 生成logo文件...');
        $domain = $this->option('domain') ?? env('APP_DOMAIN');
        if ($domain) {
            if (!file_exists(public_path('logo.png'))) {
                $this->error("必须在public目录下放入APP的icon,尺寸1024x1024,文件名logo.png");
                return 0;
            }
            $this->makeLogo($domain);
        } else {
            $this->error('必须设置env里的 APP_DOMAIN ...');
            return 0;
        }
    }

    public function makeLogo($domain)
    {
        if (!file_exists(public_path('logo'))) {
            mkdir(public_path('logo'));
        }

        $image = Image::make(public_path('logo.png'));
        $image->resize(60, 60);
        $logoPath = public_path('logo/' . $domain . '.small.png');
        $image->save($logoPath);
        $this->info($logoPath);

        $image->resize(190, 190);
        $logoPath = public_path('logo/' . $domain . '.text.png');
        $image->save($logoPath);
        $this->info($logoPath);

        $image->resize(120, 120);
        $logoPath = public_path('logo/' . $domain . '.web.png');
        $image->save($logoPath);
        $this->info($logoPath);

        $image->resize(160, 160);
        $logoPath = public_path('logo/' . $domain . '.touch.png');
        $image->save($logoPath);
        $this->info($logoPath);
    }
}
