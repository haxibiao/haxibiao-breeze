<?php

namespace Haxibiao\Breeze\Http\Controllers;

use EasyWeChat\Foundation\Application;
use Illuminate\Support\Facades\Log;

class WechatController extends Controller
{
    public function serve()
    {
        Log::info('wechat request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function ($message) {
            return "欢迎关注 懂点医";
        });

        Log::info('wechat return response.');

        return $wechat->server->serve();
    }

    public function demo(Application $wechat)
    {
        // $wechat 则为容器中 EasyWeChat\Foundation\Application 的实例
    }

}
