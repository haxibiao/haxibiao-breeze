<!DOCTYPE html>
<html lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="description" content="">
        <meta name="keywords" content="">
        <!--[if IE]><link rel="icon" href="/favicon.ico" /><![endif]-->
        <link rel="stylesheet" href="/css/normalize.css">
        <title>juhaokan</title>
        <script src="https://g.alicdn.com/jssdk/u-link/index.min.js"></script>
        <script src="/js/hls.min.js"></script>
        <link href="/css/chunk-032d70ef.7606d749.css" rel="prefetch">
        <link href="/css/chunk-22e83e04.a8486ef6.css" rel="prefetch">
        <link href="/css/chunk-57ff2109.36f5835a.css" rel="prefetch">
        <link href="/css/chunk-68427c76.ef4bacf1.css" rel="prefetch">
        <link href="/css/chunk-6924ec1c.cb34446e.css" rel="prefetch">
        <link href="/css/chunk-9df6817a.c2da87e5.css" rel="prefetch">
        <link href="/css/chunk-a92a67b2.5d198126.css" rel="prefetch">
        <link href="/css/chunk-ddd24742.03fda693.css" rel="prefetch">
        <link href="/css/chunk-feb8fc14.c68b0b04.css" rel="prefetch">
        <link href="/js/chunk-032d70ef.f3f05daa.js" rel="prefetch">
        <link href="/js/chunk-22e83e04.1eb3a695.js" rel="prefetch">
        <link href="/js/chunk-57ff2109.ae49eeb5.js" rel="prefetch">
        <link href="/js/chunk-68427c76.6e89efed.js" rel="prefetch">
        <link href="/js/chunk-6924ec1c.d448f722.js" rel="prefetch">
        <link href="/js/chunk-9df6817a.25980e7b.js" rel="prefetch">
        <link href="/js/chunk-a92a67b2.e2bb2d32.js" rel="prefetch">
        <link href="/js/chunk-ddd24742.a9ca69a2.js" rel="prefetch">
        <link href="/js/chunk-feb8fc14.d9df1ba5.js" rel="prefetch">
        <link href="/css/app.dad1b38b.css" rel="preload" as="style">
        <link href="/js/app.7026655a.js" rel="preload" as="script">
        <link href="/js/chunk-vendors.1d76a2e8.js" rel="preload" as="script">
        <link href="/css/app.dad1b38b.css" rel="stylesheet">
        <link rel="icon" type="image/png" sizes="32x32" href="/img/icons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/img/icons/favicon-16x16.png">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#FF0A54">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="white">
        <meta name="apple-mobile-web-app-title" content="剧好看">
        <link rel="apple-touch-icon" href="/img/icons/apple-touch-icon-152x152.png">
        <link rel="mask-icon" href="/img/icons/safari-pinned-tab.svg" color="#FF0A54">
        <meta name="msapplication-TileImage" content="/img/icons/msapplication-icon-144x144.png">
        <meta name="msapplication-TileColor" content="#FFFFFF">
    </head>
    <body>
        <noscript><strong>We're sorry but juhaokan doesn't work properly without JavaScript enabled. Please enable it to continue.</strong></noscript>
        <div id="app"></div>
        <script src="/js/chunk-vendors.1d76a2e8.js"></script>
        <script src="/js/app.7026655a.js"></script>
    </body>
    @if(!Agent::isRobot()) @if($matomo_url = config('matomo.matomo_url'))
        <script type="text/javascript">
            var _paq = window._paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u = "{{ $matomo_url }}/";
                _paq.push(['setTrackerUrl', u + 'matomo.php']);
                _paq.push(['setSiteId', '{{ matomo_site_id() }}']);
                var d = document,
                    g = d.createElement('script'),
                    s = d.getElementsByTagName('script')[0];
                g.type = 'text/javascript';
                g.async = true;
                g.defer = true;
                g.src = u + 'matomo.js';
                s.parentNode.insertBefore(g, s);
            })();
        </script>@endif @endif
</html>
