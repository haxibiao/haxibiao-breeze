<?php

namespace Haxibiao\Breeze;

use Haxibiao\Breeze\Console\InstallCommand;
use Haxibiao\Breeze\Console\PublishCommand;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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

        //注册blade directives
        $this->registerBladeDirectives();

        $this->bindPathsInContainer();

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

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //安装时需要
        if ($this->app->runningInConsole()) {
            //数据库
            $this->loadMigrationsFrom($this->app->make('path.haxibiao-breeze.migrations'));

            //前端资源
            $this->publishes([
                __DIR__ . '/../public/css'    => public_path('vendor/breeze/css'),
                __DIR__ . '/../public/images' => public_path('vendor/breeze/images'),
                __DIR__ . '/../public/js'     => public_path('vendor/breeze/js'),
            ], 'breeze-resources');

            //发布 graphql
            $this->publishes([
                __DIR__ . '/../graphql' => base_path('graphql'),
            ], 'breeze-graphql');

        }

        //注册路由
        $this->loadRoutesFrom(
            __DIR__ . '/../router.php'
        );

        $this->bindObservers();
    }

    public function bindObservers()
    {

        \Haxibiao\Sns\Message::observe(\Haxibiao\Breeze\Observers\MessageObserver::class);
        \Haxibiao\Sns\Comment::observe(\Haxibiao\Breeze\Observers\CommentObserver::class);
        \Haxibiao\Sns\Like::observe(\Haxibiao\Breeze\Observers\LikeObserver::class);
        \Haxibiao\Sns\Follow::observe(\Haxibiao\Breeze\Observers\FollowObserver::class);
        \Haxibiao\Sns\Report::observe(\Haxibiao\Breeze\Observers\ReportObserver::class);
        \Haxibiao\Sns\Notice::observe(\Haxibiao\Breeze\Observers\NoticeObserver::class);
        \Haxibiao\Media\Spider::observe(\Haxibiao\Breeze\Observers\SpiderObserver::class);
        \Haxibiao\Breeze\User::observe(\Haxibiao\Breeze\Observers\UserObserver::class);

        \Haxibiao\Breeze\BadWord::observe(\Haxibiao\Breeze\Observers\BadWordObserver::class);
        \Haxibiao\Task\Contribute::observe(\Haxibiao\Breeze\Observers\ContributeObserver::class);
        \Haxibiao\Media\Spider::observe(\Haxibiao\Breeze\Observers\SpiderObserver::class);
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
