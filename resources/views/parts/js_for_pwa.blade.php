{{-- 支持替换pwa模板自定义部分 --}}
<script>

{{-- 需要维护单独的pwa后端时覆盖window.gqlUri  --}}
{{-- window.gqlUri = '/gql';  --}}

window.logoUrl = '{{ small_logo() }}';
window.logoIconUrl = '{{ small_logo() }}';
window.logoTextUrl = '{{ text_logo() }}';
window.apkUrl = '{{ getApkUrl() }}';
window.appDomain = '{{ app_domain() }}';
window.downloadUrl = '{{ download_url() }}';
window.appName = '{{ get_app_name() }}';
window.appNameCN = '{{ seo_site_name() }}';
window.appSlogan = '{{ app_slogan() }}';
window.appSchema = '{{ get_app_name() }}';
window.apkPackage = '{{ get_apk_package() }}';
window.apkVersion = '{{ env("APK_VERSION","4.1.0") }}';

</script>

{{-- matomo统计 --}}
@if(!Agent::isRobot())
<script type="text/javascript">
    window.matomoUrl = '{{ matomo_url() }}';
    window.matomoId = '{{ matomo_id() }}';

    var _paq = window._paq || [];
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
        var u = window.matomoUrl;
        _paq.push(['setTrackerUrl', u + 'matomo.php']);
        _paq.push(['setSiteId', window.matomoId]);
        var d = document
            , g = d.createElement('script')
            , s = d.getElementsByTagName('script')[0];
        g.type = 'text/javascript';
        g.async = true;
        g.defer = true;
        g.src = u + 'matomo.js';
        s.parentNode.insertBefore(g, s);
    })();

    window.trackPWAEvent = (name, value = null) => {
        _paq.push(['trackEvent', 'PWA事件', name, value]);
    };

</script>
@endif
