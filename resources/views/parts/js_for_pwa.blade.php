@include('parts.js_for_vue')

{{-- matomo统计 --}}
@if (!Agent::isRobot())
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
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.type = 'text/javascript';
            g.async = true;
            g.defer = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
            window.pwaEventTrack = (name, value = null) => {
                _paq.push(['trackEvent', 'PWA事件', name, value]);
            };
        })();
    </script>
@endif
