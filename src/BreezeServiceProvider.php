<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Console\ImageLogo;
use Haxibiao\Breeze\Console\InstallCommand;
use Haxibiao\Breeze\Console\PublishCommand;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

        $this->loadRoutesFrom(
            __DIR__ . '/../router.php'
        );

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //多站/多APP切换数据库实例
        if ($switch_map = config('cms.app_domain_switch')) {
            foreach ($switch_map as $app_name => $domain) {

                //配置多个APP/网站需要的connection
                $connection_mysql   = config('database.connections.mysql');
                $connection_for_app = [
                    $app_name => $connection_mysql,
                ];
                $connections = config('database.connections');
                $connections = array_merge($connections, $connection_for_app);
                Config::set('database.connections', $connections);

                //每个db connection 对应一个数据库(连接名=数据库名=app_name同名)
                Config::set('database.connections.' . $app_name . '.database', $app_name);

                if ($domain == get_domain()) {
                    DB::purge('mysql');
                    //修改为当前项目的数据库名
                    Config::set('database.connections.mysql.database', $app_name);
                    DB::reconnect();
                }
            }
        }

        $this->bindObservers();
        $this->bindListeners();

        //仅线上seo域名顶级域名强制https
        if (is_prod_env() && get_domain() == get_sub_domain()) {
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
