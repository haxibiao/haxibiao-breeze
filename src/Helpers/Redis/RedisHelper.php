<?php
namespace Haxibiao\Breeze\Helpers\Redis;

use Illuminate\Support\Facades\Redis;

class RedisHelper
{
    protected static $instance;

    public static function redis($conn = 'cache', $isThorw = false)
    {
        if (empty(self::$instance)) {
            $redis = Redis::connection($conn);
            try {
                $redis->ping();
            } catch (\Predis\Connection\ConnectionException $ex) {
                $redis = null;
                //丢给sentry报告
                app('sentry')->captureException($ex);
                //继续向下抛出异常
                if ($isThorw) {
                    throw new $ex;
                }
            }
            self::$instance = $redis;
        }
        return self::$instance;
    }
}
