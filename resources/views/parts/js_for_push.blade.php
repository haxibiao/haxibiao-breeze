@if(config('breeze.enable_pushalert',false))
<!-- PushAlert -->
<script type="text/javascript">
		(function(d, t) {
				var g = d.createElement(t),
				s = d.getElementsByTagName(t)[0];
				g.src = "https://cdn.pushalert.co/integrate_{{ env('PUSHALERT_APPID') }}.js";
				s.parentNode.insertBefore(g, s);
		}(document, "script"));
</script>
<!-- End PushAlert -->
@endif

@if(config('breeze.enable_onesignal',false))
	<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
	<script>
		window.OneSignal = window.OneSignal || [];
		OneSignal.push(function() {
			OneSignal.init({
			appId: "{{ env('ONESIGNAL_APPID') }}",
			});
		});
	</script>
@endif