@php
    $qrcode_url = 'https://diudie-1251052432.cos.ap-guangzhou.myqcloud.com/web/public/storage/qrcode.' . get_domain() . '.jpg';
@endphp
<div class="download-app">
<a class="app" href="app" data-toggle="popover" data-placement="top" data-html="true" data-trigger="hover"
data-content="<img src='{{ $qrcode_url) }}'/>">
  <img src={{ $qrcode_url }} alt="下载{{ seo_site_name() }}手机App" />
  <div class="app-info">
    <div class="down">下载{{ seo_site_name() }}App<i class="iconfont icon-youbian"></i></div>
    <div class="describe">随时随地看{{ seo_site_name() }}</div>
  </div>
</a>
</div>
