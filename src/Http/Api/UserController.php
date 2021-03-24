<?php

namespace Haxibiao\Breeze\Http\Api;

use App\Article;
use App\Http\Controllers\Controller;
use App\Image;
use App\Post;
use App\User;
use App\Video;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getSetting(Request $request)
    {
        $user = $request->user();
        //让设置页面的头像刷新浏览器缓存
        $user->avatar  = $user->avatarUrl . '?t=' . time();
        $user->balance = $user->balance;
        return $user;
    }

    public function index()
    {
        $users = User::orderBy('id', 'desc')->paginate(10);
        foreach ($users as $user) {
            $user->fillForJs();
        }
        return $users;
    }

    public function saveAvatar(Request $request)
    {
        $user    = $request->user();
        $hasFile = $request->hasFile('avatar');
        if ($hasFile) {
            $file        = $request->file('avatar');
            $extension   = $file->getClientOriginalExtension();
            $imageStream = file_get_contents($file->getRealPath());
        } else {
            $avatar = $request->get('avatar');
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $avatar, $res)) {
                //base64图像
                $extension     = $res[2];
                $base64_string = str_replace($res[1], '', $avatar);
                $imageStream   = base64_decode($base64_string);
            }
        }

        $fileTemplate = 'avatar-%s.%s'; //以后所有cos的头像保存文件名模板
        $storePrefix  = '/storage/app/avatars/'; //以后所有cos的头像保存位置就这样了

        $filename = time();
        if (!is_prod_env()) {
            $filename = $user->id . "_test"; //测试不覆盖线上cos文件
        }
        $avatarPath  = sprintf($storePrefix . $fileTemplate, $filename, $extension);
        $storeStatus = Storage::cloud()->put($avatarPath, $imageStream);
        if ($storeStatus) {
            $user->update([
                'avatar' => $avatarPath,
            ]);
        }
        return $user->avatar;
    }

    public function saveBackground(Request $request)
    {
        $user = $request->user();
        $file = $request->file('background');
        return $user->save_background($file);
    }

    public function save(Request $request)
    {
        $user = $request->user();
        $user->update($request->all());
        return $user;
    }

    public function editors(Request $request)
    {
        $auth_user = $request->user();
        //获取我关注的人
        $followUserIds = DB::table('follows')->where('user_id', $auth_user->id)
            ->where('followable_type', 'users')
            ->pluck('followable_id')->toArray();

        $followUserIds = array_unique($followUserIds);

        $users = User::whereIn('id', $followUserIds)->select('name', 'id')->paginate(100);

        //$users = User::orderBy('id', 'desc')->select('name', 'id')->paginate(100);
        return $users;
    }

    public function recommend(Request $request)
    {
        $page_size = 5;

        $hasLogin  = Auth::guard('api')->check();
        $loginUser = $hasLogin ? Auth::guard('api')->user() : null;
        $qb        = User::where('role_id', 1);

        if ($hasLogin) {
            $qb = $qb->where('id', '!=', $loginUser->id);
        }
        $users = User::orderByDesc('updated_at')
            ->exclude(['gold', 'count_posts'])->paginate($page_size);

        //当编辑和签约作者不足的时候 填充普通用户
        if ($num = $page_size - $users->count()) {
            $page = $request->get('page');

            $recommendUser = User::whereNotIn('id', $users->pluck('id'))
                ->orderByDesc('updated_at')
                ->paginate($num);

            $users = $users->merge($recommendUser);

            //当用户不足的时候 随机取用户
            if ($users->count() == 0) {
                $users = User::inRandomOrder()->take($page_size)->get();
            }

            $users = new LengthAwarePaginator($users, $users->count(), $users->count(), $page);
        }

        foreach ($users as $user) {
            if ($hasLogin) {
                $user->is_followed = $loginUser->isFollow('users', $user->id);
            }
        }
        return $users;
    }

    public function login(Request $request)
    {
        if (Auth::attempt([
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        ])) {
            return Auth::user();
        }
        return null;
    }

    public function register(Request $request)
    {
        $data = [
            'name'     => $request->get('name'),
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        ];
        // if (!str_contains($data['email'], '@')) {
        //     return 'email format incorrect';
        // }
        if (strlen($data['password']) < 6) {
            return 'password too short';
        }
        $user = User::firstOrNew([
            'email' => $data['email'],
        ]);
        if ($user->id) {
            throw new \Exception('Email already exists');
        }
        $user->name     = $data['name'];
        $user->account  = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->avatar   = '/images/avatar-' . rand(1, 15) . '.jpg';
        $user->save();
        return $user;
    }

    public function unreads(Request $request)
    {
        return $request->user()->unreads();
    }

    public function articles(Request $request, $id)
    {
        $query = Article::with('category')->where('user_id', $id)->orderBy('id', 'desc');
        if ($request->get('title')) {
            $query = $query->where('title', 'like', '%' . $request->get('title') . '%');
        }
        $articles = $query->paginate(12);
        foreach ($articles as $article) {
            $article->fillForJs();
        }
        return $articles;
    }

    public function videos(Request $request, $id)
    {
        $query = Video::where('user_id', $id)
            ->orderBy('id', 'desc');

        //搜索视频
        if ($request->get('title')) {
            $query = $query->where('title', 'like', '%' . $request->get('title') . '%');
        }

        $videos = $query->paginate(12);
        foreach ($videos as $video) {
            $video->fillForJs();
        }
        return $videos;
    }

    public function images(Request $request, $id)
    {
        $query = Image::where('user_id', $id)->where('count', '>', 0)->orderBy('updated_at', 'desc');
        if ($request->get('title')) {
            $query = $query->where('title', 'like', '%' . $request->get('title') . '%');
        }
        $images = $query->paginate(12);
        foreach ($images as $image) {
            $image->fillForJs();
        }
        return $images;
    }

    public function name(Request $request, $name)
    {
        $user = User::where('name', $name)->first();
        return $user;
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->fillForJs();
        $data['user'] = $user;

        return $data;
    }

    public function follows(Request $request, $id)
    {
        $data = null;
        $user = User::findOrFail($id);
        if (ajaxOrDebug() && $request->get('followings')) {
            $data = smartPager($user->followingUsers()->orderBy('id', 'desc'), 10);
            foreach ($data as $follows) {
                $follows->user                   = $follows->followed;
                $follows->user->avatar           = $follows->user->avatarUrl;
                $follows->user->count_followings = $follows->user->followingUsers()->count();
                $follows->user->is_follow        = is_follow('users', $follows->user->id);
            }
            return $data;
        }

        if (ajaxOrDebug() && $request->get('followers')) {
            $data = smartPager($user->follows()->orderBy('id', 'desc'), 10);
            foreach ($data as $followUser) {
                $followUser->user->avatar           = $followUser->user->avatarUrl;
                $followUser->user->count_followings = $followUser->user->followingUsers()->count();
                $followUser->user->is_follow        = is_follow('users', $followUser->user->id);
            }
            return $data;
        }
        return $data;
    }

    /**
     * @Author   XXM
     * @DateTime 2018-07-31
     * @param Request $request
     * @param    [user]     $id
     * @return
     */
    public function relatedVideos(Request $request, $id)
    {
        $user = User::findOrFail($id);

        //跳过当前的
        $video_id = $request->video_id;
        $num      = $request->get('num') ? $request->get('num') : 10;
        $posts    = Post::with('video')->where('user_id', $user->id)->where('video_id', '<>', $video_id)->paginate($num);
        if (count($posts) < 1) {
            $posts = Post::inRandomOrder()->with('video')->paginate($num);
        }
        foreach ($posts as $post) {
            $post->fillForJs();
        }
        return $posts;
    }

    /**
     * @Author      XXM
     * @DateTime    2018-09-22
     * @description            [返回你关注的和关注你的用户]
     * @return      [users]     [description]
     */
    public function relatedUsers()
    {
        $user = getUser();

        //如果是编辑则返回所有用户
        if ($user->is_editor) {
            return $users = User::select(['id', 'name'])->orderBy('updated_at', 'desc')->get()->toArray();
        }

        $followUsers = $user->followingUsers()->pluck('followable_id')->toArray();
        $userFans    = $user->follows()->pluck('user_id')->toArray();

        $user_ids = array_merge($followUsers, $userFans);
        $user_ids = array_unique($user_ids);

        $users = User::whereIn('id', $user_ids)->select(['id', 'name'])->get()->toArray();
        return $users;
    }
}
