<?php

namespace Haxibiao\Breeze\Console\Config;

use Illuminate\Console\Command;

class UpdateEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:env {key} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '明文更新 env 任意 key value';

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
     * @return int
     */
    public function handle()
    {
        $key   = $this->argument('key');
        $value = $this->argument('value');
        $this->setKV([
            $key => $value,
        ]);
    }

    /**
     * 设置.env里key value
     */
    public function setKV(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                // $this->info("更新 $envKey");

                $str .= "\n"; // 确保.env最后一行有换行符
                $keyPosition       = strpos($str, "{$envKey}="); //找到要替换的行字符起始位
                $endOfLinePosition = strpos($str, "\n", $keyPosition); //那行的结束位
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // 如果不存在，就添加一行
                if ($keyPosition === -1) {
                    $str .= "{$envKey}={$envValue}\n";
                    $this->info(" - 增加 $envKey");
                } else {
                    //否则替换
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                    $this->info(" - 替换 $envKey");
                }
            }
        }

        // $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        }

        return true;
    }
}
