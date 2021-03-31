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
		/**
		 * 暂时硬编码了，对用户关闭深圳哈希坊下八个站点的视频/电影播放模块
		 * https://pm.haxifang.com/browse/HXB-288
		 */
		$isRecording = in_array(get_domain(),['dongdianhai.com','dongshengyin.com','tongjiuxiu.com','dongtaolu.cn','dongdezhuan.com','dongyundong.com','dongwuli.com']);
    	if($isRecording && !is_crawler()){
			return view('app');
		}
        if (isRecording() && !is_crawler()) {
            return view('app');
        }

        $data = (object) [];

        //置顶 - 电影 置顶优先原则（可无置顶）
        $data->movies = cmsTopMovies();

        //置顶 - 专题
        $data->categories = cmsTopCategories();

        //置顶 合集视频
        $data->videoPosts = cmsTopVideos();

        //首页文章 - 可置顶部分优质文章避免首页脏乱数据
        $data->articles = cmsTopArticles();

        $data->carousel = get_top_articles();

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
            ->whereIn('type', ['diagrams', 'articles', 'article'])
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
