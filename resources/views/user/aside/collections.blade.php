<div class="collection distance">
    <p class="plate-title">{{ $user->ta() }}的文集</p>
    <ul class="icon-text-list">
    	@foreach($user->hasCollections()->orderBy('id','desc')->whereStatus(1)->take(5)->get() as $collection)
        <li><a href="/collection/{{ $collection->id }}"><i class="iconfont icon-wenji"></i> <span>{{ $collection->name }}</span></a></li>
        @endforeach
    </ul>
</div>
