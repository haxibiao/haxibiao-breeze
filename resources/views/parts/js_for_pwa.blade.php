{{-- 支持替换pwa模板自定义部分 --}}
<script>
    window.logoUrl = '{{ small_logo() }}';
    window.logoTextUrl = '{{ text_logo() }}';
    window.apkUrl = '{{ getApkUrl() }}';
    window.downloadUrl = '{{ app_download_url() }}';
    window.appDomain = '{{ app_domain() }}';

</script>
