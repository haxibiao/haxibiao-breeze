<?php

return [
    'migration_autoload'        => true,
    'routes_autoload'           => true,
    'enable_mail_notification'  => env('ENABLE_MAIL_NOTIFICATION', false),
    /**
     * 站点logo的模式串
     */
    'logo_path_pattern'        => env('BREEZE_LOGO_PATH_PATTERN',null),
    /**
     * 是否关闭PC端的登陆入口
     */
    'disable_login_page'            => env('BREEZE_DISABLE_LOGIN_PAGE',true),
];
