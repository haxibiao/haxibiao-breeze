<?php

use App\BanDevice;
use App\User;
use Haxibiao\Breeze\Breeze;
use Haxibiao\Breeze\Exceptions\UserException;
use Illuminate\Support\Facades\Auth;

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

/**
 * 加载 个breeze模块 下 views 依赖的 css js images
 */
if (!function_exists('load_breeze_assets')) {
    function load_breeze_assets($public_path)
    {
        foreach (glob($public_path . '/css/*') as $filepath) {
            $asset_path = str_replace($public_path, '', $filepath);
            Breeze::asset($asset_path, $filepath);
        }

        foreach (glob($public_path . '/js/*') as $filepath) {
            $asset_path = str_replace($public_path, '', $filepath);
            Breeze::asset($asset_path, $filepath);
        }

        foreach (glob($public_path . '/images/*') as $filepath) {
            $asset_path = str_replace($public_path, '', $filepath);
            Breeze::asset($asset_path, $filepath);
        }

        foreach (glob($public_path . '/images/movie/*') as $filepath) {
            $asset_path = str_replace($public_path, '', $filepath);
            Breeze::asset($asset_path, $filepath);
        }

        foreach (glob($public_path . '/images/app/*') as $filepath) {
            $asset_path = str_replace($public_path, '', $filepath);
            Breeze::asset($asset_path, $filepath);
        }

        foreach (glob($public_path . '/images/logo/*') as $filepath) {
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
    if ($userJson = request('user')) {
        $userData = is_array($userJson) ? $userJson : json_decode($userJson, true);
        $userData = is_array($userData) ? $userData : json_decode(request()->user(), true);
        $userData = array_except($userData, ['profile', 'data', 'user_profile', 'user_data']);

        //获取有效的context缓存用户信息
        $user_id = $userData['id'] ?? null;
        if ($user_id) {
            $user = new User();
            $user->forceFill($userData);
            $user->id = $user_id;
            return $user;
        }
    }
    return getUser(false);
}

/**
 * 读取当前请求缓存的登录用户(只读用，更新用getUser)
 */
function currentUser()
{
    return checkUser();
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

    //getUser模式默认支持提示登录失败
    $isValidUser = isset($user) && $user->id > 0;
    if (!$isValidUser) {
        throw_if($throw, UserException::class, '客户端还没登录...');
        return null;
    } else {
        //请求中，缓存用户对象，不缓存profile和 data
        $cache_user               = clone $user;
        $cache_user->profile      = null;
        $cache_user->data         = null;
        $cache_user->user_profile = null;
        $cache_user->user_data    = null;
        request()->request->add(['user' => json_encode($cache_user)]);
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
        return isset($user->role_id) && $user->role_id = User::ADMIN_STATUS;
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

//FIXME:matomo app_track_event 事件会触发到这个无效的方法
// if (!function_exists("app_track_event")) {
//     function app_track_event()
//     {
//         //FIXME: 临时兼容matomo还没完成合并到breeze内的异常问题
//     }
// }

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
    $envFile = $envFilePath ?? app()->environmentFilePath();
    $str     = file_get_contents($envFile);

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
    if (!file_put_contents($envFile, $str)) {
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
