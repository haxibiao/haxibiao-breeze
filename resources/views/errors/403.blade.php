@extends('layouts.app')

@section('title')
    权限不足 - {{ config("app.name_cn") }}
@endsection

@section('content')
<div class="container">
    <div class="jumbotron">
        <div class="container">
            <h1>
                权限不足
            </h1>
            <p class="lead">
                @if(session('message'))
                    {{ session('message') }}
                @endif
            </p>
            <p class="well">
				@if($exception)
                    {{ $exception->getMessage() }}
                @endif
            </p>
            <p>
                <a class="btn btn-primary btn-lg" href="/">
                    返回首页
                </a>
                <a class="btn btn-primary btn-lg" href="/email/verify">
                    邮箱验证
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
