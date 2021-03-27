@extends('layouts.app')
@section('title')
    页面找不到 - {{ config("app.name_cn") }}
@endsection

@php
    $extra['categories'] = [];
    $extra['articles'] = []; //TODO: 真的要404的时候做推荐？
@endphp

@section('content')
<div class="container">
    <div class="jumbotron">
        <div class="container error">
            <img src="/images/404.png" alt="">
            <div class="info">
                <h2>很抱歉，你访问的页面不存在</h2>
                <p class="state">输入地址有误或该地址已被删除，你可以<a class="return" href="/">
                    返回首页
                </a>，也可以尝试我们为你推荐的有趣内容。</p>
                <i class="iconfont icon-icon-test"></i>
            </div>
        </div>
    </div>
</div>
@endsection
