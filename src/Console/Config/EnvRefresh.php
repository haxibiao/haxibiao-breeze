<?php

namespace Haxibiao\Breeze\Console\Config;

use Illuminate\Console\Command;

/**
 * 读取webconfig.json模式的，适合工厂，set:env适合单个项目单个服务器简单刷新私密配置
 */
class EnvRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:refresh {--db_host=} {--db_database=} {--db_port=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'refresh .env file, db_host is must to pass';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $env = $this->option('env') ?? 'prod';
        $this->$env();
    }

    public function copy_env()
    {
        $this->info('copy .env ...');
        $env_dev = file_get_contents(base_path('.env.local'));
        if (blank($env_dev)) {
            $env_dev = file_get_contents(base_path('.env.prod'));
        }
        file_put_contents(base_path('.env'), $env_dev);
    }

    public function local()
    {
        $this->info('更新env... 环境 local');
        $this->copy_env();
        $this->updateEnv([
            'APP_ENV'          => 'local',
            'APP_DEBUG'        => 'true',
            'FILESYSTEM_CLOUD' => 'public',
        ]);

        $this->info('更新db cloud 等配置...');
        $this->updateWebConfig($this->option('db_host'), $this->option('db_database'), $this->option('db_port'));

    }

    public function develop()
    {
        $this->info('更新env... 环境 develop');
        $this->copy_env();
        $this->updateEnv([
            'APP_ENV'          => 'develop',
            'APP_DEBUG'        => 'true',
            'FILESYSTEM_CLOUD' => 'public',
        ]);

        $this->info('更新db cloud 等配置...');
        $this->updateWebConfig($this->option('db_host'), $this->option('db_database'), $this->option('db_port'));

    }

    public function staging()
    {
        $this->info('更新env... 环境 staging');
        $this->copy_env();
        $this->updateEnv([
            'APP_ENV'          => 'staging',
            'APP_DEBUG'        => 'true',
            'FILESYSTEM_CLOUD' => 'cos',
        ]);

        $this->info('更新db cloud 等配置...');
        $this->updateWebConfig($this->option('db_host'), $this->option('db_database'), $this->option('db_port'));

    }

    public function prod()
    {
        $this->info('更新env... 环境 prod');
        $this->copy_env();
        $this->updateEnv([
            'APP_ENV'          => 'prod',
            'APP_DEBUG'        => 'false',
            'FILESYSTEM_CLOUD' => 'cos',
        ]);
        $this->info('更新db cloud 等配置...');
        $this->updateWebConfig($this->option('db_host'), $this->option('db_database'), $this->option('db_port'));
    }

    public function updateWebConfig($db_host = null, $db_database = null, $db_port = null)
    {
        $this->info("更新配置 db_host=$db_host db_database=$db_database db_port=$db_port");

        $data = @file_get_contents('/etc/webconfig.json');
        if ($data) {
            $webconfig  = json_decode($data);
            $db_changes = [
                'DB_HOST'     => $db_host ?? 'localhost',
                'DB_DATABASE' => $db_database ?? env('APP_NAME'),
                'DB_PORT'     => $db_port ?? '3306',
            ];

            //线上默认数据库host $webconfig->db_host
            if (isset($webconfig->db_host)) {
                if (\is_prod_env()) {
                    if ($webconfig->db_host == $db_host) {
                        $db_changes = array_merge($db_changes, [
                            'DB_PASSWORD' => $webconfig->db_passwd, //线上默认数据库 pass
                        ]);
                    }
                }
            }

            // db 线上不同数据库服务器 pass
            if ($db_host) {
                if (is_array($webconfig->databases)) {
                    foreach ($webconfig->databases as $database) {
                        if ($database->db_host == $db_host) {
                            $db_changes = array_merge($db_changes, [
                                'DB_USERNAME' => $database->db_user,
                                'DB_PASSWORD' => $database->db_passwd,
                            ]);
                        }
                    }
                    $this->info("updating env file with $db_host settings ...");
                }
            }

            //cos id key
            $cos_changes = [];
            if (isset($webconfig->coses) && is_array($webconfig->coses)) {
                foreach ($webconfig->coses as $cos) {
                    if (env('APP_NAME') == $cos->bucket) {
                        $cos_changes = [
                            'COS_APP_ID'     => $cos->appid,
                            'COS_REGION'     => $cos->region,
                            'COS_LOCATION'   => $cos->location,
                            'COS_SECRET_ID'  => $cos->cos_secret_id,
                            'COS_SECRET_KEY' => $cos->cos_secret_key,
                        ];
                    }
                }
                $this->info("updated env file cos env values ...");
            }

            //mail sms vod ...
            $changes = array_merge($cos_changes, $db_changes, [
                'MAIL_HOST'                    => $webconfig->mail_host ?? '',
                'MAIL_USERNAME'                => $webconfig->mail_username ?? '',
                'MAIL_PASSWORD'                => $webconfig->mail_password ?? '',
                'QCLOUD_SMS_ACCESS_KEY_ID'     => $webconfig->qcloud_sms_key_id ?? '',
                'QCLOUD_SMS_ACCESS_KEY_SECRET' => $webconfig->qcloud_sms_key_secret ?? '',
                'VOD_SECRET_ID'                => $webconfig->vod_secret_id ?? '',
                'VOD_SECRET_KEY'               => $webconfig->vod_secret_key ?? '',
            ]);

            $this->updateEnv($changes);

        } else {
            $this->error('webconfig not found!');
        }
    }

    public function updateEnv($data = array())
    {
        if (!count($data)) {
            return;
        }

        $pattern = '/([^\=]*)\=[^\n]*/';

        $envFile  = base_path() . '/.env';
        $lines    = file($envFile);
        $newLines = [];
        foreach ($lines as $line) {
            preg_match($pattern, $line, $matches);

            if (!count($matches)) {
                $newLines[] = $line;
                continue;
            }

            if (!key_exists(trim($matches[1]), $data)) {
                $newLines[] = $line;
                continue;
            }

            $line       = trim($matches[1]) . "={$data[trim($matches[1])]}\n";
            $newLines[] = $line;
        }

        $newContent = implode('', $newLines);
        $put_size   = @file_put_contents($envFile, $newContent);
        if ($put_size) {
            $changes_count = count($data);
            $this->info("update env $changes_count value success");
        }
    }
}
