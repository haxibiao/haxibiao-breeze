<?php

return [
    'migration_autoload'       => true,
    'routes_autoload'          => true,
    'enable_mail_notification' => env('ENABLE_MAIL_NOTIFICATION', false),
    'logo_path_pattern'        => env('BREEZE_LOGO_PATH_PATTERN',null),
];
