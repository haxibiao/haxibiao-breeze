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

@if(is_prod_env() && config('breeze.enable_onesignal',false))
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

@if(config('breeze.enable_pushy',false))
	<script src="https://sdk.pushy.me/web/1.0.8/pushy-sdk.js"></script>
	<script>
	// Register visitor's browser for push notifications
	Pushy.register({ appId: '{{ env('PUSHY_APPID') }}' }).then(function (deviceToken) {
		// Print device token to console
		console.log('Pushy device token: ' + deviceToken);

		// Send the token to your backend server via an HTTP GET request
		//fetch('https://your.api.hostname/register/device?token=' + deviceToken);

		// Succeeded, optionally do something to alert the user

		
	}).catch(function (err) {
		// Handle registration errors
		console.error(err);
	});

	// Handle push notifications (only when web page is open)
	Pushy.setNotificationListener(function (data) {
		// Print notification payload data
		console.log('Received notification: ' + JSON.stringify(data));

		// Attempt to extract the "message" property from the payload: {"message":"Hello World!"}
		let message = data.message || 'Test notification';

		// Display an alert with message sent from server
		alert('Received notification: ' + message);
	});
	</script>
@endif