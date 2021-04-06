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

if (!function_exists('seo_small_logo')) {
    function seo_small_logo()
    {
        //logo主要针对请求域名变化
        return small_logo();
    }
}

if (!function_exists('seo_site_name')) {
    function seo_site_name()
    {
        //1尊重cms配置站群名称
        if ($site = cms_get_site()) {
            return $site->name;
        }

        //2尊重内部站群名字
        $sites_name_map = array_merge(neihan_sites_domains(), neihan_beian_domains());
        if ($name = $sites_name_map[get_domain()] ?? null) {
            return $name;
        }

        //3最后尊重env name_cn
        return env('APP_NAME_CN') ?? '内涵电影';
    }
}

if (!function_exists('matomo_site_id')) {
    function matomo_site_id()
    {
        if (request() && $url = request()->getUri()) {
            $sites = [
                "neihanxinwen.com"     => "",
                "neihanxiaoshipin.com" => "",
                "neihanduanshipin.com" => "",

                "xiamaoshipin.com"     => "32",
                "diudie.com"           => "29",
                "caohan.com"           => "30",

                "jingdianmeiju.com"    => "2",
                "jingdianriju.com"     => "3",
                "jingdianhanju.com"    => "4",
                "jingdiangangju.com"   => "5",
                "jingdianyueyu.com"    => "6",

                "huaijiumeiju.com"     => "7",
                "huaijiuriju.com"      => "8",
                "huaijiuhanju.com"     => "9",
                "huaijiugangju.com"    => "10",
                "huaijiuyueyu.com"     => "11",

                "fengkuangmeiju.com"   => "12",
                "fengkuangriju.com"    => "14",
                "fengkuanghanju.com"   => "13",
                "fengkuanggangju.com"  => "15",

                "zaixianmeiju.com"     => "16",
                "zaixianriju.com"      => "18",
                "zaixianhanju.com"     => "17",
                "zaixiangangju.com"    => "19",

                "neihandianying.com"   => "1",
                "neihanmeiju.com"      => "24",
                "neihanriju.com"       => "25",
                "neihanhanju.com"      => "26",
                "neihangangju.com"     => "27",

                "aishanghanju.com"     => "20",
                "aishangriju.com"      => "21",
                "aishanggangju.com"    => "22",
                "aishangyueyu.com"     => "23",

                "laoyueyu.com"         => "28",
                'dianmoge.com'         => '31',
            ];

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
        return in_array(get_domain(), array_keys(neihan_beian_domains()));
    }
}

/**
 * 所有内涵站群备案域名
 */
if (!function_exists('neihan_beian_domains')) {
    function neihan_beian_domains()
    {
        return [
            "diudie.com"       => "丢碟图解",
            "shengkangtang.cn" => "圣康堂",
            "xinfashun.cn"     => "新发顺",
            "jingshiyang.cn"   => "静师杨",
            "jinlaikaisuo.cn"  => "进来开锁",
            "hushentouzi.cn"   => "沪深投资",
        ];
    }
}

/**
 * 所有内涵站群未备案域名
 */
if (!function_exists('neihan_sites_domains')) {
    function neihan_sites_domains()
    {
        return [
            "caohan.com"           => "曹汉视频",

            "jingdianmeiju.com"    => "经典美剧",
            "jingdianriju.com"     => "经典日剧",
            "jingdianhanju.com"    => "经典韩剧",
            "jingdiangangju.com"   => "经典港剧",
            "jingdianyueyu.com"    => "经典粤语",

            "huaijiumeiju.com"     => "怀旧美剧",
            "huaijiuriju.com"      => "怀旧日剧",
            "huaijiuhanju.com"     => "怀旧韩剧",
            "huaijiugangju.com"    => "怀旧港剧",
            "huaijiuyueyu.com"     => "怀旧粤语",

            "fengkuangmeiju.com"   => "疯狂美剧",
            "fengkuangriju.com"    => "疯狂日剧",
            "fengkuanghanju.com"   => "疯狂韩剧",
            "fengkuanggangju.com"  => "疯狂港剧",

            "zaixianmeiju.com"     => "在线美剧",
            "zaixianriju.com"      => "在线日剧",
            "zaixianhanju.com"     => "在线韩剧",
            "zaixiangangju.com"    => "在线港剧",

            "neihanmeiju.com"      => "内涵美剧",
            "neihanriju.com"       => "内涵日剧",
            "neihanhanju.com"      => "内涵韩剧",
            "neihangangju.com"     => "内涵港剧",

            "neihanxinwen.com"     => "内涵新闻",
            "neihanxiaoshipin.com" => "内涵小视频",
            "neihanduanshipin.com" => "内涵短视频",
            "neihanyouxi.com"      => "内涵游戏",

            "aishanghanju.com"     => "爱上韩剧",
            "aishangriju.com"      => "爱上日剧",
            "aishanggangju.com"    => "爱上港剧",
            "aishangyueyu.com"     => "爱上粤语",
            "laoyueyu.com"         => "老粤语",

            "nageshipin.com"       => "那个视频",

            // "xingqilianren.com"    => "星期恋人",
            // "didipeipei.com"       => "滴滴陪陪",
            // "pipipei.com"          => "皮皮陪",
            // "tiantiandati.cn"      => "天天答题",
        ];
    }
}

if (!function_exists('neihan_ga_measure_id')) {
    function neihan_ga_measure_id()
    {
        if (request() && $url = request()->getUri()) {
            $sites = [
                "neihanxinwen.com"     => "G-02NVWTLXQQ",
                "neihanxiaoshipin.com" => "G-L9K2KE4FMN",
                "neihanduanshipin.com" => "G-ZPBJTK4SWZ",

                "jingdianmeiju.com"    => "G-24RS5FX84Z",
                "jingdianriju.com"     => "G-VQ9ZZDZ71E",
                "jingdianhanju.com"    => "G-D9T3L30JHX",
                "jingdiangangju.com"   => "G-WLFYB2J9DV",
                "jingdianyueyu.com"    => "G-CW14RTZJD8",

                "huaijiumeiju.com"     => "G-EHSQV96WDS",
                "huaijiuriju.com"      => "G-H03WCVM8MM",
                "huaijiuhanju.com"     => "G-SKF9JT2YHQ",
                "huaijiugangju.com"    => "G-PND6NHRDGD",
                "huaijiuyueyu.com"     => "G-NVEGSN3QDS",

                "fengkuangmeiju.com"   => "G-CRK4B4W5R4",
                "fengkuangriju.com"    => "G-RBMTDGYWJB",
                "fengkuanghanju.com"   => "G-ZD5VS57QS0",
                "fengkuanggangju.com"  => "G-K0PDPPNKPQ",

                "zaixianmeiju.com"     => "G-0G65PG9RET",
                "zaixianriju.com"      => "G-V0P1GMLNP7",
                "zaixianhanju.com"     => "G-Y175YH6FQX",
                "zaixiangangju.com"    => "G-Y9X8DRH6JP",

                "neihandianying.com"   => "G-W72CHJT74V",
                "neihanmeiju.com"      => "G-6F8W0505E1",
                "neihanriju.com"       => "G-QRG8C7FJ6P",
                "neihanhanju.com"      => "G-NYBSGC3Z53",
                "neihangangju.com"     => "G-CC0CD82NYG",

                "aishanghanju.com"     => "G-C3QPSPFRLY",
                "aishangriju.com"      => "G-WKFL8YBP7S",
                "aishanggangju.com"    => "G-F07SGXP0CV",
                "aishangyueyu.com"     => "G-68LTE5T2LQ",

                "laoyueyu.com"         => "G-NTLN63MYR6",
                'dianmoge.com'         => 'G-92H6K7HTKT',
                'xiamaoshipin.com'     => 'G-3KK2NYZYLF',
                'cheliange.cn'         => 'G-H6DLJJWY2Y',
                'dongdaima.com'        => 'G-PKQL2HN9BM',
                'ainicheng.com'        => 'G-PX3RDWLYYY',
            ];

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
            $sites = [
                "neihanxinwen.com"     => "",
                "neihanxiaoshipin.com" => "",
                "neihanduanshipin.com" => "",

                "jingdianmeiju.com"    => "",
                "jingdianriju.com"     => "",
                "jingdianhanju.com"    => "",
                "jingdiangangju.com"   => "",
                "jingdianyueyu.com"    => "",

                "huaijiumeiju.com"     => "",
                "huaijiuriju.com"      => "",
                "huaijiuhanju.com"     => "",
                "huaijiugangju.com"    => "",
                "huaijiuyueyu.com"     => "",

                "fengkuangmeiju.com"   => "",
                "fengkuangriju.com"    => "",
                "fengkuanghanju.com"   => "",
                "fengkuanggangju.com"  => "",

                "zaixianmeiju.com"     => "500733778",
                "zaixianriju.com"      => "",
                "zaixianhanju.com"     => "500733766",
                "zaixiangangju.com"    => "",

                "neihandianying.com"   => "500733779",
                "neihanmeiju.com"      => "",
                "neihanriju.com"       => "",
                "neihanhanju.com"      => "",
                "neihangangju.com"     => "",

                "aishanghanju.com"     => "",
                "aishangriju.com"      => "",
                "aishanggangju.com"    => "",
                "aishangyueyu.com"     => "",

                "laoyueyu.com"         => "",
                'dianmoge.com'         => '500734959',
                'xiamaoshipin.com'     => '500735048',

            ];

            $host = parse_url($url)['host'];
            $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
            // 默认内函电影的
            return $sites[$host] ?? '500733779';
        }
    }
}

if (!function_exists('siteName')) {
    function siteName()
    {
        if (request() && $url = request()->getUri()) {
            $sites = [
                "neihanxinwen.com"     => "内涵新闻",
                "neihanxiaoshipin.com" => "内涵小视频",
                "neihanduanshipin.com" => "内涵短视频",

                "jingdianmeiju.com"    => "经典美剧",
                "jingdianriju.com"     => "经典日剧",
                "jingdianhanju.com"    => "经典韩剧",
                "jingdiangangju.com"   => "经典港剧",
                "jingdianyueyu.com"    => "经典粤语",

                "huaijiumeiju.com"     => "怀旧美剧",
                "huaijiuriju.com"      => "怀旧日剧",
                "huaijiuhanju.com"     => "怀旧韩剧",
                "huaijiugangju.com"    => "怀旧港剧",
                "huaijiuyueyu.com"     => "怀旧粤语",

                "fengkuangmeiju.com"   => "疯狂美剧",
                "fengkuangriju.com"    => "疯狂日剧",
                "fengkuanghanju.com"   => "疯狂韩剧",
                "fengkuanggangju.com"  => "疯狂港剧",

                "zaixianmeiju.com"     => "在线美剧",
                "zaixianriju.com"      => "在线日剧",
                "zaixianhanju.com"     => "在线韩剧",
                "zaixiangangju.com"    => "在线港剧",

                "neihandianying.com"   => "内涵电影",
                "neihanmeiju.com"      => "内涵美剧",
                "neihanriju.com"       => "内涵日剧",
                "neihanhanju.com"      => "内涵韩剧",
                "neihangangju.com"     => "内涵港剧",

                "aishanghanju.com"     => "爱上韩剧",
                "aishangriju.com"      => "爱上日剧",
                "aishanggangju.com"    => "爱上港剧",
                "aishangyueyu.com"     => "爱上粤语",

                "laoyueyu.com"         => "老粤语",

                "caohan.com"           => "曹汉视频",
                "dianmoge.com"         => "点墨阁",
                "xiamaoshipin.com"     => "瞎猫视频",

                'cheliange.cn'         => '彻恋阁',
                'renzaichazai.cn'      => '人在茶在',
                'shengkangtang.cn'     => '盛康泰',
                'xinfashun.cn'         => '心法书',
                'xinyuezhong.cn'       => '新月中',
                'jingshiyang.cn'       => '景士阳',
                'jinlaikaisuo.cn'      => '进来开锁',
                'shiyongceping.cn'     => '使用测评',
                'hushentouzi.cn'       => '沪深投资',
                'nageshipin.com'       => '那个视频',
            ];
            $host = parse_url($url)['host'];
            $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
            return $sites[$host] ?? '内涵电影';
        }
    }
}

if (!function_exists('friend_links')) {
    function friend_links()
    {
        $zaixianSites = [
            [
                'name' => '在线美剧',
                'url'  => 'https://zaixianmeiju.com',
            ],
            [
                'name' => '在线韩剧',
                'url'  => 'https://zaixianhanju.com',
            ],
            [
                'name' => '在线日剧',
                'url'  => 'https://zaixianriju.com',
            ],
            [
                'name' => '在线港剧',
                'url'  => 'https://zaixiangangju.com',
            ],
        ];
        $neihanjuzhen = [
            [
                'name' => '内涵电影',
                'url'  => 'https://neihandianying.com',
            ],
            [
                'name' => '在线美剧',
                'url'  => 'https://zaixianmeiju.com',
            ],
            [
                'name' => '在线韩剧',
                'url'  => 'https://zaixianhanju.com',
            ],
            [
                'name' => '在线日剧',
                'url'  => 'https://zaixianriju.com',
            ],
            [
                'name' => '在线港剧',
                'url'  => 'https://zaixiangangju.com',
            ],
        ];
        if (request() && $url = request()->getUri()) {
            $sites = [
                "neihandianying.com" => $zaixianSites,
                "dianmoge.com"       => $neihanjuzhen,
                "xiamaoshipin.com"   => $neihanjuzhen,
            ];

            $host = parse_url($url)['host'];
            $host = str_replace(['l.', 'www.', 'cdn.'], '', $host);
            // 默认内函电影的
            return $sites[$host] ?? $zaixianSites;
        }
    }
}

if (!function_exists('getDomain')) {
    function getDomain()
    {
        $urlInfo = parse_url(request()->getUri());
        $arr     = explode(".", $urlInfo['host']);
        if (count($arr) == 3) {
            $host = $arr[1];
        } else {
            $host = $arr[0];
        }
        return $host;
    }
}

if (!function_exists('sitemap')) {
    function sitemap()
    {
        $host = getDomain();
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
            $sites = [
                "neihanxinwen.com"     => "",
                "neihanxiaoshipin.com" => "",
                "neihanduanshipin.com" => "",

                "xiamaoshipin.com"     => "96467d054f941be696ddb37f213d246f",
                "diudie.com"           => "29",
                "caohan.com"           => "30",

                "jingdianmeiju.com"    => "2",
                "jingdianriju.com"     => "3",
                "jingdianhanju.com"    => "4",
                "jingdiangangju.com"   => "5",
                "jingdianyueyu.com"    => "6",

                "huaijiumeiju.com"     => "7",
                "huaijiuriju.com"      => "8",
                "huaijiuhanju.com"     => "9",
                "huaijiugangju.com"    => "10",
                "huaijiuyueyu.com"     => "11",

                "fengkuangmeiju.com"   => "12",
                "fengkuangriju.com"    => "14",
                "fengkuanghanju.com"   => "13",
                "fengkuanggangju.com"  => "15",

                "zaixianmeiju.com"     => "16",
                "zaixianriju.com"      => "18",
                "zaixianhanju.com"     => "17",
                "zaixiangangju.com"    => "19",

                "neihandianying.com"   => "6e97a3278bb1c87902e1db0ccaf413bd",
                "neihanmeiju.com"      => "24",
                "neihanriju.com"       => "25",
                "neihanhanju.com"      => "26",
                "neihangangju.com"     => "27",
                "aishanghanju.com"     => "20",
                "aishangriju.com"      => "21",
                "aishanggangju.com"    => "22",
                "aishangyueyu.com"     => "23",

                "laoyueyu.com"         => "28",
                "cheliange.cn"         => "38dcbe82fe7f03235d6cde7a1c6b4c19",
                'dianmoge.com'         => '3bd05fbfcaaf8dad90231ea3de958d76',
            ];

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
