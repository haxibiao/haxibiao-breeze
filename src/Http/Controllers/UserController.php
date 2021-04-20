<?php

namespace Haxibiao\Breeze\Http\Controllers;

use App\Article;
use App\Favorite;
use App\Follow;
use App\Issue;
use App\Solution;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['store', 'update', 'destroy', 'settings', 'edit']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$currentPage = request()->get('page',1);
		$perPage     = request()->get('count',24);
		$total       = User::count();

		$users = User::with([
			'articles'=>function($query){
				$query->take(3)->orderBy('id','desc');
			}
		])->skip(($currentPage * $perPage) - $perPage)
			->take($perPage)
			->get();

		$result = new \Illuminate\Pagination\LengthAwarePaginator(
			$users,
			$total,
			$perPage,
			$currentPage,
			['path' => url('user')]
		);

        return view('user.index')->withUsers($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user                   = User::with('articles')->findOrFail($id);
        $user->followUsers      = $user->followingUsers()->count();
        $user->count_production = $user->articles()->count();
        //个人作品 ，个人主页不展示爬取的文章
        //个人发布的文集都有一个主文集，通过has('collection')过滤掉爬取的文章
        $qb = $user->articles()->with('category')
            ->has('collection')
            ->where('status', '>', 0)
            ->orderBy('id', 'desc');
        $articles = smartPager($qb, 10);
        if (ajaxOrDebug() && request('articles')) {
            foreach ($articles as $article) {
                $article->fillForJs();
                $article->time_ago = $article->updatedAt();
            }
            return $articles;
        }
        $data['articles'] = $articles;

        //最新评论
        $qb = $user->articles()->with('category')
            ->where('status', '>', 0)
            ->orderBy('commented', 'desc');
        $articles = smartPager($qb, 10);
        if (ajaxOrDebug() && request('commented')) {
            foreach ($articles as $article) {
                $article->fillForJs();
                $article->time_ago = $article->updatedAt();
            }
            return $articles;
        }
        $data['commented'] = $articles;

        //热门
        $qb = $user->articles()->with('category')
            ->where('status', '>', 0)
            ->orderBy('hits', 'desc');
        $articles = smartPager($qb, 10);
        if (ajaxOrDebug() && request('hot')) {
            foreach ($articles as $article) {
                $article->fillForJs();
                $article->time_ago = $article->updatedAt();
            }
            return $articles;
        }
        $data['hot'] = $articles;

        //动态
        $qb = $user->actions()
            ->with('user')
            ->with('actionable')
            ->where('status', 1)
            ->orderBy('created_at', 'desc');
        $actions = smartPager($qb, 10);
        if (ajaxOrDebug() && request('actions')) {
            foreach ($actions as $action) {
                $action->time = diffForHumansCN($action->created_at);
                $action       = $action->fillForJs();
            }
            return $actions;
        }

        foreach ($actions as $action) {
            if (empty($action->actionable)) {
                continue;
            }
            switch (get_class($action->actionable)) {
                case 'App\Article':
                    # code...
                    break;
                case 'App\Comment':
                    $action = $action->load('actionable.commentable.user');
                    break;
                case 'App\Favorite':
                    $action = $action->load('actionable.favorable.user');
                    break;
                case 'App\Like':
                    $action = $action->load('actionable.likable.user');
                    break;
                case 'App\Follow':
                    if (get_class($action->actionable->followable) == 'App\Category') {
                        $action = $action->load('actionable.followable.user');
                    } else {
                        $action = $action->load('actionable.followable');
                    }
                    break;
            }
        }
        $data['actions'] = $actions;

        //视频
        // $qb = $user->videoPosts()
        //     ->orderBy('updated_at', 'desc');
        // $videos = smartPager($qb, 10);
        // $data['videos'] = $videos;

        app_track_event('用户页', '访问他人主页');

        return view('user.show')
            ->withUser($user)
            ->withData($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('user.edit')->withUser($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());
        $file = $request->file('avatar');
        //判断是否为空
        if (!empty($file)) {
            $user->save_avatar($file);
        }
        return redirect()->to('/user/' . $user->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->status = -1;
            $user->save();
        }
        return redirect()->back();
    }

    public function drafts($id)
    {
        $user             = User::findOrFail($id);
        $data['articles'] = Article::where('user_id', $user->id)->orderBy('id', 'desc')->where('status', 0)->paginate(10);
        return view('user.drafts')
            ->withUser($user)
            ->withData($data);
    }

    public function articles($id)
    {
        $user             = User::findOrFail($id);
        $data['articles'] = Article::where('user_id', $user->id)->orderBy('id', 'desc')->where('status', 1)->paginate(10);
        return view('user.articles')
            ->withUser($user)
            ->withData($data);
    }

    public function videos($id)
    {
        $user               = User::findOrFail($id);
        $data['videoPosts'] = $user->allVideoPosts()->orderBy('id', 'desc')->paginate(10);
        return view('user.videos')
            ->withUser($user)
            ->withData($data);
    }

    public function wallet(Request $request)
    {
        $user         = $request->user();
        $transactions = $user->transactions()->orderBy('id', 'desc')->paginate(10);
        return view('user.wallet')
            ->withUser($user)
            ->withTransactions($transactions);
    }

    public function favorites(Request $request)
    {
        if (checkUser()) {
            $user    = $request->user();
            $movieID = Favorite::where('user_id', $user->id)
                ->where('favorable_type', 'movies')
                ->pluck('favorable_id');
            $movies = \App\Movie::whereIn('id', $movieID)->paginate(12);

            return view('user.favorites', [
                'movies' => $movies,
            ]);
        }

    }
    public function questions(Request $request)
    {
        $user              = Auth::user();
        $data['questions'] = Issue::where('user_id', $user->id)->orderBy('id', 'desc')->paginate(10);

        $issue_ids                = Solution::where('user_id', $user->id)->select('issue_id')->get()->pluck('issue_id');
        $ans                      = Issue::whereIn('id', $issue_ids)->get();
        $data['answer_questions'] = $ans;
        return view('user.questions')->withUser($user)->withData($data);
    }

    public function likes(Request $request, $id)
    {
        $user              = User::findOrFail($id);
        $user->followUsers = $user->followings()->where('followable_type', 'users')->count();

        $data['liked_articles'] = $user->likes()->where('likable_type', '!=', 'movies')
            ->orderBy('id', 'desc')->paginate(10);

        $articles = $data['liked_articles'];

        //load more articles ...
		if (request()->ajax() || request('debug')) {
			$transFormedArticles = collect();
			foreach ($articles as $like){
				$article = $like->likable;
				if(!$article){
					continue;
				}
				$article->fillForJs();
				$article->time_ago = $article->updatedAt();
				$transFormedArticles->push($article);
			}
			return $transFormedArticles;
		}

        $data['followed_categories'] = Follow::with('followable')
            ->where('user_id', $user->id)
            ->where('followable_type', 'categories')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $data['followed_collections'] = Follow::with('followable')
            ->where('user_id', $user->id)
            ->where('followable_type', 'collections')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('user.likes')
            ->withUser($user)
            ->withData($data);
    }

    public function follows(Request $request, $id)
    {
        $user              = User::findOrFail($id);
        $user->followUsers = $user->followingUsers()->count();

        $data['follows']   = $user->followingUsers()->orderBy('id', 'desc')->paginate(10);
        $data['followers'] = $user->followers()->orderBy('id', 'desc')->paginate(10);

        return view('user.follows')
            ->withUser($user)
            ->withData($data);
    }

    public function settings(Request $request)
    {
        $user = $request->user();
        return view('user.settings')->withUser($user);
    }
}
