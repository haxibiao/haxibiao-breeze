@extends('layouts.app')

@section('title') {{ $user->name }} - @stop

@section('description') {{ $user->introduction }} @stop

@section('content')
    <div id="user">
        <div class="clearfix">
            <div class="main sm-left">
                {{-- 用户信息 --}}
                @include('user.parts.information')
                {{-- 内容 --}}
                <div class="content">
                    <!-- Nav tabs -->
                    <ul id="trigger-menu" class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#article" aria-controls="article" role="tab" data-toggle="tab"><i
                                    class="iconfont icon-wenji"></i>作品</a>
                        </li>
                        <li role="presentation">
                            <a href="#actions" aria-controls="actions" role="tab" data-toggle="tab"><i
                                    class="iconfont icon-zhongyaogaojing"></i>动态</a>
                        </li>
                        <li role="presentation">
                            <a href="#comment" aria-controls="comment" role="tab" data-toggle="tab"><i
                                    class="iconfont icon-svg37"></i>最新评论</a>
                        </li>
                        <li role="presentation">
                            <a href="#hot" aria-controls="hot" role="tab" data-toggle="tab"><i
                                    class="iconfont icon-huo"></i>热门</a>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="article-list tab-content">
                        <ul role="tabpanel" class="fade in note-list tab-pane active" id="article">
                            @if (count($data['articles']) == 0)
                                <blank-content></blank-content>
                            @else
                                @each('parts.article_item', $data['articles'], 'article')
                                @if (Auth::check())
                                    <article-list api="/user/{{ $user->id }}?articles=1" start-page="2"
                                        not-empty="{{ count($data['articles']) > 0 }}" />
                                @else
                                    <div>{!! $data['articles']->fragment('article')->links() !!}</div>
                                @endif
                            @endif
                        </ul>
                        {{-- 动态 --}}
                        <ul role="tabpanel" class="fade feed-list tab-pane" id="actions">
                            @if (count($data['actions']) == 0)
                                <blank-content></blank-content>
                            @else
                                @each('user.parts.action_item', $data['actions'], 'action')
                                @if (Auth::check())
                                    <action-list api="/user/{{ $user->id }}?actions=1" start-page="2"
                                        not-empty="{{ count($data['actions']) > 0 }}" app-name="{{ seo_site_name() }}" />
                                @else
                                    <div>{!! $data['actions']->fragment('actions')->links() !!}</div>
                                @endif
                                {{-- 加入时间 --}}
                                <li class="feed-info distance">
                                    <div class="content">
                                        <div class="author">
                                            <a class="avatar" href="/user/{{ $user->id }}">
                                                <img src="{{ $user->avatarUrl }}" alt="">
                                            </a>
                                            <div class="info">
                                                <a class="nickname"
                                                    href="/user/{{ $user->id }}">{{ $user->name }}</a>
                                                {{-- <img class="badge-icon" src="/images/signed.png" data-toggle="tooltip" data-placement="top" title="{{ config('app.name') }}签约作者" alt=""> --}}
                                                <span class="time"> 加入了{{ seo_site_name() }} ·
                                                    {{ $user->created_at }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif

                        </ul>
                        <ul role="tabpanel" class="fade note-list tab-pane" id="comment">
                            @if (count($data['commented']) == 0)
                                <blank-content></blank-content>
                            @else
                                @each('parts.article_item', $data['commented'], 'article')
                                @if (Auth::check())
                                    <article-list api="/user/{{ $user->id }}?commented=1" start-page="2"
                                        not-empty="{{ count($data['commented']) > 0 }}" />
                                @else
                                    <div>{!! $data['commented']->fragment('comment')->links() !!}</div>
                                @endif
                            @endif
                        </ul>
                        <ul role="tabpanel" class="fade note-list tab-pane" id="hot">
                            @if (count($data['hot']) == 0)
                                <blank-content></blank-content>
                            @else
                                @each('parts.article_item', $data['hot'], 'article')
                                @if (Auth::check())
                                    <article-list api="/user/{{ $user->id }}?hot=1" start-page="2"
                                        not-empty="{{ count($data['hot']) > 0 }}" />
                                @else
                                    <div>{!! $data['hot']->fragment('hot')->links() !!}</div>
                                @endif
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            {{-- 侧栏 --}}
            @include('user.parts.aside')
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(function() {
            var url = window.location.href;
            if (url.includes("article")) {
                $("[href='#article']").click();
            }
            if (url.includes("comment")) {
                $("[href='#comment']").click();
            }
            if (url.includes("hot")) {
                $("[href='#hot']").click();
            }
            if (url.includes("actions")) {
                $("[href='#actions']").click();
            }
        });
    </script>
@endpush

@push('modals')
    <modal-blacklist></modal-blacklist>
    <modal-report></modal-report>
@endpush
