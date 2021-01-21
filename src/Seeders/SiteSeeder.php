<?php
namespace Haxibiao\Breeze\Seeders;

use App\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_DOMAIN') == null) {
            dd("请设置 网站默认域名 env('APP_DOMAIN')");
        }
        if (env('APP_NAME_CN') == null) {
            dd("请设置 网站中文名称 env('APP_NAME_CN')");
        }

        $domains = [
            env('APP_DOMAIN', 'diudie.com') => env('APP_NAME_CN', '丢碟图解'),
        ];

        foreach ($domains as $domain => $name) {
            $item = Site::firstOrCreate([
                'domain' => $domain,
                'name'   => $name,
            ]);
            $item->title        = '疯狂看港剧，快乐无极限';
            $item->keywords     = '在线港剧，在线韩剧，经典港剧，怀旧港剧，高清日剧';
            $item->description  = $name . ' ' . $domain . ' 是一个可以免费看全网影视大全的内涵电影网站';
            $item->ziyuan_token = ''; //站长的token
            $item->owner        = ''; //站长的名字
            $item->verify_meta  = '';
            $item->footer_js    = '';
            $item->active       = true;
            $item->save();
        }
    }
}
