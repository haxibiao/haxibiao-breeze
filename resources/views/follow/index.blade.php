@extends('layouts.base')
@section('title')
    关注 -
@stop
@section('content')
    <div id="follow">
        <section class="left-aside clearfix">
            <follow-aside></follow-aside>
            <div class="main">
                <router-view></router-view>
            </div>
        </section>
    </div>

    <!-- Modal -->
    <modal-contribute></modal-contribute>
@endsection
