<?php

use App\BanDevice;
use App\User;
use Haxibiao\Breeze\Breeze;
use Haxibiao\Breeze\Exceptions\UserException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * breeze的mix, 优先尊重app public path 下的mix-manifest.json
 */
function breeze_mix($path)
{
    $manifestPaths = [base_path('public/mix-manifest.json'), breeze_path('public/mix-manifest.json'), media_path('public/mix-manifest.json')];
    return resolve_mix_version_path($path, $manifestPaths);
}

/**
 * 尊重manifestPath实现版本更新的mix函数
 *
 * @param string $path 资源路径
 * @param array $manifestPaths
 * @return string
 */
function resolve_mix_version_path($path, $manifestPaths)
{
    if (!Str::startsWith($path, '/')) {
        $path = "/" . $path;
    }

    //匹配path
    foreach ($manifestPaths as $manifestPath) {
        if (is_file($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if ($asset_path = $manifest[$path] ?? null) {
                //启用jsdelivr的cdn加速
                if (config('breeze.enable.jsdelivr')) {
                    //直接开启最新压缩版本
                    $asset_path = str_replace('.js', '.min.js', $asset_path);
                    $asset_path = str_replace('.css', '.min.css', $asset_path);
                    return "https://cdn.jsdelivr.net/gh/haxibiao/haxibiao-media@latest/public" . $asset_path;
                }
                return $asset_path;
            }
        }
    }

    $exception = new Exception("Unable to locate Breeze Mix file: {$path}.");
    if (!app('config')->get('app.debug')) {
        report($exception);
        return $path;
    } else {
        throw $exception;
    }
}

/**
 * 简化pwa开启逻辑，只要是二级域名都是pwa
 */
function is_enable_pwa()
{
    if (isRobot()) {
        return false;
    }
    //优先尊重breeze.enable_pwa
    if (!is_null(config('breeze.enable_pwa'))) {
        return config('breeze.enable_pwa');
    }
    if (is_sub_domain()) {
        return true;
    }
    return false;
}

/**
 * pwa视图,支持cms.pwa_themes主题配置
 */
function pwa_view()
{
    if ($theme = config('cms.pwa_themes')[get_sub_domain()] ?? null) {
        return view('pwa.' . $theme);
    }
    return view('pwa.index');
}

/**
 * 站群APP群暂时没必须分库的理由
 */
function switch_sites_db()
{
    // $db_switch_map = [];
    // foreach (config('cms.sites', []) as $domain => $names) {
    //     if ($db_name = data_get($names, 'db_name', data_get($names, 'app_name'))) {
    //         $db_switch_map[$db_name] = $domain;
    //     }
    // }
    // foreach ($db_switch_map as $db_name => $domain) {
    //     //SEO都用顶级域名
    //     if ($domain === get_domain()) {
    //         if ($db_name !== config('database.connections.mysql.database')) {
    //             DB::purge('mysql');
    //             //修改为当前项目的数据库名
    //             config(['database.connections.mysql.database' => $db_name]);
    //             DB::reconnect();
    //         }
    //     }
    // }

    // //apps多数据库实例切换(根据二级域名)
    // $db_switch_map = [];
    // foreach (config('cms.apps', []) as $domain => $names) {
    //     if ($db_name = data_get($names, 'db_name', data_get($names, 'app_name'))) {
    //         $db_switch_map[$db_name] = $domain;
    //     }
    // }
    // foreach ($db_switch_map as $db_name => $domain) {
    //     //APP都用二级域名
    //     if ($domain === get_sub_domain()) {
    //         if ($db_name !== config('database.connections.mysql.database')) {
    //             DB::purge('mysql');
    //             //修改为当前项目的数据库名
    //             config(['database.connections.mysql.database' => $db_name]);
    //             DB::reconnect();
    //         }
    //     }
    // }
}

if (!function_exists('register_routes')) {
    function register_routes($path)
    {
        $is_testing = false;
        try {
            $phpunit    = simplexml_load_file('phpunit.xml');
            $is_testing = !app()->environment('prod');
        } catch (Exception $ex) {
        }
        $files = [];
        get_allfiles($path, $files);
        foreach ($files as $apiFile) {
            if ($is_testing) {
                require $apiFile;
            } else {
                require_once $apiFile;
            }
        }
    }
}

function load_breeze_frontend($public_path)
{
    //breeze核心的前端
    $scan_folders = [
        'css',
        'js',
    ];
    foreach ($scan_folders as $folder) {
        foreach (glob($public_path . "/" . $folder . "/*") as $filepath) {
            $asset_path = str_replace($public_path, '', $filepath);
            Breeze::asset($asset_path, $filepath);
        }
    }

    //默认pwa的sw
    Breeze::asset('/service-worker.js', $public_path . '/service-worker.js');

    //站群：不同域名的icons
    foreach (glob(public_path('/images/icons/' . get_domain() . '/*')) as $filepath) {
        $asset_path = str_replace(public_path('/'), '/', $filepath);
        $asset_path = str_replace('/images/icons/' . get_domain(), '/images/icons', $asset_path);
        Breeze::asset($asset_path, $filepath);
    }

    //APP群：不同主题的pwa前端
    if ($theme = config('cms.pwa_themes')[get_sub_domain()] ?? null) {
        foreach ($scan_folders as $folder) {
            foreach (glob(public_path('/themes/' . $theme . '/' . $folder . '/*')) as $filepath) {
                $asset_path = str_replace(public_path('/'), '/', $filepath);
                //替换为根目录的
                $asset_path = str_replace('/themes/' . $theme . '/' . $folder, '/' . $folder, $asset_path);
                Breeze::asset($asset_path, $filepath);
            }
        }

        //主题模板pwa的sw
        Breeze::asset('/service-worker.js', public_path('/themes/' . $theme . '/js/service-worker.js'));
    }

}

/**
 * 加载子模块视图依赖的public目录下的assets
 */
function load_breeze_assets($public_path = null)
{
    $public_path = $public_path ?? breeze_path('public');

    //加载前端代码
    load_breeze_frontend($public_path);

    //加载图片字体
    $scan_folders = [
        'img',
        'images',
        'images/movie',
        'images/app',
        'images/logo',
        'images/icons',
    ];
    foreach ($scan_folders as $folder) {
        foreach (glob($public_path . "/" . $folder . "/*") as $filepath) {
            $asset_path = str_replace($public_path, '', $filepath);
            Breeze::asset($asset_path, $filepath);
        }
    }
}

function breeze_path($path)
{
    return __DIR__ . "/../../" . $path;
}

function checkUserDevice()
{
    $deviceId = get_device_id();
    if (!empty($deviceId)) {
        $banDevice = BanDevice::deviceIsBaned($deviceId);
        if (!is_null($banDevice)) {
            $banDevice->fault();
        }
    }
}

/**
 * 读取当前请求缓存的登录用户
 * @deprecated  新版本方法叫 currentUser()
 * @return User|null
 */
function checkUser()
{
    return currentUser();
}

/**
 * 读取当前请求缓存的登录用户(只读用，更新用getUser)
 */
function currentUser()
{
    if ($userJson = request('current_user')) {
        $user     = null;
        $userData = is_array($userJson) ? $userJson : json_decode($userJson, true) ?? [];
        // if (!is_array($userData)) {
        //     return getUser(false);
        // }
        $userData = array_except($userData, ['profile', 'data', 'user_profile', 'user_data']);

        //获取有效的context缓存用户信息
        $user_id = $userData['id'] ?? null;
        if ($user_id) {
            $user = new User();
            $user->forceFill($userData);
            $user->id = $user_id;
        }
        return $user;
    }
    return getUser(false);
}

/**
 * 获取当前用户，兼容api guard 和 token guard, 和旧版自定义token header
 *
 * @param boolean $throw 是否丢未登录异常，默认丢
 * @return User
 */
function getUser($throw = true)
{
    // api 场景
    $user = auth('api')->user() ?? request()->user();

    if (blank($user)) {
        //兼容多场景(app) 获得token
        $token = request()->bearerToken();
        if (blank($token)) {
            $token = request()->header('token') ?? request('api_token') ?? request('token');
        }

        //获得用户身份
        if ($token) {
            $user = User::where('api_token', $token)->first();
        }

        if (blank($user)) {
            // web 场景
            $user = Auth::user();
        }
    }
    $isValidUser = isset($user) && $user->id > 0;

    //请求中，缓存用户对象，不缓存profile和 data
    $cache_user = $isValidUser ? clone $user : null;
    if ($cache_user) {
        $cache_user->profile      = null;
        $cache_user->data         = null;
        $cache_user->user_profile = null;
        $cache_user->user_data    = null;
    }

    if (!$cache_user) {
        request()->request->add(['current_user' => json_encode($cache_user)]);
    }

    //getUser模式默认支持提示登录失败
    if (!$isValidUser) {
        throw_if($throw, UserException::class, '客户端还没登录...');
        return null;
    }

    return $user;
}

/**
 * 获取当前登录用户的ID
 * @return int|null
 */
function getUserId()
{
    if ($user = currentUser()) {
        return $user->id;
    }
    return null;
}

function isAdmin()
{
    if ($user = currentUser()) {
        return isset($user->role_id) && $user->role_id == User::ADMIN_STATUS;
    }
    return false;
}

function getUserById($id)
{
    return !is_null($id) ? User::find($id) : null;
}

function canEdit($content)
{
    return checkEditor() || $content->isSelf();
}

function user_id()
{
    return Auth::check() ? Auth::user()->id : 0;
}

/**
 * 登录用户是否关注了
 */
function is_follow($type, $id)
{
    $user = currentUser();
    if ($user && !is_string($user)) {
        return $user->isFollow($type, $id);
    }
    return false;
}

/**
 * 检查是否编辑以上身份
 */
function checkEditor()
{
    if ($user = currentUser()) {
        return $user->checkEditor();
    }
    return false;
}

/**
 * 检查是否管理以上身份
 */
function checkAdmin()
{
    if ($user = currentUser()) {
        return $user->checkAdmin();
    }
    return false;
}

/**
 * 保存上传文件的文件夹(规范app文件存储位置)
 *
 * @param string $type 类型 avatars:头像 movies:影片封面 ...
 * @return string
 */
function storage_folder($type = 'avatars')
{
    return sprintf('/storage/app-%s/%s', env('APP_NAME'), $type);
}

if (!function_exists('get_files')) {
    function get_files($path, $full_path = true, $allowExtension = '*')
    {
        $files = [];
        if (is_file($path)) {
            $files[] = $path;
        }

        if (is_dir($path)) {
            $handler = opendir($path);
            while (($filename = readdir($handler)) !== false) {
                //使用!==，防止目录下出现类似文件名“0”等情况
                if ($filename != "." && $filename != "..") {
                    if ($allowExtension != '*' && get_file_ext($filename) != $allowExtension) {
                        continue;
                    }
                    $files[] = $full_path ? $path . '/' . $filename : $filename;
                }
            }
            closedir($handler);
        }

        return $files;
    }
}

if (!function_exists('get_allfiles')) {
    function get_allfiles($path, &$files)
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file !== "." && $file !== "..") {
                    get_allfiles($path . "/" . $file, $files);
                }
            }
            $dp->close();
        }
        if (is_file($path)) {
            $files[] = $path;
        }
    }
}

