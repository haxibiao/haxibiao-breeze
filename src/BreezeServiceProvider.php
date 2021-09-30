<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Console\DeployManifest;
use Haxibiao\Breeze\Console\ImageLogo;
use Haxibiao\Breeze\Console\InstallCommand;
use Haxibiao\Breeze\Console\PublishCommand;
use Haxibiao\Breeze\Services\MetaService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class BreezeServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        //注册一些helpers 函数
        $src_path = __DIR__;
        foreach (glob($src_path . '/Helper/*.php') as $filename) {
            require_once $filename;
        }
        $this->bindPathsInContainer();

        // 这一段会重写掉整个sentry的配置
        $this->rewriteSentryDsn();

        // Register Commands
        $this->commands([
            InstallCommand::class,
            PublishCommand::class,
            ImageLogo::class,
            DeployManifest::class,
            Console\Dimension\ArchiveAll::class,
            Console\Dimension\ArchiveRetention::class,
            Console\Dimension\ArchiveUser::class,
            Console\Dimension\ArchiveWithdraw::class,

            Console\Matomo\MatomoProxy::class,
            Console\Matomo\MatomoClient::class,
            Console\Matomo\MatomoGoalModuleCommand::class,

            Console\Config\EnvRefresh::class,
            Console\Config\SetEnv::class,
            Console\Config\UpdateEnv::class,
        ]);

        //加载路由
        if (config('breeze.routes_autoload', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../router.php');
        }

        //加载视图
        if (!app()->configurationIsCached()) {
            $view_paths = array_merge(
                //APP 的 views 最先匹配
                config('view.paths'),
                //然后 匹配 breeze的默认views
                [breeze_path('resources/views')]
            );
            config(['view.paths' => $view_paths]);
        }
        //注册laravelPWA的views
        $this->loadViewsFrom([breeze_path('resources/views/pwa')], 'laravelpwa');

        //注册blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/breeze.php', 'breeze');
            //尊重pwa配置覆盖
            if (file_exists(config_path('pwa.php'))) {
                $this->mergeConfigFrom(config_path('pwa.php'), 'breeze.pwa');
            } else {
                $this->mergeConfigFrom(__DIR__ . '/../config/pwa.php', 'breeze.pwa');
            }
        }

        //加载 breeze 自带的 assets
        load_breeze_assets();

        //SEO网站多数据库实例切换(根据顶级域名)
        switch_breeze_db();

        $this->bindObservers();
        $this->bindListeners();

        //仅线上seo域名顶级域名强制https
        if (is_prod_env() && get_domain() == get_sub_domain()) {
            URL::forceScheme('https');
        }

        //修复分页样式
        Paginator::useBootstrap();

        //安装时需要
        if ($this->app->runningInConsole()) {

            // 数据库
            if (config('breeze.migration_autoload')) {
                $this->loadMigrationsFrom($this->app->make('path.haxibiao-breeze.migrations'));
            }

            // 配置
            $this->publishes([
                __DIR__ . '/../config/breeze.php' => config_path('breeze.php'),
                __DIR__ . '/../config/matomo.php' => config_path('matomo.php'),
                __DIR__ . '/../config/pwa.php'    => config_path('pwa.php'),
            ], 'breeze-config');

            //发布前端资源(暂无需要)
            // load_breeze_assets 已预先加载，因为Breeze是不需要publish就能用的,但是支持覆盖自定义

            //发布 graphql
            $this->publishes([
                __DIR__ . '/../graphql' => base_path('graphql'),
            ], 'breeze-graphql');

        }
    }

    public function registerBladeDirectives()
    {
        Blade::directive('timeago', function ($expression) {
            return "<?php echo diffForHumansCN($expression); ?>";
        });
        //将秒数转换成 分:秒
        Blade::directive('sectominute', function ($expression) {
            return "<?php echo gmdate('i:s', $expression); ?>";
        });

        Blade::directive('pwa', function () {
            return (new MetaService)->render();
        });

        Blade::if('admin', function () {
            return currentUser() && currentUser()->checkAdmin();
        });

        Blade::if('editor', function () {
            return currentUser() && currentUser()->checkEditor();
        });

        Blade::if('weixin', function () {
            return request('weixin');
        });

        $this->app->singleton('app.config.beian', function ($app) {
            return \App\AppConfig::where([
                'group' => 'record',
                'name'  => 'web',
            ])->first();
        });

        $this->app->singleton('asos', function ($app) {
            return \App\Aso::all();
        });
        $this->app->singleton('seos', function ($app) {
            return \App\Seo::all();
        });
    }

    public function bindObservers()
    {
        \Haxibiao\Breeze\User::observe(\Haxibiao\Breeze\Observers\UserObserver::class);
        \Haxibiao\Breeze\BadWord::observe(\Haxibiao\Breeze\Observers\BadWordObserver::class);
        \Haxibiao\Task\Contribute::observe(\Haxibiao\Breeze\Observers\ContributeObserver::class);
    }

    public function bindListeners()
    {
        \Illuminate\Support\Facades\Event::listen(
            'Haxibiao\Breeze\Events\NewLike',
            'Haxibiao\Breeze\Listeners\SendNewLikeNotification'
        );
        \Illuminate\Support\Facades\Event::listen(
            'Haxibiao\Breeze\Events\NewFollow',
            'Haxibiao\Breeze\Listeners\SendNewFollowNotification'
        );
        \Illuminate\Support\Facades\Event::listen(
            'Haxibiao\Breeze\Events\NewReport',
            'Haxibiao\Breeze\Listeners\SendNewReportNotification'
        );
        \Illuminate\Support\Facades\Event::listen(
            'Haxibiao\Breeze\Events\NewComment',
            'Haxibiao\Breeze\Listeners\SendNewCommentNotification'
        );
        \Illuminate\Support\Facades\Event::listen(
            'Haxibiao\Breeze\Events\NewComment',
            'Haxibiao\Breeze\Listeners\UpdateCommentMorphData'
        );

        // 新私信消息broadcast即可，暂时无需发通知或邮件SMS等
        // \Illuminate\Support\Facades\Event::listen(
        //     'Haxibiao\Breeze\Events\NewMessage',
        //     'Haxibiao\Breeze\Listeners\SendNewMessageNotification'
        // );
    }

    protected function bindPathsInContainer()
    {
        foreach ([
            'path.haxibiao-breeze'            => $root = dirname(__DIR__),
            'path.haxibiao-breeze.config'     => $root . '/config',
            'path.haxibiao-breeze.database'   => $database = $root . '/database',
            'path.haxibiao-breeze.migrations' => $database . '/migrations',
            'path.haxibiao-breeze.seeders'    => $database . '/seeders',
            'path.haxibiao-breeze.graphql'    => $root . '/graphql',
        ] as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

    protected function rewriteSentryDsn()
    {
        if (!app()->environment('prod')) {
            return;
        }
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
