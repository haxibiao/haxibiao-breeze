<?php

return [
    'enable'            => [
        //数据库升级
        'migration'         => true,
        //路由升级
        'routes'            => true,
        //邮件通知
        'mail_notification' => env('ENABLE_MAIL_NOTIFICATION', false),
        //登录功能
        'login'             => env('BREEZE_DISABLE_LOGIN_PAGE', true),
    ],

    //站点logo的模式串
    'logo_path_pattern' => env('BREEZE_LOGO_PATH_PATTERN', null),
];
