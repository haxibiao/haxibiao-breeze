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
    protected $signature = 'image:logo {--domain= : 多域名站群} {--pwa : PWA用icons和splashs} {--icon= : APP图标源文件} {--splash= : 启动图源文件}';

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

        $this->info('生成breeze网站基础模板所需icons...');
        $domain = $this->option('domain') ?? env('APP_DOMAIN');
        if (blank($domain)) {
            $this->error('必须设置env里的 APP_DOMAIN ...');
        }
        if (!$this->option('icon') && !file_exists(public_path('logo.png'))) {
            $this->error("必须在public目录下放入APP的icon,尺寸1024x1024,文件名:logo.png");
        }
        $this->makeLogo($domain);

        if ($this->option('pwa')) {
            $this->info('生成breeze网站PWA所需icons...');
            $this->makePwaIcons($domain);

            if (!$this->option('splash') && !file_exists(public_path('splash.png'))) {
                $this->error("必须在public目录下放入APP的竖版splash图,文件名:splash.png");
            }
            $this->makePwaSplashs($domain);
        }

        return 0;
    }

    public function makePwaSplashs($domain)
    {
        $this->comment('开始pwa所需splashs的生成');

        if (!file_exists(public_path('images/icons/' . $domain))) {
            mkdir(public_path('images/icons/' . $domain));
        }
        $splash_path = $this->option('splash') ? public_path($this->option('splash')) : public_path('splash.png');
        $image       = Image::make($splash_path);
        $w           = 640;
        $h           = 1136;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 750;
        $h = 1334;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 828;
        $h = 1792;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 1125;
        $h = 2436;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 1242;
        $h = 2208;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 1242;
        $h = 2688;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 1536;
        $h = 2048;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 1668;
        $h = 2224;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 1668;
        $h = 2388;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $w = 2048;
        $h = 2732;
        $image->resize($w, $h);
        $image->save(public_path('images/icons/' . $domain . '/splash-' . $w . 'x' . $h . '.png'));

        $this->info('完成所有pwa所需splashs的生成');
    }

    public function makePwaIcons($domain)
    {
        $this->comment('开始pwa所需icons的生成');

        if (!file_exists(public_path('images/icons/' . $domain))) {
            mkdir(public_path('images/icons/' . $domain));
        }
        $icon_path = $this->option('icon') ? public_path($this->option('icon')) : public_path('logo.png');

        $image = Image::make($icon_path);
        $size  = 72;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $size = 96;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $size = 128;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $size = 144;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $size = 152;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $size = 192;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $size = 384;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $size = 512;
        $image->resize($size, $size);
        $image->save(public_path('images/icons/' . $domain . '/icon-' . $size . 'x' . $size . '.png'));

        $this->info('完成所有pwa所需icons的生成');
    }

    public function makeLogo($domain)
    {
        if (!file_exists(public_path('logo'))) {
            mkdir(public_path('logo'));
        }

        //默认logo
        $icon_path = public_path('logo.png');
        //复用app的icon(1024*1024)
        if ($icon = $this->option('icon')) {
            $icon_path = $icon;
        }
        //自动搜索每个域名的logo
        if (file_exists(public_path('logo/' . $domain . '.png'))) {
            $icon_path = public_path('logo/' . $domain . '.png');
        }

        $image = Image::make($icon_path);

        //FIXME: 模板中用text_logo的全部需要换banner/get_domain_key().png

        $image->resize(160, 160);
        $logoPath = public_path('logo/' . $domain . '.touch.png');
        $image->save($logoPath);
        $this->comment(" - " . $logoPath);

        $image->resize(120, 120);
        $logoPath = public_path('logo/' . $domain . '.web.png');
        $image->save($logoPath);
        $this->comment(" - " . $logoPath);

        $image->resize(60, 60);
        $logoPath = public_path('logo/' . $domain . '.small.png');
        $image->save($logoPath);
        $this->comment(" - " . $logoPath);

    }
}
