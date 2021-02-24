<?php

/**
 * 主要的web网页请求事件track到matomo
 */
if(!function_exists('track_web')){
    function track_web($category, $action = null, $name = null, $value = null)
    {
        $web_idSite = config('matomo.web_id');
        $web_url    = config('matomo.web_url');
        $tracker    = new \MatomoTracker($web_idSite, $web_url);
        $tracker->doTrackEvent($category, $action ?? $category, $name, $value);
    }
}


/**
 * 主要的后端事件track到matomo
 */
if(!function_exists('app_track_event')){
    function app_track_event($category, $action = null, $name = null, $value = null)
    {
        //开启matomo开关功能
        if (!config('matomo.on')) {
            return;
        }

        //UT跳过matomo即可,前面的改动结果停掉了答赚的matomo
        if (is_testing_env()) {
            return;
        }
        if (is_local_env()){
            return;
        }
        //是否开启管理员账号行为不埋点
        if(config('matomo.matomo_user',false)){
            if(checkUser() && getUser()->role_id != \App\User::USER_STATUS){
                return;
            }
        }

        $event['category'] = $category;
        $event['action']   = $action ?? $category;
        $event['name']     = $name;
        //避免进入的value有对象，不是String会异常
        $event['value'] = $value instanceof String ? $value : false;

        if (config('matomo.use_swoole')) {
            //TCP发送事件数据
            sendMatomoTcpEvent($event);
        } else {
            //直接发送，兼容matomo 3.13.6
            $tracker = new \MatomoTracker(config('matomo.matomo_id'), config('matomo.matomo_url'));
            //用户机型
            // $tracker->setCustomVariable(1, '机型', $event['dimension5'], 'visit');

            $tracker->setUserId(getUniqueUserId());
            $tracker->setIp(getIp());
            $tracker->setTokenAuth(config('matomo.token_auth'));
            $tracker->setRequestTimeout(1); //最多卡1s
            $tracker->setForceVisitDateTime(time());

            $tracker->setCustomVariable(1, '系统', $event['dimension1'], 'visit');
            $tracker->setCustomVariable(2, '来源', $event['dimension2'], 'visit');
            $tracker->setCustomVariable(3, '版本', $event['dimension3'], 'visit');
            $tracker->setCustomVariable(4, '用户', $event['dimension4'], 'visit');
            $tracker->setCustomVariable(5, "服务器", gethostname(), "visit");
            $tracker->setCustomVariable(6, '机型', $event['dimension5'], 'visit');

            try {
                //直接发送到matomo
                $tracker->doTrackEvent($category, $action, $name, $value);
                // $url = $tracker->getUrlTrackEvent($category, $action, $name, $value);
            } catch (\Throwable $ex) {
                return false;
            }
        }
    }
}

if(!function_exists('wrapMatomoEventData')){
    function wrapMatomoEventData($event)
    {
        $event['user_id'] = getUniqueUserId();
        $event['ip']      = getIp();

        //传给自定义变量 服务器
        $event['server'] = gethostname();
        $event['cdt']    = time();

        $event['dimension1'] = getOsSystemVersion(); //设备系统带版本
        $event['dimension2'] = get_referer(); //下载渠道
        $event['dimension3'] = getAppVersion(); //版本
        $event['dimension4'] = getUserCategoryTag(); //新老用户分类
        $event['dimension5'] = getDeviceBrand(); //用户机型品牌

        $event['siteId'] = config('matomo.matomo_id');
        return $event;
    }
}

if (!function_exists('sendMatomoTcpEvent')){
    function sendMatomoTcpEvent(array $event)
    {
        //包装必要的事件参数进入数组
        $json = json_encode(wrapMatomoEventData($event));
        try {
            $client = new \swoole_client(SWOOLE_SOCK_TCP); //同步阻塞？？
            //默认0.1秒就timeout, 所以直接丢给本地matomo:server
            $port = config('matomo.proxy_port');
            $client->connect('127.0.0.1', $port) or die("swoole connect failed\n");
            $client->set([
                'open_length_check'     => true,
                'package_length_type'   => 'n',
                'package_length_offset' => 0,
                'package_body_offset'   => 2,
            ]);
            $client->send(tcp_pack($json));
        } catch (\Throwable $ex) {
            return false;
        }
        return true;
    }
}

if (!function_exists('tcp_pack')){
    function tcp_pack(string $data): string
    {
        return pack('n', strlen($data)) . $data;
    }
}

if (!function_exists('tcp_unpack')){
    function tcp_unpack(string $data): string
    {
        return substr($data, 2, unpack('n', substr($data, 0, 2), 0)[1]);
    }
}

if (!function_exists('getUniqueUserId')){
    function getUniqueUserId()
    {
        try {
            return getUserId();
        } catch (\Exception $ex) {
            return getIp();
        }
    }
}

/**
 * 用户分类（匿名,新,未提现,老）
 */
if (!function_exists('getUserCategoryTag')){
    function getUserCategoryTag()
    {
        $user = getUser(false);
        if (blank($user)) {
            return "匿名用户";
        }
        if ($user->created_at > now()->subDay()) {
            return '新用户';
        }
        //FIXME: 需要用户表维护最后提现时间字段withdraw_at
        if (isset($user->withdraw_at)) {
            return "未提现用户";
        }
        return '老用户';
    }
}
