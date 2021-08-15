<?php

use Haxibiao\Breeze\Seo;
use Haxibiao\Breeze\SEOFriendlyUrl;

if (!function_exists('push_url_baidu')) {
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
}

if (!function_exists('seo_friendly_urls')) {
    function seo_friendly_urls()
    {
        return SEOFriendlyUrl::generate();
    }
}

if (!function_exists('seo_site_name')) {
    function seo_site_name()
    {
        $site_name = env('APP_NAME_CN') ?? '内涵电影';

        //1.尊重seo站群配置
        $sites = config('seo.sites') ?? [];
        // - APP网站后台用二级域名
        if ($name = array_get($sites, get_sub_domain())) {
            return $name;
        }
        if ($name = array_get($sites, get_domain())) {
            return $name;
        }

        //2.尊重cms配置站群数据库表
        if ($site = cms_get_site()) {
            return $site->name;
        }

        //3.最后默认用env
        return $site_name;
    }
}

if (!function_exists('matomo_site_id')) {
    function matomo_site_id()
    {
        if (request() && $url = request()->getUri()) {
            $sites = config('seo.matomo_ids');

            $host = parse_url($url)['host'];
            $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
            // 默认内函电影的
            return $sites[$host] ?? '1';
        }
    }
}

/**
 * 是否备案站群
 *
 * @return boolean
 */
if (!function_exists('is_beian_sites')) {
    function is_beian_sites()
    {
        return false;
    }
}

if (!function_exists('neihan_ga_measure_id')) {
    function neihan_ga_measure_id()
    {
        if (request() && $url = request()->getUri()) {
            $sites = config('seo.google_tj_ids');

            $host = parse_url($url)['host'];
            $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
            // 默认内函电影的
            return $sites[$host] ?? 'G-W72CHJT74V';
        }
    }
}

if (!function_exists('neihan_tencent_app_id')) {
    function neihan_tencent_app_id()
    {
        if (request() && $url = request()->getUri()) {
            $sites = config('seo.tencent_tj_ids');

            $host = parse_url($url)['host'];
            $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
            // 默认
            return $sites[$host] ?? '500733779';
        }
    }
}

if (!function_exists('friend_links')) {
    function friend_links()
    {
        return config('seo.friend_links');
    }
}

if (!function_exists('sitemap')) {
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
}

// 百度统计id
if (!function_exists('baidu_id')) {
    function baidu_id()
    {
        if (request() && $url = request()->getUri()) {
            $sites = config('seo.baidu_tj_ids');

            $host = parse_url($url)['host'];
            $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
            // 默认内函电影的
            return $sites[$host] ?? '1';
        }
    }
}

if (!function_exists('cnzz_id')) {
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
}

if (!function_exists('seo_value')) {
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
}

/**
 * @deprecated 统计合并到 cms_seo_js 里即可
 */
if (!function_exists('get_seo_tj')) {
    function get_seo_tj()
    {
        //站群模式
        if (config('cms.multi_domains')) {
            if ($site = cms_get_site()) {
                return $site->footer_js;
            }
        }
        return Seo::getValue('统计', 'matomo');
    }
}

/*****************************
 * *****答赚web网页兼容*********
 * ***************************
 */
if (!function_exists('get_seo_title')) {
    function get_seo_title()
    {
        //站群模式
        if (config('cms.multi_domains')) {
            if ($site = cms_get_site()) {
                if ($site->title) {
                    return $site->title;
                }
            }
        }
        return seo_value('TKD', 'title');
    }
}

if (!function_exists('get_seo_keywords')) {
    function get_seo_keywords()
    {
        //站群模式
        if (config('cms.multi_domains')) {
            if ($site = cms_get_site()) {
                if ($site->keywords) {
                    return $site->keywords;
                }
            }
        }
        return seo_value('TKD', 'keywords');
    }
}

if (!function_exists('get_seo_description')) {
    function get_seo_description()
    {
        //站群模式
        if (config('cms.multi_domains')) {
            if ($site = cms_get_site()) {
                if ($site->description) {
                    return $site->description;
                }
            }
        }
        return seo_value('TKD', 'description');
    }
}

if (!function_exists('get_seo_meta')) {
    function get_seo_meta($group_name = "站长")
    {
        //配合调试模式才允许验证站长
        if (env('APP_DEBUG')) {
            if (config('cms.multi_domains')) {
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
}

if (!function_exists('get_seo_push')) {
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
}
