<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Console\InstallCommand;
use Haxibiao\Breeze\Console\PublishCommand;
use Illuminate\Contracts\Foundation\CachesConfiguration;
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
            PublishCommand::class,
        ]);

        //合并view config 配置
        $view_config_path = __DIR__ . '/../config/view.php';
        if (!(app() instanceof CachesConfiguration && app()->configurationIsCached())) {
            $config = app()->make('config');
            //用breeze的view config来覆盖
            $config->set('view', array_merge(
                $config->get('view', []), require $view_config_path
            ));
        }

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        //注册路由
        $this->loadRoutesFrom(
            __DIR__ . '/../router.php'
        );
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
