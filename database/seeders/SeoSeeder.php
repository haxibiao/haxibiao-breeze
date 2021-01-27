<?php
namespace Database\Seeders;

use Haxibiao\Config\Seo;
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
