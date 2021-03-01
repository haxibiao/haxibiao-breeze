<?php

use App\BanDevice;
use App\User;
use Haxibiao\Breeze\Exceptions\UserException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

function content_path($path)
{
    return __DIR__ . "/../../../content/" . $path;
}

function media_path($path)
{
    return __DIR__ . "/../../../media/" . $path;
}

function cms_path($path)
{
    return __DIR__ . "/../../../cms/" . $path;
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
 * 检查并返回当前登录用户
 *
 * @return User
 */
function checkUser()
{
    return getUser(false);
}

/**
 * 获取当前用户
 *
 * @param boolean $throw 是否丢未登录异常，默认丢
 * @return User
 */
function getUser($throw = true)
{
    //已登录,swoole不能记录用户登录状态,会直接记录到服务器内存中,并且之后返回的都是该用户
    if (Auth::check()) {
        return Auth::user();
    }

    //guard api token
    $user = auth('api')->user() ?? request()->user();

    //APP的场景
    if (blank($user)) {
        //兼容passport guard
        $token = request()->bearerToken();

        //兼容我们自定义token方式
        if (blank($token)) {
            $token = request()->header('token') ?? request()->get('token');
        }
        if ($token) {
            $user = User::where('api_token', $token)->first();
        }

        //调试旧/graphiql 的场景
        if (is_giql() && !$user) {
            if ($user_id = Cache::get('giql_uid')) {
                $user = User::find($user_id);
            }
        }
    }

    throw_if(is_null($user) && $throw, UserException::class, '客户端还没登录...');

    //授权,减少重复查询 && 授权背后有个event监听
    if ($user) {
        Auth::login($user);
    }

    return $user;
}

//获取当前用户ID
function getUserId()
{
    //兼容哈希表ImageRepo允许外部上传图片，不丢异常
    if ($user = getUser(false)) {
        return $user->id;
    }
    return 0;
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
    if ($user = checkUser()) {
        return $user->isFollow($type, $id);
    }
    return false;
}

/**
 * 检查是否编辑以上身份
 */
function checkEditor()
{
    if ($user = checkUser()) {
        return $user->checkEditor();
    }
    return false;
}

/**
 * 检查是否管理以上身份
 */
function checkAdmin()
{
    if ($user = checkUser()) {
        return $user->checkAdmin();
    }
    return false;
}

if (!function_exists("app_track_event")) {
    function app_track_event()
    {
        //FIXME: 临时兼容matomo还没完成合并到breeze内的异常问题
    }
}
