<?php

namespace Database\Seeders;

use Haxibiao\Breeze\Aso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_name    = env('APP_NAME');
        $app_name_cn = env('APP_NAME_CN');

        //apps sites切换数据库后，seed不同的账户名
        $db_name = DB::connection()->getDatabaseName();
        if ($db_name != $app_name) {
            $app_name = $db_name;
            //找到对应域名
            $app_domain = array_get(config('cms.apps_db'), $app_name) ?? array_get(config('cms.sites_db'), $app_name);
            //找到对应seo_site_name
            $seo_site_name = get_app_name_cn($app_domain);
            if ($seo_site_name != $app_name_cn) {
                $app_name_cn = $seo_site_name;
            }
        }

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '安卓地址',
        ]);
        // 示范代码，项目下需要覆盖AsoSeeder 或者 AsoTableSeeder来修复单个项目的app下载地址(小火箭的下载版本管理做好之前)
        $item->value = 'https://cos.haxibiao.com/apk/' . $app_name . '_4.0.0.apk';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '苹果地址',
        ]);
        // 示范代码，项目下需要覆盖AsoSeeder 或者 AsoTableSeeder来修复单个项目的app下载地址(小火箭的下载版本管理做好之前)
        $item->value = "itms-services:///\?action=download-manifest&url=https://cos.haxibiao.com/ipa/" . $app_name . "_4.0.0_1.manifest.plist";
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍1图',
        ]);
        $item->value = $item->value ?? '/images/app/1.png';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍1标题',
        ]);
        $item->value = $item->value ?? '精选美剧，告别剧荒';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍1文字',
        ]);
        $item->value = $item->value ?? '行尸走肉、西部世界、天赋异禀、黑袍纠察队、邪恶力量、暮光之城等精彩影视大片一网打尽，小编为您精心搜集了所有经典爆款、时下热门、最新上映的美剧资源，资源齐全更新快，全部支持免费在线观影！';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍2图',
        ]);
        $item->value = $item->value ?? '/images/app/2.png';
        $item->save();
        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍2标题',
        ]);
        $item->value = $item->value ?? '智能搜索，搜你想看';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍2文字',
        ]);
        $item->value = $item->value ?? '上疯狂美剧搜索你想看的全都能找到，再也不用到处求片找资源啦！一键搜索，所有海外影视剧资源全部帮你搞定！';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍3图',
        ]);
        $item->value = $item->value ?? '/images/app/3.png';
        $item->save();
        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍3标题',
        ]);
        $item->value = $item->value ?? '播放流畅 更新迅速';
        $item->save();
        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍3文字',
        ]);
        $item->value = $item->value ?? '剧集全、更新快、无广告，播放流畅，支持倍速播放想看哪里点哪里，看完一集自动连播，全身心享受观影新体验，让你追剧停不下来！';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍4图',
        ]);
        $item->value = $item->value ?? '/images/app/4.png';
        $item->save();
        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍4标题',
        ]);
        $item->value = $item->value ?? '精彩短视频 告别剧荒';
        $item->save();
        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '功能介绍4文字',
        ]);
        $item->value = $item->value ?? '不知道最近哪些美剧好看？上疯狂美剧帮你告别剧荒！我们有最新美剧评分、最热预告花絮、最精彩的剧情剪辑片段、 还有评论区的剧迷一起与你追星聊剧点评吐槽，疯狂美剧是美剧爱好者的天堂，看美剧请认准疯狂美剧！';
        $item->save();

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => 'logo',
        ]);
        $item->value = small_logo();

        $item->save();

    }
}
