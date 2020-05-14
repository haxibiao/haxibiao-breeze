<?php
namespace Haxifang\Users;

use GuzzleHttp\Client;
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
        $this->publishes([
            __DIR__ . '/config/alipay_sns.php' => config_path('alipay_sns.php'),
            __DIR__ . '/config/wechat_sns.php' => config_path('wechat_sns.php'),
        ]);
        $this->app->singleton('client', function ($app) {
            return new Client();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
