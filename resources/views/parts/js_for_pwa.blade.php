{{-- 支持替换pwa模板自定义部分 --}}
<script>

{{-- 需要维护单独的pwa后端时覆盖window.gqlUri  --}}
{{-- window.gqlUri = '/gql';  --}}

window.logoUrl = '{{ small_logo() }}';
window.logoTextUrl = '{{ text_logo() }}';
window.apkUrl = '{{ getApkUrl() }}';
window.downloadUrl = '{{ app_download_url() }}';
window.appDomain = '{{ app_domain() }}';

</script>
