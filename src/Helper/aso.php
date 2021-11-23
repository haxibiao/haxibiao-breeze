<?php

use Haxibiao\Breeze\AppConfig;
use Haxibiao\Breeze\Aso;
use Haxibiao\Breeze\Config;
use Jenssegers\Agent\Facades\Agent;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

function getDownloadUrl()
{
    if (Agent::isAndroidOS()) {
        return getApkUrl();

    }
    return getIpaUrl();
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

function touch_logo()
{
    return str_replace('.small.', '.touch.', small_logo());
}

function web_logo()
{
    return str_replace('.small.', '.web.', small_logo());
}

/**
 * 其实就是banner的场景
 */
function text_logo()
{
    //域名定制banner
    $banner_path = '/img/banner/' . get_sub_domain() . '.png';
    if (file_exists(public_path($banner_path))) {
        return url($banner_path);
    }
    //默认banner
    $banner_path = '/img/banner/banner.png';
    if (file_exists(public_path($banner_path))) {
        return url($banner_path);
    }
    //兼容以前复用small logo
    return str_replace('.small.', '.text.', small_logo());
}

/**
 * 小尺寸logo,大部分场景得到logo,尺寸60*60
 */

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

/**
 * 去下载APP的qrcode图片地址(自动生成)
 */

function app_qrcode_url()
{
    $domain           = app_domain();
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
        $qrcode->merge(public_path($small_logo_path), .2, true);
    }
    try {
        @file_put_contents($qrcode_full_path, $qrcode->generate(app_download_url()));
    } catch (Exception $ex) {}
    return url($qrcode_path);
}

/**
 * 当前网站的app的下载页URL
 */
function app_download_url()
{
    return "https://" . app_domain() . "/app";
}

/**
 * 获取当前app的入口域名
 */
function app_domain()
{
    // 如果是二级域名，尊重二级域名
    $app_domain = get_sub_domain();

    // 已配置二维码域名入口的
    if ($scan_domain = config('cms.qrcode_traffic.scan_domain')) {
        $app_domain = $scan_domain;
    }
    // 允许站群多SEO域名合并为一个扫码入口转化app的域名
    foreach (config('cms.qrcode_traffic.scan_domains', []) as $scan_domain => $scan_config) {
        foreach ($scan_config['seo_domains'] ?? [] as $seo_domain) {
            if ($seo_domain == get_domain()) {
                $app_domain = $scan_domain;
            }
        }
    }

    return $app_domain;
}

/**
 * 返回的是base64 data 内容是apk的cdn URL
 * @deprecated 建议用app_qrcode_url返回图片地址
 */

function qrcode_url()
{
    if (class_exists("App\\Aso", true)) {
        $apkUrl = aso_value('下载页', '安卓地址');

        if (class_exists("SimpleSoftwareIO\QrCode\Facades\QrCode")) {
            $qrcode = QrCode::format('png')->size(250)->encoding('UTF-8');

            //二维码中心带上logo
            $logo = parse_url(small_logo(), PHP_URL_PATH);
            if (file_exists(public_path($logo))) {
                $qrcode->merge(public_path($logo), .2, true);
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
