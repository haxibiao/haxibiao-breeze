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
    protected $signature = 'image:logo {--domain=} {--icons} {--splashs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成web、app所需的各尺寸icons splash';

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
        if ($this->option('icons')) {
            $this->makePwaIcons();
            return 0;
        }

        if ($this->option('splashs')) {
            $this->makePwaSplashs();
            return 0;
        }

        $this->info('生成网站icons图片...');
        $domain = $this->option('domain') ?? env('APP_DOMAIN');
        if ($domain) {
            if (!file_exists(public_path('logo.png'))) {
                $this->error("必须在public目录下放入APP的icon,尺寸1024x1024,文件名logo.png");
            }
            $this->makeLogo($domain);
        } else {
            $this->error('必须设置env里的 APP_DOMAIN ...');
        }
        return 0;
    }

    public function makePwaSplashs()
    {
        if (!file_exists(public_path('images/icons'))) {
            mkdir(public_path('images/icons'));
        }

        $image = Image::make(public_path('splash.png'));
        $w     = 640;
        $h     = 1136;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 750;
        $h = 1334;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 828;
        $h = 1792;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 1125;
        $h = 2436;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 1242;
        $h = 2208;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 1242;
        $h = 2688;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 1536;
        $h = 2048;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 1668;
        $h = 2224;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 1668;
        $h = 2388;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $w = 2048;
        $h = 2732;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/splash-' . $w . 'x' . $h . '.png'));

        $this->info('完成所有pwa splashs的生成');
    }

    public function makePwaIcons()
    {
        if (!file_exists(public_path('images/icons'))) {
            mkdir(public_path('images/icons'));
        }

        $image = Image::make(public_path('logo.png'));
        $size  = 72;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $size = 96;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $size = 128;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $size = 144;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $size = 152;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $size = 192;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $size = 384;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $size = 512;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/icon-' . $size . 'x' . $size . '.png'));

        $this->info('完成所有pwa icons的生成');
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
