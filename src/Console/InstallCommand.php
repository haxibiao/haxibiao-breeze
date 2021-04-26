<?php

namespace Haxibiao\Breeze\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InstallCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'breeze:install {--force} {--debug}';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '安装 haxibiao/breeze';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {
        $force = $this->option('force');
        if ($force) {
            if (!$this->confirm("强制覆盖安装breeze?")) {
                return;
            }
        } else {
            $this->comment("开始安装Breeze...");
        }

        if (!file_exists(base_path('.env.prod')) || $force) {
            $this->info('配置环境');
            return $this->configEnvValues();
        }

        if (!is_dir(public_path('vendor/nova'))) {
            $this->comment('安装Nova');
            $this->callSilent('nova:install');
        }

        if (is_dir(base_path('graphql')) && !$force) {
            if (!$this->confirm('已安装过breeze,是否强制覆盖安装?')) {
                return;
            }
        }

        $this->info('安装子模块');
        $this->installModules($force);

        $this->info('安装APP代码');
        $this->installAppCode($force);

        $this->info('安装数据库结构');
        $this->installDatabase();

        $this->info('初始化基础数据');
        $this->callSilent("db:seed", ['--class' => "BreezeSeeder"]);

        $this->newLine(2);
        // $this->comment('发布配置');
        // $this->call('breeze:publish', ['--force' => $force]);

        $this->comment('生成图标');
        $this->callSilent('image:logo');

        $this->comment("完成安装");
    }

    public function configEnvValues()
    {
        if (!file_exists(base_path('.env.example'))) {
            dd("请用新初始化的laravel项目来安装，并确保 env.example未删除掉");
        }
        if (!file_exists(base_path('.env'))) {
            @file_put_contents(base_path('.env'), @file_get_contents(base_path('.env.example')));
        }

        if (blank(env('APP_KEY'))) {
            $this->callSilent('key:gen');
        }

        if ($app_name = $this->ask(" - 网站/APP名称(英文)?", env('APP_NAME'))) {
            setEnvValues(['APP_NAME' => $app_name]);
        }
        if ($app_name_cn = $this->ask(" - 网站/APP名称(中文)?", env('APP_NAME_CN', env('APP_NAME')))) {
            setEnvValues(['APP_NAME_CN' => $app_name_cn]);
        }
        if ($domain = $this->ask(" - 网站域名?", $app_name . ".com")) {
            setEnvValues([
                'APP_DOMAIN' => $domain,
                'APP_URL'    => 'https://' . $domain,
            ]);
        }

        if ($db_host = $this->ask(" - 数据库服务器host?", 'localhost')) {
            setEnvValues(['DB_HOST' => $db_host]);
        }
        if ($db_database = $this->ask(" - 数据库名?", $app_name)) {
            setEnvValues(['DB_DATABASE' => $db_database]);
        }
        if ($db_username = $this->ask(" - 数据库账户名?", 'root')) {
            setEnvValues(['DB_USERNAME' => $db_username]);
        }
        if ($db_password = $this->secret(" - 数据库账户密码?")) {
            setEnvValues(['DB_PASSWORD' => $db_password]);
        }
        if ($default_password = $this->ask(" - 默认网站测试用户密码?", "dadada")) {
            setEnvValues(['DEFAULT_PASSWORD' => $default_password]);
        }

        if ($this->confirm(" - 是否用云存储cos或space?")) {
            $cloudSolution = $this->choice("云方案选择", ['cos', 'space'], 'cos');
            if ($cloudSolution == "cos") {
                //选择cos
                setEnvValues(['FILESYSTEM_DRIVER' => 'cos']);

                $cos_region = $this->choice("区域", ['ap-guangzhou', 'ap-shanghai'], 'ap-guangzhou');
                setEnvValues(['COS_REGION' => $cos_region]);
                $cos_region = $this->ask("存储桶", 'haxibiao');
                setEnvValues(['COS_BUCKET' => $cos_region]);
                $cos_app_id = $this->ask("COS_APP_ID", '1251052432');
                setEnvValues(['COS_APP_ID' => $cos_app_id]);
                $cos_secret_id = $this->ask("COS_SECRET_ID", 'AKIDPbXCbj5C1bz72i7F9oDMHxOaXEgsNX0E');
                setEnvValues(['COS_SECRET_ID' => $cos_secret_id]);
                $cos_secret_key = $this->secret("COS_SECRET_KEY");
                setEnvValues(['COS_SECRET_KEY' => $cos_secret_key]);
                $cos_domain = $this->ask("cos加速的cdn域名", 'cos.haxibiao.com');
                setEnvValues(['COS_DOMAIN' => $cos_domain]);
            }
            if ($cloudSolution == "space") {
                //选择cos
                setEnvValues(['FILESYSTEM_DRIVER' => 'space']);

                $space_region = $this->choice("区域", ['sfo2', 'nyc3'], 'sfo2');
                setEnvValues(['SPACE_REGION' => $space_region]);
                $space_region = $this->ask("存储桶", 'movieimage');
                setEnvValues(['SPACE_BUCKET' => $space_region]);
                $space_key = $this->ask("SPACE_KEY", 'CFE7N5U5YHERNBY232OH');
                setEnvValues(['SPACE_KEY' => $space_key]);
                $space_secret = $this->secret("SPACE_SECRET");
                setEnvValues(['SPACE_SECRET' => $space_secret]);
                $space_domain = $this->ask("space加速的cdn域名", 'space.haxibiao.com');
                setEnvValues(['SPACE_DOMAIN' => $space_domain]);
            }
        }

        $this->comment(' - 备份安装成功的非敏感配置信息到文件.env.prod');
        @file_put_contents(base_path('.env.prod'), @file_get_contents(base_path('.env')));

        setEnvValues([
            'APP_ENV'        => 'prod', //这个小别名兼容以前的习惯，并且避免 migrate 无--force 会跳过的小问题
            'APP_DEBUG'      => 'false',
            'DB_PASSWORD'    => '',
            'COS_SECRET_KEY' => '',
        ], base_path('.env.prod'));

        $this->info(' - 环境配置success');
        $this->info(' - 重新配置环境，请删除.env.prod');
        $this->info(' - 请重新执行 breeze:install 开始安装');

    }

    public function installDatabase()
    {
        // breeze web 默认不需要队列，需要的项目单独配置
        $this->info('清理队列表');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        $this->callSilent('migrate');
    }

    public function installAppCode($force)
    {
        copyStubs(__DIR__, $force);

        // 修复laralve 8 新安装默认的 Auth Config 差异
        $auth_config = \file_get_contents(config_path('auth.php'));
        $auth_config = str_replace("App\Models\User", "App\User", $auth_config);
        file_put_contents(config_path('auth.php'), $auth_config);
    }

    public function installModules($force)
    {
        if ($this->option('debug')) {
            $this->call("media:install", ['--force' => $force]);
            $this->call("content:install", ['--force' => $force]);
            $this->call("sns:install", ['--force' => $force]);
            $this->call("task:install", ['--force' => $force]);
            $this->call("wallet:install", ['--force' => $force]);
            $this->call("question:install", ['--force' => $force]);
        } else {
            $this->callSilent("media:install", ['--force' => $force]);
            $this->callSilent("content:install", ['--force' => $force]);
            $this->callSilent("sns:install", ['--force' => $force]);
            $this->callSilent("task:install", ['--force' => $force]);
            $this->callSilent("wallet:install", ['--force' => $force]);
            $this->callSilent("question:install", ['--force' => $force]);

        }
    }
}
