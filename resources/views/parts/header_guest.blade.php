<nav class="navbar navbar-default  navbar-fixed-top" role="navigation">
    <div class="width-limit">
        @section('logo')
            @if (isMobile())
                <a class="logo" href="/" title="{{ seo_site_name() }}">
                    <img src="{{ small_logo() }}" alt="{{ seo_site_name() }}">
                </a>
            @else
                <a class="logo" href="/" title="{{ seo_site_name() }}">
                    <img src="{{ small_logo() }}" alt="{{ seo_site_name() }}">
                </a>
            @endif
        @show

        {{-- 问答，创作模块 --}}
        {{-- @if (starts_with(request()->path(), 'question'))
                <div class="ask"><a href="/login" data-toggle="modal" class="btn-base btn-theme"><span class="iconfont icon-maobi hidden-xs"></span>提问</a></div>
            @else
                <div class="creation hidden-xs"><a href="/write" class="btn-base btn-theme"><span class="iconfont icon-maobi"></span>发布</a></div>
            @endif --}}

        <a href="{{ request()->path() == 'app' ? '#tancen' : '/app' }}" class="download-app">
            <p>下载APP</p>
        </a>

        {{-- 备案模式隐藏 --}}
        @if (!isRecording())
            {{-- 登录注册界面避免重复 --}}
            @if (!isset($auth))
                <a href="/login" class="login btn">登录</a>
            @endif
        @endif

        {{-- <a class="search">
				<search-box is-desktop="{{ isDeskTop() == 1 }}"></search-box>
			</a> --}}
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav">

                    {{-- 备案模式隐藏 --}}
                    @if (!isRecording())
                        <li class="tab {{ get_active_css('/') }}"><a href="/"><i
                                    class="iconfont icon-faxian hidden-xs hidden-md"></i><span
                                    class="hidden-sm">首页</span></a></li>
                        <li class="tab {{ get_active_css('video') }}"><a href="/video"><i
                                    class="iconfont icon-shipin1 hidden-xs hidden-md"></i><span
                                    class="hidden-sm">短视频</span></a></li>
                        @if (config('media.enable.movie', false))
                            <li class="tab {{ get_active_css('movie') }}"><a href="/movie"><i
                                        class="iconfont icon-shipin3 hidden-xs hidden-md"></i><span
                                        class="hidden-sm">长视频</span></a></li>
                        @endif
                        <li class="tab {{ get_active_css('app') }}"><a href="/app"><i
                                    class="iconfont icon-ordinarymobile hidden-xs hidden-md"></i><span
                                    class="hidden-sm">下载App</span></a></li>
                    @endif

                </ul>

                {{-- 备案模式隐藏 --}}
                @if (!isRecording())
                    <a class="search" toogle="true" style="padding-right: 5px">
                        <search-box is-desktop="{{ isDeskTop() }}"></search-box>
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>
