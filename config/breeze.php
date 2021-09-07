<?php

return [
    'migration_autoload'       => true,
    'routes_autoload'          => true,
    'enable_mail_notification' => env('ENABLE_MAIL_NOTIFICATION', false),
    'enable_onesignal'         => false,
    'enable_pushalert'         => false,
    'enable_pushy'             => false,
];
