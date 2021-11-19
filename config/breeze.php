<?php

return [
    'migration_autoload'       => true,
    'routes_autoload'          => true,
    'enable_mail_notification' => env('ENABLE_MAIL_NOTIFICATION', false),
    'enable_pwa'               => false, //FIXME 下一步自动智能判断腾讯流量内自动切换PWA
];
