<?php

namespace Haxibiao\Breeze\Http\Controllers;

use Carbon\Carbon;
use Haxibiao\Content\Article;

class IndexController extends Controller
{
    /**
     * 首页数据的全部逻辑
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {

        if (isRecording() && !is_crawler()) {
            return view('app');
        }

        $data = (object) [];

        //置顶 - 或最近有更新的电影
        $data->movies = cmsTopMovies();

        //置顶 - 或最近有更新的专题
        $data->categories = cmsTopCategories();

        //置顶 - 或最近有更新的视频
        $data->posts = cmsTopVideos();

        //置顶 - 或最近有更新的文章
        $data->articles = cmsTopArticles();

        //FIXME: 轮播图，需要运营控制宽屏图片素材
        // $data->carousel = get_top_articles();

        return view('index.index')->with('data', $data);
    }

    public function app()
    {
        return view('app');
    }

    public function aboutUs()
    {
        return view('index.about_us');
    }

    public function trending()
    {
        $articles = [];
        $qb       = Article::orderBy('hits', 'desc')
        // ->whereIn('type', ['diagrams', 'articles', 'article'])
            ->where('status', '>', 0);

        if (request('type') == 'thirty') {
            $articles = $qb->where('updated_at', '>', Carbon::now()->addDays(-30))
                ->paginate(10);
        } else if (request('type') == 'seven') {
            $articles = $qb
                ->where('updated_at', '>', Carbon::now()->addDays(-7))
                ->paginate(10);
        }
        //经典热门
        if (isset($articles)) {
            $articles = $qb->paginate(10);
        }

        return view('index.trending')->with('articles', $articles);
    }
}
