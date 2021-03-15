@php
    $small_logo = 'https://diudie-1251052432.cos.ap-guangzhou.myqcloud.com/web/public/logo/' . get_domain() . '.small.png';
    $web_logo   = 'https://diudie-1251052432.cos.ap-guangzhou.myqcloud.com/web/public/logo/' . get_domain() . '.web.png';
​    $touch_logo = 'https://diudie-1251052432.cos.ap-guangzhou.myqcloud.com/web/public/logo/' . get_domain() . '.touch.png';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ $small_logo }}" sizes="60*60">
    <link rel="icon" type="image/png" href="{{ $web_logo }}" sizes="120*120">
    <link rel="apple-touch-icon" href="{{ $touch_logo }}" sizes="160*160">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> @yield('title') {{ seo_site_name() }} </title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="keywords" content=" @yield('keywords'), {{ seo_site_name() }} ">
    <meta name="description" content=" @yield('description'), {{ seo_site_name() }} ">

    <!-- Styles -->
    <link href="{{ asset('/vendor/breeze/css/guest.css') }}" rel="stylesheet">
    <link href="{{ asset('/vendor/breeze/css/editor.css') }}" rel="stylesheet">
    <style>
        html,body {
            width: 100%;
            height: 100%;
            overflow-y: unset;
        }
        #app {
            width: 100%;
            height: 100%;
            padding: 0 !important;
        }
    </style>
    @stack('css')

</head>
<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Scripts -->
    @if(Auth::check())
    <script type="text/javascript">
        window.appName = '{{ seo_site_name() }}';
        window.tokenize =　 function(api_url){
            var api_token = '{{ Auth::user()->api_token }}'
            if(api_url.indexOf('?') === -1) {
                api_url += '?api_token=' + api_token;
            } else {
                api_url += '&api_token=' + api_token;
            }
            return api_url;
        };
    </script>
    @endif
    <script src="{{ asset('/vendor/breeze/js/write.js') }}"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @stack('scripts')
    @stack('js')

</body>
</html>
