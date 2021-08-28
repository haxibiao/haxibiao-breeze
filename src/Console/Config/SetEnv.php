<?php

namespace Haxibiao\Breeze\Console\Config;

use Illuminate\Console\Command;

/**
 * 依赖env同步关键环境配置密钥到目标服务器的/etc/breeze目录
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
        //优先覆盖最新的 breeze安装生成的.env.prod 模板
        $envFilePath = app()->environmentFilePath();
        $envTemplate = @file_get_contents($envFilePath . ".prod") ?? @file_get_contents($envFilePath . ".local");
        @file_put_contents($envFilePath, $envTemplate);

        //数据库密码
        if ($db_password = @file_get_contents("/etc/breeze/sql_pass")) {
            //留空的模板才支持 /etc/breeze覆盖
            if (blank(env('DB_PASSWORD'))) {
                setEnvValues(['DB_PASSWORD' => $db_password]);
            }
        }
        //nosql数据库密码
        // setEnvValues(['MONGO_PASSWORD' => @file_get_contents("/etc/nosql_pass")]);

        //数据库名称
        if ($db_database = $this->option("db_database")) {
            setEnvValues(['DB_DATABASE' => $db_database]);
        }
        if ($db_host = $this->option("db_host")) {
            setEnvValues(['DB_HOST' => $db_host]);
        }
        if ($db_port = $this->option("db_port")) {
            setEnvValues(['DB_PORT' => $db_port]);
        }

        // 直播 (已暂停开发)
        // setEnvValues(['LIVE_SECRET_KEY' => @file_get_contents("/etc/breeze/live_secret")]);

        // 邮件
        if ($mail_pass = @file_get_contents("/etc/breeze/mailgun_mail_pass")) {
            setEnvValues(['MAIL_PASSWORD' => $mail_pass]);
        }

        // VOD
        if ($vod_key = @file_get_contents("/etc/breeze/vod_secret_key")) {
            setEnvValues(['VOD_SECRET_KEY' => $vod_key]);
        }

        // COS
        if ($cos_key = @file_get_contents("/etc/breeze/cos_secret_key")) {
            setEnvValues(['COS_SECRET_KEY' => $cos_key]);
        }

        // SPACE
        if ($space_key = @file_get_contents("/etc/breeze/space_secret")) {
            setEnvValues(['SPACE_SECRET' => $space_key]);
        }

        //支付的
        if ($this->option('pay')) {
            $this->warn("更新支付信息...");
            //答妹暂时没微信支付
            setEnvValues(['WECHAT_PAY_KEY' => @file_get_contents("/etc/breeze/wechat_pay_key")]);
            setEnvValues(['WECHAT_PAY_MCH_ID' => @file_get_contents("/etc/breeze/wechat_pay_mch_id")]);
            setEnvValues(['ALIPAY_PAY_APPID' => @file_get_contents("/etc/breeze/appid_alipay")]);
        }
    }
}
