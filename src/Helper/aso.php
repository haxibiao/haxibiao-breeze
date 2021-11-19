<?php

use Haxibiao\Breeze\AppConfig;
use Haxibiao\Breeze\Aso;
use Haxibiao\Breeze\Config;
use Jenssegers\Agent\Facades\Agent;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

if (!function_exists('adIsOpened')) {
    function adIsOpened()
    {

        $os     = request()->header('os', 'android');
        $config = AppConfig::where(['group' => 'android', 'name' => 'ad'])->first();
        //如果使用的是版本开关
        if ($config && isset($config->app_version)) {
            $user          = getUser(false);
            $userVersion   = $user && $user->profile->app_version ? $user->profile->app_version : $config->app_version;
            $config->state = $config->isOpen($userVersion) == 'on' ? AppConfig::STATUS_ON : AppConfig::STATUS_OFF;
        }
        if ($config && intval($config->state) === AppConfig::STATUS_OFF) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('aso_value')) {
    function aso_value($group, $name)
    {
        if ($asos = app('asos')) {
            foreach ($asos as $aso) {
                if ($aso->group == $group) {
                    if ($aso->name == $name) {
                        return $aso->value;
                    }
                }
            }
        }
        return Aso::getValue($group, $name);
    }
}

if (!function_exists('getDownloadUrl')) {
    function getDownloadUrl()
    {
        if (Agent::isAndroidOS()) {
            return getApkUrl();

        }
        return getIpaUrl();
    }
}

function getApkUrl()
{
    $apkUrl = aso_value('下载页', '安卓地址');
    if (is_null($apkUrl) || empty($apkUrl)) {
        return null;
    }
    return $apkUrl;
}

function getIpaUrl()
{
    $url = aso_value('下载页', '苹果地址');
    if (is_null($url) || empty($url)) {
        return null;
    }
    return $url;
}

if (!function_exists('douyinOpen')) {
    function douyinOpen()
    {
        $config = Config::where([
            'name' => 'douyin',
        ])->first();
        if ($config && $config->value === Config::CONFIG_OFF) {
            return false;
        } else {
            return true;
        }

    }
}

/*****************************
 * *****答赚web网页兼容*********
 * ***************************
 */
//是否处于备案状态
if (!function_exists('isRecording')) {
    function isRecording()
    {
        if (class_exists("App\\AppConfig", true)) {
            $config = app('app.config.beian');
            // //兼容答赚web默认开启备案检查模式?
            // if ($config === null) {
            //     return true;
            // }
            if ($config && $config->state === \App\AppConfig::STATUS_ON) {
                return true;
            }
        }
        /**
         * 暂时硬编码了，对用户关闭深圳哈希坊下八个站点的视频/电影播放模块
         * https://pm.haxifang.com/browse/HXB-288
         */
        $isRecording = in_array(get_domain(), ['dongdianhai.com', 'dongshengyin.com', 'tongjiuxiu.com', 'dongtaolu.cn', 'dongdezhuan.com', 'dongyundong.com', 'dongwuli.com']);
        if ($isRecording) {
            return true;
        }
        return false;
    }
}

if (!function_exists('touch_logo')) {
    function touch_logo()
    {
        return str_replace('.small.', '.touch.', small_logo());
    }
}

if (!function_exists('web_logo')) {
    function web_logo()
    {
        return str_replace('.small.', '.web.', small_logo());
    }

}

/**
 * 注册登录场景用的文字logo
 */
if (!function_exists('text_logo')) {
    function text_logo()
    {
        return str_replace('.small.', '.text.', small_logo());
    }
}

/**
 * 小尺寸logo,大部分场景得到logo,尺寸60*60
 */
if (!function_exists('small_logo')) {
    function small_logo()
    {
        //APP的
        $logo_path = '/logo/' . get_sub_domain() . '.small.png';
        if (file_exists(public_path($logo_path))) {
            return url($logo_path);
        }
        //站群的
        $logo_path = '/logo/' . get_domain() . '.small.png';
        if (file_exists(public_path($logo_path))) {
            return url($logo_path);
        }
        //breeze默认logo
        return url("/images/logo/default.small.png");
    }
}

/**
 * 去下载APP的qrcode图片地址(自动生成)
 */
if (!function_exists('app_qrcode_url')) {
    function app_qrcode_url()
    {
        $domain = get_sub_domain();
        //二维码域名入口,优先尊重腾讯流量的可用域名
        if ($income_domain = config('cms.tencent_traffic.income_domain')) {
            $domain = $income_domain;
        }

        $qrcode_path      = "/storage/qrcode." . $domain . ".jpg";
        $qrcode_full_path = public_path($qrcode_path);
        //缓存的二维码图片
        if (file_exists($qrcode_full_path)) {
            return url($qrcode_path);
        }

        $qrcode = QrCode::format('png')->size(250)->encoding('UTF-8');

        //二维码中心带上logo
        $small_logo_path = parse_url(small_logo(), PHP_URL_PATH);
        if (file_exists(public_path($small_logo_path))) {
            $qrcode->merge(public_path($small_logo_path), .1, true);
        }
        try {
            //兼容PC扫码场景，先打开app下载页
            $url = "https://" . $domain . "/app";
            @file_put_contents($qrcode_full_path, $qrcode->generate($url));
        } catch (Exception $ex) {}
        return url($qrcode_path);
    }
}

/**
 * 返回的是base64 data 内容是apk的cdn URL
 * @deprecated 建议用app_qrcode_url返回图片地址
 */
if (!function_exists('qrcode_url')) {
    function qrcode_url()
    {
        if (class_exists("App\\Aso", true)) {
            $apkUrl = aso_value('下载页', '安卓地址');

            if (class_exists("SimpleSoftwareIO\QrCode\Facades\QrCode")) {
                $qrcode = QrCode::format('png')->size(250)->encoding('UTF-8');

                //二维码中心带上logo
                $logo = parse_url(small_logo(), PHP_URL_PATH);
                if (file_exists(public_path($logo))) {
                    $qrcode->merge(public_path($logo), .1, true);
                }

                if (!empty($apkUrl)) {
                    try {
                        $qrcode = $qrcode->generate($apkUrl);
                        $data   = base64_encode($qrcode);
                        return $data;
                    } catch (\Throwable$ex) {
                    }
                }
            }
            return null;
        }
    }
}
