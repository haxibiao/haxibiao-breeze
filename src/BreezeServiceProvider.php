<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Console\ImageLogo;
use Haxibiao\Breeze\Console\InstallCommand;
use Haxibiao\Breeze\Console\PublishCommand;
use Illuminate\Config\Repository as Config;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
        'Haxibiao\Breeze\Events\NewReport'  => [
            'Haxibiao\Breeze\Listeners\SendNewReportNotification',
        ],
        'Haxibiao\Breeze\Events\NewLike'    => [
            'Haxibiao\Breeze\Listeners\SendNewLikeNotification',
        ],
        'Haxibiao\Breeze\Events\NewFollow'  => [
            'Haxibiao\Breeze\Listeners\SendNewFollowNotification',
        ],
        'Haxibiao\Breeze\Events\NewComment' => [
            'Haxibiao\Breeze\Listeners\SendNewCommentNotification',
        ],
        'Haxibiao\Breeze\Events\NewMessage' => [
            'Haxibiao\Breeze\Listeners\SendNewMessageNotification',
        ],
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

        //加载编译的breeze css js fonts images
        load_breeze_assets(breeze_path('public'));

        // 这一段会重写掉整个sentry的配置
        $this->rewriteSentryDsn();

        // Register Commands
        $this->commands([
            InstallCommand::class,
            PublishCommand::class,
            ImageLogo::class,

            Console\Dimension\ArchiveAll::class,
            Console\Dimension\ArchiveRetention::class,
            Console\Dimension\ArchiveUser::class,
            Console\Dimension\ArchiveWithdraw::class,

            Console\Matomo\MatomoProxy::class,
            Console\Matomo\MatomoClient::class,

            Console\Config\EnvRefresh::class,
            Console\Config\SetEnv::class,
        ]);

        //合并view paths
        if (!app()->configurationIsCached()) {
            $view_paths = array_merge(
                //APP 的 views 最先匹配
                config('view.paths'),
                //然后 匹配 breeze的默认views
                [breeze_path('resources/views')]
            );
            config(['view.paths' => $view_paths]);
        }

        //注册blade directives
        $this->registerBladeDirectives();

        $this->bindPathsInContainer();

        $this->bindObservers();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        //默认强制线上的站点支持https(google已开始强制站点只收录https多年)
        if (is_prod_env()) {
            URL::forceScheme('https');
        }

        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/breeze.php', 'breeze');
            $this->mergeConfigFrom(__DIR__ . '/../config/seo.php', 'seo');
        }

        //修复分页样式
        Paginator::useBootstrap();

        //安装时需要
        if ($this->app->runningInConsole()) {

            // FIXME:临时添加了一个属性动态控制了migrations的加载。
            if (config('breeze.migration_autoload')) {
                $this->loadMigrationsFrom($this->app->make('path.haxibiao-breeze.migrations'));
            }

            $this->publishes([
                __DIR__ . '/../config/breeze.php' => config_path('breeze.php'),
            ], 'breeze-config');

            $this->publishes([
                __DIR__ . '/../config/matomo.php' => config_path('matomo.php'),
            ], 'breeze-config');

            //前端资源
            $this->publishes([
                // __DIR__ . '/../public/fonts' => public_path('/fonts'),
                // __DIR__ . '/../public/images'            => public_path('/images'),
                // __DIR__ . '/../public/css'               => public_path('/css'),
                // __DIR__ . '/../public/js'                => public_path('/js'),
            ], 'breeze-assets');

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
        Blade::if('admin', function () {
            return Auth::check() && Auth::user()->checkAdmin();
        });

        Blade::if('editor', function () {
            return Auth::check() && Auth::user()->checkEditor();
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

        //\Haxibiao\Wallet\Gold::observe(\Haxibiao\Breeze\Observers\GoldObserver::class);

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
