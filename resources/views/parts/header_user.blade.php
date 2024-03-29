<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
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
        @if (starts_with(request()->path(), 'question'))
            <div class="ask"><a data-target=".modal-ask-question" data-toggle="modal"
                    class="btn-base btn-theme"><span class="iconfont icon-maobi hidden-xs"></span>提问</a></div>
        @else
            <div class="user" data-hover="dropdown">
                <div class="creation hidden-xs"><a class="btn-base btn-theme"><span
                            class="iconfont icon-icon-feixingmanyou"></span>发布</a></div>
                <ul class="dropdown-menu hover-dropdown-menu">
                    <li>
                        <a href="/write"><i class="iconfont icon-maobi"></i>文章</a>
                    </li>
                    <li>
                        <a data-target=".modal-post" data-toggle="modal">
                            <i class="iconfont icon-shangchuan"></i><span>动态</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
        <a class="download-app" href="{{ request()->path() == 'app' ? '#tancen' : '/app' }}">
            <p>下载APP</p>
        </a>
        <div class="user" data-hover="dropdown">
            <a><img class="avatar" src="{{ Auth::user()->avatarUrl }}" alt=""><i
                    class="iconfont icon-xiangxiajiantou"></i></a>
            <ul class="dropdown-menu hover-dropdown-menu">
                @editor
                <li>
                    <a href="/home">
                        <i class="iconfont icon-wendangxiugai"></i><span>编辑面板</span>
                    </a>
                </li>
                @endeditor

                <li>
                    <a href="/user/{{ Auth::user()->id }}">
                        <i class="iconfont icon-yonghu01"></i><span>我的主页</span>
                    </a>
                </li>
                <li>
                    <a href="/user/{{ Auth::user()->id }}/favorites">
                        <i class="iconfont icon-biaoqian"></i><span>我的收藏</span>
                    </a>
                </li>
                <li>
                    <a href="/user/{{ Auth::user()->id }}/questions">
                        <i class="iconfont icon-svg37"></i><span>我的问答</span>
                    </a>
                </li>
                <li>
                    <a href="/user/{{ Auth::user()->id }}/likes">
                        <i class="iconfont icon-03xihuan"></i><span>我的喜欢</span>
                    </a>
                </li>
                <li>
                    <a href="/wallet">
                        <i class="iconfont icon-qianbao"></i><span>我的钱包</span>
                    </a>
                </li>
                <li>
                    <a href="/settings">
                        <i class="iconfont icon-shezhi"></i><span>我的设置</span>
                    </a>
                </li>
                {{-- <li>
			     <a href="javascript:;">
			       <i class="iconfont icon-svg37"></i><span>帮助与反馈</span>
					 </a>
			   </li> --}}
                <li>
                    <a rel="nofollow" data-method="delete" href="{{ route('logout') }}" onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                        <i class="iconfont icon-tuichu1"></i><span>退出</span>
                    </a>
                </li>
            </ul>
        </div>
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
                    <li class="tab {{ get_active_css('/') }}"><a href="/"><i
                                class="iconfont icon-faxian hidden-xs hidden-md"></i><span
                                class="hidden-sm">发现</span></a></li>
                    <li class="tab {{ get_active_css('video') }}"><a href="/video"><i
                                class="iconfont icon-shipin1 hidden-xs hidden-md"></i><span
                                class="hidden-sm">短视频</span></a></li>
                    @if (config('media.enable.movie', false))
                        <li class="tab {{ get_active_css('video') }}"><a href="/movie"><i
                                    class="iconfont icon-shipin3 hidden-xs hidden-md"></i><span
                                    class="hidden-sm">长视频</span></a></li>
                    @endif
                    <li class="tab {{ get_active_css('follow') }}" class="follow"><a href="/follow"><i
                                class="iconfont icon-huizhang hidden-xs hidden-md"></i><span
                                class="hidden-sm">关注</span></a></li>
                    <li class="tab notification {{ get_active_css('notification') }}" data-hover="dropdown">
                        <a href="/notification"><i class="iconfont icon-zhongyaogaojing hidden-xs hidden-md"></i><span
                                class="hidden-sm">消息</span></a>
                        @php
                            $unreads_all = array_sum(Auth::user()->unreads());
                        @endphp
                        @if ($unreads_all)
                            <span class="badge">{{ $unreads_all }}</span>
                        @endif
                        <ul class="dropdown-menu hover-dropdown-menu hidden-xs">
                            <li>
                                <a href="/notification#/comments"><i class="iconfont icon-xinxi"></i>
                                    <span>评论</span><span
                                        class="badge">{{ Auth::user()->unreads('comments') }}</span></a>
                            </li>
                            <li>
                                <a href="/notification#/chats"><i class="iconfont icon-email"></i> <span>消息</span><span
                                        class="badge">{{ Auth::user()->unreads('chats') }}</span></a>
                            </li>
                            <li>
                                <a href="/notification#/requests"><i class="iconfont icon-tougaoguanli"></i>
                                    <span>投稿请求</span><span
                                        class="badge">{{ Auth::user()->unreads('requests') }}</span></a>
                            </li>
                            <li>
                                <a href="/notification#/likes"><i class="iconfont icon-xin"></i> <span>喜欢和赞</span><span
                                        class="badge">{{ Auth::user()->unreads('likes') }}</span></a>
                            </li>
                            <li>
                                <a href="/notification#/follows"><i class="iconfont icon-jiaguanzhu"></i>
                                    <span>关注</span><span
                                        class="badge">{{ Auth::user()->unreads('follows') }}</span></a>
                            </li>
                            <li>
                                <a href="/notification#/tips"><i class="iconfont icon-zanshangicon"></i>
                                    <span>赞赏</span><span
                                        class="badge">{{ Auth::user()->unreads('tips') }}</span></a>
                            </li>
                            <li>
                                <a href="/notification#/others"><i class="iconfont icon-gengduo"></i>
                                    <span>其他消息</span><span
                                        class="badge">{{ Auth::user()->unreads('others') }}</span></a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <a class="search">
                    <search-box is-desktop="{{ isDeskTop() == 1 }}"></search-box>
                </a>
                <modal-post />
            </div>
        </div>
    </div>
</nav>
