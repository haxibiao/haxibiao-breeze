<?php

namespace Haxibiao\Breeze\Http\Api;

use App\AdConfig;
use App\AppConfig;
use App\Http\Controllers\Controller;
use App\Version;
use Haxibiao\Breeze\Aso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AppController extends Controller
{

    public function hookApkUpload()
    {
        $app     = request('app', config('app.name'));
        $version = request('version', "1.0.0");
        $build   = request('build', "1");

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '安卓地址',
        ]);
        $item->value = "https://cos.haxibiao.com/apk/${app}_${version}_${build}.apk";
        $item->save();

        // 刷新预热 apk cdn
        $pushCdnUrl = "http://haxiyun.cn/api/cdn/pushUrlsCache?urls[]=https://cos.haxibiao.com/apk/${app}_${version}_${build}.apk";
        @file_get_contents($pushCdnUrl);

        return $item;
    }

    public function hookIpaUpload()
    {
        $app     = request('app', config('app.name'));
        $version = request('version', "1.0.0");
        $build   = request('build', "1");

        $item = Aso::firstOrCreate([
            'group' => '下载页',
            'name'  => '苹果地址',
        ]);
        $item->value = "itms-services:///?action=download-manifest&url=https://cos.haxibiao.com/ipa/${app}_${version}_${build}.ipa/manifest.plist";
        $item->save();

        // 刷新预热 ipa cdn
        $pushCdnUrl = "http://haxiyun.cn/api/cdn/pushUrlsCache?urls[]=https://cos.haxibiao.com/ipa/${app}_${version}_${build}.ipa";
        @file_get_contents($pushCdnUrl);

        return $item;
    }

    //返回 app-config (含ad config)
    public function appConfig(Request $request)
    {
        //方便 &name=android 这样调试, 兼容旧版本传name..
        $group = request('name') ?? $request->header('name');
        $store = request('store') ?? $request->header('store');

        if (empty($group)) {
            $group = request('os') ?? $request->header('os');
        }

        //华为单独有开关
        if ("huawei" == $store) {
            $group = 'huawei';
        }

        $array   = [];
        $configs = AppConfig::whereGroup($group)->get();

        foreach ($configs as $config) {
            $array[$config->name] = $config->status;
        }

        //AdConfig的合并一起返回
        $array = array_merge($array, $this->getAdConfig());
        return $array;
    }

    public function configs(Request $request)
    {
        return json_decode(\App\Config::query()->select(['name', 'value'])->get());
    }

    public function getAdConfig()
    {
        $adData = [];
        foreach (AdConfig::all() as $adconfig) {
            $adData[$adconfig->name] = $adconfig->value;
        }

        //开屏混合 ----------------------------
        $splash_prodiver = $adData['splash_provider'] ?? null;
        if ('混合' == $splash_prodiver) {
            if (rand(1, 100) % 3 == 0) {
                $splash_prodiver = '腾讯';
            } else if (rand(1, 100) % 3 == 1) {
                $splash_prodiver = '百度';
            } else {
                $splash_prodiver = '头条';
            }
        }

        //信息流混合 ----------------------------
        $feed_provider = $adData['feed_provider'] ?? null;
        if ('混合' == $feed_provider) {
            if (rand(1, 100) % 3 == 0) {
                $feed_provider = '腾讯';
            } else if (rand(1, 100) % 3 == 1) {
                $feed_provider = '百度';
            } else {
                $feed_provider = '头条';
            }
        }

        //激励视频混合 ----------------------------
        $reward_video_provider = $adData['reward_video_provider'] ?? null;
        if ('混合' == $reward_video_provider) {
            if ($user = getUser(false)) {
                //统计激励次数，并强制每次轮换平台
                $counter = $user->rewardCounter;
                if ("头条" == $counter->last_provider) {
                    $reward_video_provider  = '腾讯';
                    $counter->count_tencent = $counter->count_tencent + 1;
                    $counter->last_provider = "腾讯";
                } else {
                    $reward_video_provider  = '头条';
                    $counter->count_toutiao = $counter->count_toutiao + 1;
                    $counter->last_provider = "头条";
                }
                $counter->count = $counter->count + 1;
                $counter->save();
            } else {
                //没用户信息时，简单随机
                if (rand(1, 100) % 2 == 0) {
                    $reward_video_provider = '腾讯';
                } else {
                    $reward_video_provider = '头条';
                }
            }
        }

        return $adData;
    }

    //api/ad-config 返回广告的configs
    public function adConfig(Request $request)
    {
        return $this->getAdConfig();
    }

    public function version(Request $request)
    {
        $package  = $request->input('package');
        $cacheKey = "app_android_version_$package";
        $result   = Cache::get($cacheKey);
        if ($result) {
            return $result;
        }
        // 缓存10分钟
        return Cache::remember($cacheKey, 10 * 60, function () use ($package) {
            $builder = Version::where('os', 'Android')->orderByDesc('id');

            // if (!empty($package) && config('app.name') != 'juhaokan') {
            $builder = $builder->where('package', $package);
            // }

            if (is_prod_env()) {
                $builder = $builder->where('type', 1);
            }

            $version = $builder->take(1)->get();
            $array   = $version->toArray();
            return array_map(static function ($item) {
                return array(
                    'version'     => $item['name'],
                    'apk'         => $item['url'],
                    'is_force'    => $item['is_force'],
                    'description' => $item['description'],
                    'size'        => formatSizeUnits($item['size']),
                    'package'     => $item['package'],
                );
            }, $array);
        });
    }

    //ios 指令更新检查
    public function rcutsVersionCheck(Request $request)
    {
        $builder = Version::where('os', 'RCUT')->orderByDesc('id');
        $package = $request->input('package');
        if (!empty($package)) {
            $builder = $builder->where('package', $package);
        }
        return $builder->first();
    }
}