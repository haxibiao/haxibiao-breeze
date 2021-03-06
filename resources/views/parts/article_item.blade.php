@if($article)
    <li class="content-item {{ $article->cover ? 'have-img' : '' }}">
      @if($article->cover)
        <a class="wrap-img" href="{{ $article->url }}"    >
            <img src="{{ $article->cover }}" alt="{{$article->subject}}">
            @if( $article->type=='video' )
            <span class="rotate-play">
              <i class="iconfont icon-shipin"></i>
            </span>
            @endif
            @if( $article->type=='video' && $article->video )
            <i class="duration">@sectominute($article->video->duration)</i>
            @endif
        </a>
      @endif
      <div class="content">
        @if( $article->user && $article->type!=='article' )
        <div class="author">
          <a class="avatar"   href="/user/{{ $article->user->id }}">
            <img src="{{ $article->user->avatarUrl }}" alt="">
          </a>
          <div class="info">
            <a class="nickname"   href="/user/{{ $article->user->id }}">{{ $article->user->name }}</a>
            @if($article->user->is_signed)
              <img class="badge-icon" src="/images/signed.png" data-toggle="tooltip" data-placement="top" title="{{ seo_site_name() }}签约作者" alt="">
            @endif
            @if($article->user->is_editor)
              <img class="badge-icon" src="/images/editor.png" data-toggle="tooltip" data-placement="top" title="{{ seo_site_name() }}小编" alt="">
            @endif
            <span class="time">{{ $article->updated_at }}</span>
          </div>
        </div>
        @endif

        {{-- 如果是文章，就显示标题 --}}
        <a class="title"   href="{{ $article->url }}">
            <span>{{ $article->subject }}</span>
        </a>

        {{-- 然后任何类型，这段简介是一定要显示的 --}}
        <a class="abstract"   href="{{ $article->url }}">
          {{ $article->summary }}
        </a>

        <div class="meta">
          @if($article->category)
            <a class="category"   href="/category/{{ $article->category->id }}">
              <i class="iconfont icon-zhuanti1"></i>
              {{ $article->category->name }}
            </a>
          @endif
          @if( $article->user && $article->type=='article' )
            <a class="nickname"   href="/user/{{ $article->user->id }}">{{ $article->user->name }}</a>
          @endif
          <a   href="{{ $article->url }}">
            <i class="iconfont icon-liulan"></i> {{ $article->hits }}
          </a>
          <a   href="{{ $article->url }}/#comments">
            <i class="iconfont icon-svg37"></i> {{ $article->count_replies }}
          </a>
          <span><i class="iconfont icon-03xihuan"></i> {{ $article->count_likes }} </span>
          @if($article->count_tips)
            <span class="hidden-xs" ><i class="iconfont icon-qianqianqian"></i> {{ $article->count_tips }}</span>
          @endif
        </div>
      </div>
    </li>
@endif
