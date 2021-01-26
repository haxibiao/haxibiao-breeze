<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{--  icon  --}}
    <link rel="icon" type="image/png" href="{{ small_logo() }}" sizes="60*60">
    <link rel="icon" type="image/png" href="{{ web_logo() }}" sizes="120*120">
    <link rel="apple-touch-icon" href="{{ touch_logo() }}" sizes="160*160">
    <link href="{{ small_logo() }}" rel="icon" type="image/x-ico">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {!! get_seo_meta() !!}

    <title> @yield('title') </title>

    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="keywords" content="@yield('keywords'), {{ seo_site_name() }} ">
    <meta name="description" content="{{ seo_site_name() }} - @yield('description')  ">
    @stack('seo_metatags')
    @stack('seo_og_result')
    <!-- Styles -->
    <link href="{{ asset('/vendor/breeze/css/guest.css') }}" rel="stylesheet">
    @if (Auth::check())
        <link href="{{ asset('/vendor/breeze/css/editor.css') }}" rel="stylesheet">
    @endif

    @stack('css')
</head>

<body>
    <div id="app">

        @include('parts.header')

        <div class="container">
            <div class="row">
                @yield('content')
            </div>
        </div>

        @stack('section')

        <div id="side-tool">
            <side-tool></side-tool>
            @stack('side_tool')
        </div>

        @stack('modals')

    </div>

    <!-- Scripts -->
    @if (Auth::check())
        <script type="text/javascript">
            window.appName = '{{ seo_site_name() }}';
            window.tokenize = function(api_url) {
                var api_token = '{{ Auth::user()->api_token }}'
                if (api_url.indexOf('?') === -1) {
                    api_url += '?api_token=' + api_token;
                } else {
                    api_url += '&api_token=' + api_token;
                }
                return api_url;
            };
            window.user = {
                id:{{ Auth::user()->id }},
                name: '{{ Auth::user()->name }}',
                avatar: '{{ Auth::user()->avatar }}',
                balance: {{ Auth::user()->balance }}
            }

        </script>
    @endif
    <script type="text/javascript">
        window.csrf_token = '{{ csrf_token() }}';

    </script>

    @if (in_array(request()->path(), ['follow', 'notification', 'settings']))
        <script src="{{ asset('/vendor/breeze/js/spa.js') }}"></script>
    @else
        <script src="{{ asset('/vendor/breeze/js/app.js') }}?v=1"></script>
    @endif


    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>

    <script src="//imgcache.qq.com/open/qcloud/js/vod/sdk/ugcUploader.js"></script>

    @stack('scripts')
    @stack('js')
    <div class="container">
         @include('parts.footer')
    </div>

{!! cms_seo_js() !!}
</body>
</html>
