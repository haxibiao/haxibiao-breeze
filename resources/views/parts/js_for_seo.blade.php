{{-- GA统计 --}}
@if ($ga_id = neihan_ga_measure_id())
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga_id }}"></script>
    <script>function gtag(){dataLayer.push(arguments)}window.dataLayer=window.dataLayer||[],gtag("js",new Date),console.log("{{ $ga_id }}","{{ $ga_id }}"),gtag("config","{{ $ga_id }}");</script>
@endif
{{-- 百度统计 https://tongji.baidu.com/ --}}
@if ($baidu_id = baidu_id())
    <script>var _hmt=_hmt||[];!function(){var e=document.createElement("script");e.src="https://hm.baidu.com/hm.js?{{ $baidu_id }}";var t=document.getElementsByTagName("script")[0];t.parentNode.insertBefore(e,t)}();</script>
@endif
{{-- 腾讯统计 --}}
@if ($tencent_appid = neihan_tencent_app_id())
    <script>var _mtac={};!function(){var t=document.createElement("script");t.src="//pingjs.qq.com/h5/stats.js?v2.0.4",t.setAttribute("name","MTAH5"),t.setAttribute("sid","{{ $tencent_appid }}");var e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(t,e)}();</script>
@endif