if (!function_exists('get_file_ext')) {
    function get_file_ext($file)
    {
        $ext = substr($file, strpos($file, '.') + 1); //获取文件后缀
        return $ext;
    }
}

/**
 * 设置.env里多个key/value(s)(支持新增key)
 */
function setEnvValues(array $keyValues, $envFilePath = null)
{
    $envFilePath = $envFilePath ?? app()->environmentFilePath();
    $str         = file_get_contents($envFilePath);

    // 确保.env最后一行有换行符
    if (!str_ends_with($str, "\n")) {
        $str .= "\n";
    }

    if (count($keyValues) > 0) {
        foreach ($keyValues as $envKey => $envValue) {
            $keyPosition       = strpos($str, "{$envKey}="); //找到要替换的行字符起始位
            $endOfLinePosition = strpos($str, "\n", $keyPosition); //那行的结束位
            $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition); //提取原值

            // 如果不存在，就添加一行
            // $existKey = !$keyPosition || !$endOfLinePosition || !$oldLine;
            $existKey = str_contains($str, $envKey);
            if (!$existKey) {
                $str .= "{$envKey}={$envValue}";
            } else {
                //否则替换
                $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
            }
        }
        // $str = substr($str, 0, -1); //最后的换行清理？
    }
    if (!file_put_contents($envFilePath, $str)) {
        return false;
    }
    return true;
}

