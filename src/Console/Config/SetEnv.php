<?php

namespace Haxibiao\Breeze\Console\Config;

use Illuminate\Console\Command;

/**
 * 依赖ops/env里的bash/server_etc.sh同步一些关键环境配置key secret到目标服务的/etc目录下
 */
class SetEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:env {--db_database=} {--db_host=} {--db_port=} {--pay : 是否配置支付能力}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '设置env的值(当前主要设置当前环境的一些安全设置)';

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
        //先覆盖最新的 .env.prod 模板
        $envFile = app()->environmentFilePath();
        $env     = @file_get_contents($envFile . ".prod");
        if (!$env) {
            $env = @file_get_contents($envFile . ".local");
        }
        @file_put_contents($envFile, $env);

        setEnvValues(['DB_PASSWORD' => @file_get_contents("/etc/sql_pass_dm")]);
        // setEnvValues(['MONGO_PASSWORD' => @file_get_contents("/etc/nosql_pass")]);

        //数据库
        if ($this->option("db_database")) {
            setEnvValues(['DB_DATABASE' => $this->option("db_database")]);
        }
        if ($this->option("db_host")) {
            setEnvValues(['DB_HOST' => $this->option("db_host")]);
        }
        if ($this->option("db_port")) {
            setEnvValues(['DB_PORT' => $this->option("db_port")]);
        }

        // 直播 (已暂停开发)
        // setEnvValues(['LIVE_SECRET_KEY' => @file_get_contents("/etc/live_secret")]);

        // 邮件
        setEnvValues(['MAIL_PASSWORD' => @file_get_contents("/etc/mailgun_mail_pass")]);
        // VOD
        setEnvValues(['VOD_SECRET_KEY' => @file_get_contents("/etc/vod_secret_key")]);
        // COS
        setEnvValues(['COS_SECRET_KEY' => @file_get_contents("/etc/cos_secret_key")]);

        //支付的
        if ($this->option('pay')) {
            $this->warn("更新支付信息...");
            //答妹暂时没微信支付
            setEnvValues(['WECHAT_PAY_KEY' => @file_get_contents("/etc/wechat_pay_key")]);
            setEnvValues(['WECHAT_PAY_MCH_ID' => @file_get_contents("/etc/wechat_pay_mch_id")]);
            setEnvValues(['ALIPAY_PAY_APPID' => @file_get_contents("/etc/appid_alipay")]);
        }
    }
}
