{{-- 支持替换vue模板自定义部分 --}}
<script>
    {{-- 需要维护单独的pwa后端时覆盖window.gqlUri --}}
    {{-- window.gqlUri = '/gql'; --}}

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
    window.apkVersion = '{{ env('APK_VERSION', '4.1.0') }}';
</script>