/**
 * 复制基础代码stubs文件用
 *
 * @param string $pwd 当前代码目录
 * @param boolean $force 是否强制
 * @return void
 */
function copyStubs($pwd, $force = false)
{
    //复制所有ops stubs
    if (!is_dir(base_path('ops'))) {
        mkdir(base_path('ops'));
    }
    foreach (glob($pwd . '/stubs/ops/*.stub') as $filepath) {
        $filename = basename($filepath);
        $dest     = base_path('ops/' . str_replace(".stub", ".sh", $filename));
        if (!file_exists($dest) || $force) {
            copy($filepath, $dest);
        }
    }

    //复制所有App stubs
    foreach (glob($pwd . '/stubs/*.stub') as $filepath) {
        $filename = basename($filepath);
        $dest     = app_path(str_replace(".stub", ".php", $filename));
        if (!file_exists($dest) || $force) {
            copy($filepath, $dest);
        }
    }

    //复制所有Nova stubs
    if (!is_dir(app_path('Nova'))) {
        mkdir(app_path('Nova'));
    }
    foreach (glob($pwd . '/stubs/Nova/*.stub') as $filepath) {
        $filename = basename($filepath);
        $dest     = app_path('Nova/' . str_replace(".stub", ".php", $filename));
        if (!file_exists($dest) || $force) {
            copy($filepath, $dest);
        }
    }

    //复制所有GraphQL stubs
    if (!is_dir(app_path('GraphQL/Directives'))) {
        mkdir(app_path('GraphQL/Directives'), 0777, true);
    }
    if (!is_dir(app_path('GraphQL/Scalars'))) {
        mkdir(app_path('GraphQL/Scalars'), 0777, true);
    }

    foreach (glob($pwd . '/stubs/GraphQL/Directives/*.stub') as $filepath) {
        $filename = basename($filepath);
        $dest     = app_path('GraphQL/Directives/' . str_replace(".stub", ".php", $filename));
        if (!file_exists($dest) || $force) {
            copy($filepath, $dest);
        }
    }
    foreach (glob($pwd . '/stubs/GraphQL/Scalars/*.stub') as $filepath) {
        $filename = basename($filepath);
        $dest     = app_path('GraphQL/Scalars/' . str_replace(".stub", ".php", $filename));
        if (!file_exists($dest) || $force) {
            copy($filepath, $dest);
        }
    }
}

/**
 * 返回通知Notification的type里的full class name的最后className, 比如:ArticleCommented
 */
function short_notify_type($type)
{
    return Str::afterLast($type, "\\");
}
