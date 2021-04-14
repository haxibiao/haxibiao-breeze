<div class="aside sm-right hidden-xs">
    <div class="board">
        @include('index.parts.trendings')
        <div class="board-list">
            <a class="question"   href="/issue">
            <span class="board-title">{{ seo_site_name() }}问答<i class="iconfont icon-youbian"></i></span>
            <i class="iconfont icon-changjianwenti board-right"></i>
            </a>
        </div>
    </div>

    {{--  问答分类  --}}
    {{--  @if(isDeskTop())
        <div class="recommend-follower">
            <div class="plate-title">问答分类</div>
            @include('index.parts.recommend_questions')
        </div>
    @endif  --}}

    {{-- 日报 --}}
    {{-- @include('index.parts.daily') --}}
    {{-- 推荐作者 --}}
    <recommend-authors is-login="{{ Auth::check() ? true : false }}"></recommend-authors>

    {{-- 下载APP --}}
    @include('index.parts.download_app')
</div>
