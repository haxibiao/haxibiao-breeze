<?php
namespace Database\Seeders;

use Haxibiao\Breeze\Seo;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Seo::truncate();

        //未备案代理跳板域名 修复 get_domain 返回顶级域名 - 示范
        // $item = Seo::firstOrCreate([
        //     'group' => 'proxy_domains',
        //     'name'  => env('APP_DOMAIN', 'yingdaqian.com'),  //SEO用顶级未备案域名
        //     'value' => env('APP_DOMAIN_PROXY', 'yingdaquan.haxifang.com'), //实际机房请求的备案二级域名
        // ]);
        // $item->save();
        //多域名站群实例，proxy_domains可以有多个，用让 get_domain()返回顶级seo用途域名

        //TKD
        $item = Seo::firstOrCreate([
            'group' => 'TKD',
            'name'  => 'title',
        ]);
        $item->value = '疯狂看美剧，快乐无极限';
        $item->save();
        $item = Seo::firstOrCreate([
            'group' => 'TKD',
            'name'  => 'keywrods',
        ]);
        $item->value = '精选美剧，告别剧荒，播放流畅，更新迅速，精彩短视频，抖音精彩电影剪辑合集';
        $item->save();

        $item = Seo::firstOrCreate([
            'group' => 'TKD',
            'name'  => 'description',
        ]);
        $item->value = '行尸走肉、西部世界、天赋异禀、黑袍纠察队、邪恶力量、暮光之城等精彩影视大片一网打尽，小编为您精心搜集了所有经典爆款、时下热门、最新上映的美剧资源，资源齐全更新快，全部支持免费在线观影！';
        $item->save();

        //站长 - 验证
        $item = Seo::firstOrCreate([
            'group' => '站长',
            'name'  => 'meta',
        ]);
        $item->value = '';
        $item->save();

        //站长 - 提交
        $item = Seo::firstOrCreate([
            'group' => '站长',
            'name'  => 'token',
        ]);
        $item->value = '';
        $item->save();

    }
}
