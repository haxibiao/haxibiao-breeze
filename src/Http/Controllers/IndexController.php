<?php

namespace Haxibiao\Breeze\Http\Controllers;

use App\Version;
use Haxibiao\Content\Article;
use Illuminate\Support\Carbon;

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
        $builder = Version::where('os', 'Android')->orderByDesc('id');

        if (is_prod_env()) {
            $builder = $builder->where('type', 1);
        }

        $version = $builder->get();

        $array   = $version->toArray();
        $verions = array_map(static function ($item) {
            $createdAt = Carbon::parse($item['created_at']);
            return array(
                'name'        => $item['name'],
                'url'         => $item['url'],
                'is_force'    => $item['is_force'],
                'description' => $item['description'],
                'size'        => formatSizeUnits($item['size']),
                'package'     => $item['package'],
                'created_at'  => (string) $createdAt->toDateString(),
            );
        }, $array);

        return view('app')->with('data', $verions);
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

        //用created_at比updated_at更好些，经常数据改动又会把几年前的article排上来
        if (request('type') == 'thirty') {
            $articles = $qb->where('created_at', '>', Carbon::now()->addDays(-30))
                ->paginate(10);
        } else if (request('type') == 'seven') {
            $articles = $qb
                ->where('created_at', '>', Carbon::now()->addDays(-7))
                ->paginate(10);
        }
        //经典热门
        if (isset($articles)) {
            $articles = $qb->paginate(10);
        }

        return view('index.trending')->with('articles', $articles);
    }

    public function show()
    {
        return 404;
    }
}
