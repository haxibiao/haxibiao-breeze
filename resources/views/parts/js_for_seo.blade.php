{{-- GA统计 --}}
@if ($ga_id = neihan_ga_measure_id())
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        console.log('{{ $ga_id }}', '{{ $ga_id }}');
        gtag('config', '{{ $ga_id }}');
    </script>
@endif

{{-- 百度统计 https://tongji.baidu.com/ --}}
@if ($baidu_id = baidu_id())
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?{{ $baidu_id }}";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
@endif

{{-- 腾讯统计 --}}
@if ($tencent_appid = neihan_tencent_app_id())
    <script>
        var _mtac = {};
        (function() {
            var mta = document.createElement("script");
            mta.src = "//pingjs.qq.com/h5/stats.js?v2.0.4";
            mta.setAttribute("name", "MTAH5");
            mta.setAttribute("sid", '{{ $tencent_appid }}');
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(mta, s);
        })();
    </script>
@endif
