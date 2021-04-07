<?php

use App\BanDevice;
use App\User;
use Haxibiao\Breeze\Exceptions\UserException;
use Illuminate\Support\Facades\Auth;

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
 * 获取当前用户，兼容api guard 和 token guard, 和旧版自定义token header
 *
 * @param boolean $throw 是否丢未登录异常，默认丢
 * @return User
 */
function getUser($throw = true)
{
    //fetch current request context cached user
    if ($userJson = request('user')) {
        $userData = json_decode($userJson, true);
        //获取有效的context缓存用户信息
        $user_id = $userData['id'] ?? null;
        if ($user_id) {
            $user = new User();
            $user->forceFill($userData);
            $user->id = $user_id;
            return $user;
        }
    }

    //兼容 api routes
    $user = auth('api')->user() ?? request()->user();

    if (blank($user)) {
        //兼容 app的场景，gql模式

        //获得token，兼容api guard 和 token guard, 和旧版自定义token header
        $token = request()->bearerToken();
        if (blank($token)) {
            $token = request()->header('token') ?? request('api_token') ?? request('token');
        }

        //获得用户身份
        if ($token) {
            $user = User::where('api_token', $token)->first();
        }

        if (blank($user)) {
            //兼容 web routes - 极少用
            if ($user = Auth::user()) {
                return $user;
            }
        }
    }

    throw_if(is_null($user) && $throw, UserException::class, '客户端还没登录...');
    //add to request context, cache current user
    request()->request->add(['user' => json_encode($user)]);

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
    $user = checkUser();
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

//FIXME:matomo app_track_event 事件会触发到这个无效的方法
// if (!function_exists("app_track_event")) {
//     function app_track_event()
//     {
//         //FIXME: 临时兼容matomo还没完成合并到breeze内的异常问题
//     }
// }
