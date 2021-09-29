@extends('layouts.app')

@section('sub_title') - {{ cms_seo_title() }} @stop

@section('keywords') {{ cms_seo_keywords() }} @stop

@section('description') {{ cms_seo_description() }} @stop

@section('content')

    <div id="index">

        <div class="wrap clearfix">
            {{-- 主要内容 --}}
            <div class="main sm-left">
                {{-- 轮播图 --}}
                @if(isset($data->carousel))
                <div class="poster-container">
                     @include('index.parts.poster', ['items' => $data->carousel])
                </div>
                @endif
                @if(config('media.movie.enable',false))
                    {{-- 最新电影 --}}
                    @include('index.parts.top_movies', ['movies'=>$data->movies])
                @endif
                {{-- 推荐专题 --}}
                @include('index.parts.recommend_categories',['categories'=>$data->categories])
				<recommend-category></recommend-category>

                {{-- top 4 videos --}}
                @include('index.parts.top_videos', ['posts' => $data->posts])

                {{-- 文章列表 --}}
                <ul class="article-list">
                    {{-- 置顶文章 --}}
                    {{-- @if (request('page') < 2)
                        @each('parts.article_item', get_stick_articles('发现'), 'article')
					@endif --}}

                    {{-- 文章 --}}
                    @each('parts.article_item', $data->articles, 'article')

                    {{-- PWA优化，直接VUE体验刷文章 --}}
                    <article-list api="/api/articles" start-page="2" is-desktop="{{ isDeskTop() == 1 }}" />                    

                </ul>
            </div>
            {{-- 侧栏 --}}
            @include('index.parts.side')
        </div>
    </div>

@endsection
