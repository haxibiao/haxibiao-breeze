<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	{{-- pwa icons --}}
	@pwa

    {{-- icon --}}
    <link rel="icon" type="image/png" href="{{ small_logo() }}" sizes="60*60">
    <link rel="icon" type="image/png" href="{{ web_logo() }}" sizes="120*120">
    <link rel="apple-touch-icon" href="{{ touch_logo() }}" sizes="160*160">
    <link href="{{ small_logo() }}" rel="icon" type="image/x-ico">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {!! get_seo_meta() !!}

    <title>@yield('title') {{ seo_site_name() }} @yield('sub_title')</title>

    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="keywords" content="@yield('keywords'), {{ seo_site_name() }} ">
    <meta name="description" content="{{ seo_site_name() }} - @yield('description')  ">
    @stack('seo_metatags')
    @stack('seo_og_result')

    <!-- Styles -->
    <link href="{{ breeze_mix('/css/breeze.css') }}" rel="stylesheet">

    @stack('css')

	@include('parts.js_for_push')

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
            @stack('side_tool')
        </div>

        @stack('modals')

		{{--  苹果pwa提示安装  --}}
		<ios-pwa-prompt
			app-name={{ seo_site_name() }}
			logo={{ small_logo() }}
			v-bind:delay="3000"
			v-bind:prompt-on-visit="1"
			v-bind:times-to-show="10"
			v-bind:permanently-hide-on-dismiss="false"
		/>

    </div>

    <!-- Scripts -->
    @if (Auth::check())
        <script type="text/javascript">
            window.appName = '{{ seo_site_name() }}';
			window.app = '{{ env('APP_NAME') }}';
			window.haxiyun_endpoint = '{{ env('HAXIYUN_ENDPOINT','https://media.haxibiao.com') }}'
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
    <script type="text/javascript">
        window.csrf_token = '{{ csrf_token() }}';

    </script>

    <script src="{{ breeze_mix('/js/breeze.js') }}"></script>

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>


    @stack('scripts')
    @stack('js')
    <div class="container">
        @include('parts.footer')
    </div>

    {!! cms_seo_js() !!}
</body>

</html>
