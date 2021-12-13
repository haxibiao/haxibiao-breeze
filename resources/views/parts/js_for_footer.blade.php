{{-- matomo 统计 --}}
@if ($matomo_url = matomo_url())
    <script type="text/javascript">var _paq=window._paq||[];_paq.push(["trackPageView"]),_paq.push(["enableLinkTracking"]),function(){_paq.push(["setTrackerUrl","{{ $matomo_url }}/matomo.php"]),_paq.push(["setSiteId","{{ matomo_id() }}"]);var e=document,t=e.createElement("script"),a=e.getElementsByTagName("script")[0];t.type="text/javascript",t.async=!0,t.defer=!0,t.src="https://cdn.jsdelivr.net/gh/breesite/d/matomo.js",a.parentNode.insertBefore(t,a)}();</script>
@endif
@include('parts.js_for_seo')
@include('parts.js_for_vue')
