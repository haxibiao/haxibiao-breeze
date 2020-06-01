<?php

namespace haxibiao\user;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //注册一些helpers 函数
        $src_path = __DIR__;
        foreach (glob($src_path . '/helpers/*.php') as $filename) {
            require_once $filename;
        }

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
