@if(!Agent::isRobot())

{{-- GA统计 --}}
@if($ga_id = neihan_ga_measure_id())
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

{{-- matomo统计 --}}
@if($matomo_url = matomo_url())
<!-- Matomo -->
<script type="text/javascript">
    var _paq = window._paq || [];
    /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
        var u = "{{ $matomo_url }}/";
        _paq.push(['setTrackerUrl', u + 'matomo.php']);
        _paq.push(['setSiteId', '{{ matomo_id() }}']);
        var d = document
            , g = d.createElement('script')
            , s = d.getElementsByTagName('script')[0];
        g.type = 'text/javascript';
        g.async = true;
        g.defer = true;
        g.src = 'https://cdn.jsdelivr.net/gh/breesite/d/matomo.js';
        s.parentNode.insertBefore(g, s);
    })();

</script>
<!-- End Matomo Code -->
@endif



{{-- 百度统计 https://tongji.baidu.com/ --}}
@if($baidu_id = baidu_id())
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
@if($tencent_appid = neihan_tencent_app_id())
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

@include('parts.js_for_seo')
@endif
