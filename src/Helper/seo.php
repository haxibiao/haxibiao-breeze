<?php

use Haxibiao\Breeze\Seo;
use Haxibiao\Breeze\SEOFriendlyUrl;

function push_url_baidu($urls)
{
    $urls    = array($urls);
    $api     = 'http://data.zz.baidu.com/urls?site=https://neihandianying.com&token=0Oay0vFOYGDk9x34';
    $ch      = curl_init();
    $options = array(
        CURLOPT_URL            => $api,
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => implode("\n", $urls),
        CURLOPT_HTTPHEADER     => array('Content-Type: text/plain'),
    );
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    echo $result;
}

function seo_friendly_urls()
{
    return SEOFriendlyUrl::generate();
}

//通过app_name获取域名
function get_app_domain($app_name)
{
    $app_domain = env('APP_DOMAIN');
    $sites      = config('cms.sites') ?? [];
    foreach ($sites as $domain => $names) {
        if ($appname = array_get($names, 'app_name')) {
            if ($appname == $app_name) {
                $app_domain = $domain;
            }
        }
    }
    $apps = config('cms.apps') ?? [];
    foreach ($apps as $domain => $names) {
        if ($appname = array_get($names, 'app_name')) {
            if ($appname == $app_name) {
                $app_domain = $domain;
            }
        }
    }
    return $app_domain;
}

/**
 * 获取当前的app_name
 */
function get_app_name($domain = null)
{
    $domain   = $domain ?? get_sub_domain();
    $app_name = get_domain_key(); //域名中提取的app_name(逻辑优先尊重域名名称，再env)

    //尊重cms站群配置覆盖
    $names = config('cms.sites') ?? [];
    if ($sites_app_name = array_get(array_get($names, $domain), 'app_name')) {
        $app_name = $sites_app_name;
    }

    //尊重app群配置覆盖
    $names = config('cms.apps') ?? [];
    if ($apps_app_name = array_get(array_get($names, $domain), 'app_name')) {
        $app_name = $apps_app_name;
    }
    return $app_name;
}

//获取app_name_cn
function get_app_name_cn($domain = null)
{
    $domain      = $domain ?? get_sub_domain();
    $names       = config('cms.sites') ?? [];
    $app_name_cn = array_get(array_get($names, $domain), 'app_name_cn');
    if (blank($app_name_cn)) {
        $names       = config('cms.apps') ?? [];
        $app_name_cn = array_get(array_get($names, $domain), 'app_name_cn');
    }
    return $app_name_cn ?? env('APP_NAME_CN');
}

//网站显示的站名/APP后端名称
function seo_site_name()
{
    //1.app群cms config
    $apps = config('cms.apps') ?? [];
    if ($name = array_get(array_get($apps, get_sub_domain()), 'app_name_cn')) {
        return $name;
    }
    //2.站群cms config
    $sites = config('cms.sites') ?? [];
    if ($name = array_get(array_get($sites, get_domain()), 'app_name_cn')) {
        return $name;
    }
    //3.cms数据库sites表
    if ($site = cms_get_site()) {
        return $site->name;
    }
    return env('APP_NAME_CN');
}

function matomo_url()
{
    return config('matomo.matomo_url');
}

/**
 * 获取当前站点的matomo_id（兼容cms站群app群）
 */
function matomo_id()
{
    $domain   = get_sub_domain();
    $matomoId = config('cms.matomo_ids', [])[$domain] ?? null;
    if (!blank($matomoId)) {
        return $matomoId;
    }
    return config('matomo.web_id', 1);
}

/**
 * 是否备案站群
 */
function is_beian_sites()
{
    return false;
}

function neihan_ga_measure_id()
{
    if (request() && $url = request()->getUri()) {
        $sites = config('cms.google_tj_ids') ?? [];

        $host = parse_url($url)['host'];
        $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
        // 默认内函电影的
        return $sites[$host] ?? 'G-W72CHJT74V';
    }
}

function neihan_tencent_app_id()
{
    if (request() && $url = request()->getUri()) {
        $sites = config('cms.tencent_tj_ids') ?? [];

        $host = parse_url($url)['host'];
        $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
        // 默认
        return $sites[$host] ?? '500733779';
    }
}

function friend_links()
{
    return config('cms.friend_links') ?? [];
}

function sitemap()
{
    $host = get_domain();
    $path = "sitemap/" . $host;
    return [
        'Google地图' => "/{$path}/google.xml",
        '百度地图'     => "/{$path}/baidu.xml",
        '搜狗地图'     => "/{$path}/sougou.xml",
        '360地图'    => "/{$path}/360.xml",
        '神马地图'     => "/{$path}/shenma.xml",
    ];
}

// 百度统计id
function baidu_id()
{
    if (request() && $url = request()->getUri()) {
        $sites = config('cms.baidu_tj_ids');

        $host = parse_url($url)['host'];
        $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
        // 默认内函电影的
        return $sites[$host] ?? '1';
    }
}

function cnzz_id()
{
    if (request() && $url = request()->getUri()) {
        $sites = [
            "neihandianying.com" => "1279817045",
            "xiamaoshipin.com"   => "1279817267",
        ];

        $host = parse_url($url)['host'];
        $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
        // 默认内函电影的
        return $sites[$host] ?? '1';
    }
}

function seo_value($group, $name)
{
    if ($seos = app('seos')) {
        foreach ($seos as $seo) {
            if ($seo->group == $group) {
                if ($seo->name == $name) {
                    return $seo->value;
                }
            }
        }
    }
    return Seo::getValue($group, $name);
}

/**
 * @deprecated 统计合并到 cms_seo_js 里即可
 */

function get_seo_tj()
{
    //站群模式
    if (config('cms.enable_sites')) {
        if ($site = cms_get_site()) {
            return $site->footer_js;
        }
    }
    return Seo::getValue('统计', 'matomo');
}

function get_seo_title()
{
    //站群模式
    if (config('cms.enable_sites')) {
        if ($site = cms_get_site()) {
            if ($site->title) {
                return $site->title;
            }
        }
    }
    return seo_value('TDK', 'title');
}

function get_seo_keywords()
{
    //站群模式
    if (config('cms.enable_sites')) {
        if ($site = cms_get_site()) {
            if ($site->keywords) {
                return $site->keywords;
            }
        }
    }
    return seo_value('TDK', 'keywords');
}

function get_seo_description()
{
    //站群模式
    if (config('cms.enable_sites')) {
        if ($site = cms_get_site()) {
            if ($site->description) {
                return $site->description;
            }
        }
    }
    return seo_value('TDK', 'description');
}

function get_seo_meta($group_name = "站长")
{
    //配合调试模式才允许验证站长
    if (env('APP_DEBUG')) {
        if (config('cms.enable_sites')) {
            //站群模式

            if ($site = cms_get_site()) {
                if ($site->verify_meta) {
                    return $site->verify_meta;
                }
            }
        }
        return seo_value($group_name, 'meta');
    }
}

function get_seo_push($seo_site_name = null, $group_name = "百度")
{
    if ($seo_site_name) {
        $js = Haxibiao\Breeze\Seo::query()
            ->where('group', $group_name)
            ->where('name', $seo_site_name . "_push")->first();
        if ($js) {
            return $js->value;
        }
    } else {
        seo_value('百度', 'push');
    }
}
