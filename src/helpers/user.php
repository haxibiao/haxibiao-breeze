<?php
use App\BanDevice;
use App\Exceptions\UserException;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

//新老用户分类
function getUserCategoryTag()
{
    $user = getUser(false);
    if (blank($user)) {
        return "匿名用户";
    }
    if ($user->created_at > now()->subDay()) {
        return '新用户';
    }
    if (isset($user->total_withdraw_amount) && $user->total_withdraw_amount == 0) {
        return "未提现用户";
    }
    return '老用户';
}

/**
 * 使用场景:主要提供于APP设计 用于GQL中调用或 restful api
 * APP全局辅助函数
 */
function checkUser()
{
    try {
        $user = getUser();
    } catch (\Exception $ex) {
        return null;
    }

    return $user;
}

//获取当前用户
function getUser($throw = true)
{
    //已登录
    if (Auth::check()) {
        return Auth::user();
    }
    if (auth('api')->user()) {
        return auth('api')->user();
    }

    //APP的场景
    $user = request()->user();
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

        throw_if(is_null($user) && $throw, UserException::class, '客户端还没登录...');

        //授权,减少重复查询
        if ($user) {
            Auth::login($user, true);
        }

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
    return Auth::check() ? Auth::user()->id : false;
}

/**
 * @deprecated 请用新的 $user->is_follow($followable)
 */
function is_follow($type, $id)
{
    return Auth::check() ? Auth::user()->isFollow($type, $id) : false;
}

/**
 * 旧web版本检查是否编辑身份
 * @deprecated
 */
function checkEditor()
{
    if ($user = Auth::user()) {
        return $user->checkEditor();
    }
    return false;
}
