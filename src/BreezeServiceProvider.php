<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Console\InstallCommand;
use Illuminate\Support\ServiceProvider;

class BreezeServiceProvider extends ServiceProvider
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

        // 这一段会重写掉整个sentry的配置
        $this->rewriteSentryDsn();

        // Register Commands
        $this->commands([
            InstallCommand::class,
        ]);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //合并配置
        // if (!app()->configurationIsCached())
        {
            $this->mergeConfigFrom(__DIR__ . '/../config/view.php', 'view');
        }

    }

    protected function rewriteSentryDsn()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/site-sentry.php',
            'site-sentry'
        );
        $sentryDsn = config('site-sentry.' . config('app.name') . '.dsn');
        if (!empty($sentryDsn)) {
            config(['sentry.dsn' => $sentryDsn]);
        }
    }
}
