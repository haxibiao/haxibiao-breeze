<?php

return [
    //总开关,可以暂时关闭
    'on'           => env('MATOMO_ON', false),

    //是否借用swoole的proxy实现tcp+bulk发送
    'use_swoole'   => env('MATOMO_USE_SWOOLE', false),
    'proxy_host'   => env('MATOMO_PROXY_HOST', '127.0.0.1'),
    'proxy_port'   => env('MATOMO_PROXY_PORT', 9502),
    'token_auth'   => env('MATOMO_TOKEN_AUTH'),
    'only_track_app' => env('MATOMO_ONLY_TRACK_APP', false),

    //默认事件(含后端)
    'matomo_id'    => env('MATOMO_ID'),
    'matomo_url'   => env('MATOMO_URL'),

    //APP事件
    'app_id'       => env('MATOMO_APP_ID', env('MATOMO_ID')),
    'app_url'      => env('MATOMO_APP_URL', env('MATOMO_URL')),

    //网页事件(含PWA)
    'web_id'       => env('MATOMO_WEB_ID', env('MATOMO_ID')),
    'web_url'      => env('MATOMO_WEB_URL', env('MATOMO_URL')),

    //是否开启管理员账号行为不埋点
    'matomo_user'  => env('MATOMO_USER', false),
];
