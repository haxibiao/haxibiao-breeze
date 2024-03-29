<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/logo/{{ env('APP_DOMAIN') }}.small.png" sizes="60*60">
    <link rel="icon" type="image/png" href="/logo/{{ env('APP_DOMAIN') }}.web.png" sizes="120*120">
    <link rel="apple-touch-icon" href="/logo/{{ env('APP_DOMAIN') }}.touch.png">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> @yield('title') {{ config('app.name_cn') }} </title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="keywords" content=" @yield('keywords'), {{ config('app.name_cn') }} ">
    <meta name="description" content=" @yield('description'), {{ config('app.name_cn') }} ">

    <!-- Styles -->
    <link href="{{ breeze_mix('css/breeze.css') }}" rel="stylesheet">

    <style>
        html,
        body {
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
    @stack('scripts')
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
                id: {{ Auth::user()->id }},
                token: '{{ Auth::user()->api_token }}',
                name: '{{ Auth::user()->name }}',
                avatar: '{{ Auth::user()->avatar }}',
                balance: {{ Auth::user()->balance }}
            }
        </script>
    @endif
    <script src="{{ breeze_mix('js/breeze.js') }}"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @stack('js')
</body>

</html>
