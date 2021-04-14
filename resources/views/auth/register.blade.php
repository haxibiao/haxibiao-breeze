@extends('layouts.app')

@section('title')注册 - @stop

@section('content')
    @include('parts.header_guest', ['auth' => true])
    <div id="login" style="padding-top:20px">
        {{--  <div class="logo">
            <a href="/">
                <img src="{{ text_logo() }}" alt="{{ config('app.name') }}">
            </a>
        </div>  --}}

        {{-- 登录注册vue --}}
        <signs register></signs>

        @if($errors->any())
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>出错了！</strong>
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
@stop
