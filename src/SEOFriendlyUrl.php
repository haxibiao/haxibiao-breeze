<?php

namespace Haxibiao\Breeze;
use Illuminate\Support\Arr;

class SEOFriendlyUrl
{

    public static function generate()
    {
        $domain = get_domain();
        $frienlyUrls = Arr::get(config('seo.frienly_urls'),$domain,false);
        if(!$frienlyUrls){
            return null;
        }
        $html = [];
        $html [] = "<div id=\"link\">";
        $html [] = "<h7>友情链接</h7>";
        foreach ($frienlyUrls as $url){
            $href  = data_get($url,'url');
            $title = data_get($url,'title');
            $html [] = "<a target=\"_blank\" href=\"{$href}\" title=\"{$title}\">{$title}</a>";
        }
        $html [] = "</div>";
        return implode(PHP_EOL, $html);
    }
}
