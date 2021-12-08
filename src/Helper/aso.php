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

function aso_value($name)
{
    if ($asos = app('asos')) {
        foreach ($asos as $aso) {
            //默认支持app群多个子域名的下载页设置
            $domain = get_sub_domain();
            if ($aso->domain == $domain) {
                if ($aso->name == $name) {
                    return $aso->value;
                }
            }
        }
    }
    return Aso::getValue($name);
}

function getDownloadUrl()
{
    if (Agent::isPhone() && Agent::isSafari()) {
        return getIpaUrl();
    }
    return getApkUrl();
}

function getApkUrl()
{
    $apkUrl = aso_value('安卓地址');
    if (blank($apkUrl)) {
        return null;
    }
    return $apkUrl;
}

function getIpaUrl()
{
    $url = aso_value('苹果地址');
    if (blank($url)) {
        return null;
    }
    return $url;
}

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

/**
 * 是否处于备案状态
 */
function isRecording()
{
    if (class_exists("App\\AppConfig", true)) {
        $config = app('app.config.beian');
        if ($config && $config->state === \App\AppConfig::STATUS_ON) {
            return true;
        }
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
 * banner场景的带文字的logo
 */
function text_logo()
{
    //域名定制banner
    $banner_path = '/banner/' . get_app_name() . '.png';
    if (file_exists(public_path($banner_path))) {
        return url($banner_path);
    }
    //默认banner
    $banner_path = '/banner/default.png';
    if (file_exists(public_path($banner_path))) {
        return url($banner_path);
    }
    //复用text_logo
    return str_replace('.small.', '.text.', small_logo());
}

/**
 * 小尺寸logo,大部分场景得到logo,尺寸60*60
 */
function small_logo()
{
    // 优先返回CDN地址
    if(!blank(config('breeze.logo_path_pattern',null))){
        return sprintf(config('breeze.logo_path_pattern'),get_app_name().'.com.small');
    }
    //APP群
    $logo_path = '/logo/' . get_app_name() . '.small.png';
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
        @file_put_contents($qrcode_full_path, $qrcode->generate(download_url()));
    } catch (Exception $ex) {}
    return url($qrcode_path);
}

/**
 * 当前网站的app的下载页URL
 */
function download_url()
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
        $apkUrl = aso_value('安卓地址');

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
