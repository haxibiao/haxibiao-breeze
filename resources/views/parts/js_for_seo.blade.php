{{-- 这些代码不应该强制写入blade,让CMS系统站长管理临时配置才对 --}}

{{-- 360自动收录 --}}
{{-- <script>
    (function() {
        var src = "https://jspassport.ssl.qhimg.com/11.0.1.js?d182b3f28525f2db83acfaaf6e696dba";
        document.write('<script src="' + src + '" id="sozz"><\/script>');
    })();
</script> --}}

{{-- 百度推送 --}}
{{-- <script>
	(function() {
		var canonicalURL, curProtocol;
		//Get the <link> tag
		var x = document.getElementsByTagName("link");
		//Find the last canonical URL
		if (x.length > 0) {
			for (i = 0; i < x.length; i++) {
				if (x[i].rel.toLowerCase() == 'canonical' && x[i].href) {
					canonicalURL = x[i].href;
				}
			}
		}
		//Get protocol
		if (!canonicalURL) {
			curProtocol = window.location.protocol.split(':')[0];
		} else {
			curProtocol = canonicalURL.split(':')[0];
		}
		//Get current URL if the canonical URL does not exist
		if (!canonicalURL) canonicalURL = window.location.href;
		//Assign script content. Replace current URL with the canonical URL
		! function() {
			var e = /([http|https]:\/\/[a-zA-Z0-9\_\.]+\.baidu\.com)/gi,
				r = canonicalURL,
				t = document.referrer;
			if (!e.test(r)) {
				var n = (String(curProtocol).toLowerCase() === 'https') ?
					"https://sp0.baidu.com/9_Q4simg2RQJ8t7jm9iCKT-xh_/s.gif" : "//api.share.baidu.com/s.gif";
				t ? (n += "?r=" + encodeURIComponent(document.referrer), r && (n += "&l=" + r)) : r && (n += "?l=" +
					r);
				var i = new Image;
				i.src = n
			}
		}(window);
	})();
</script> --}}