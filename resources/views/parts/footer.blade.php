<footer id="footer">
    {{-- 站点地图 --}}
    {{-- @include('parts.sitemap_links') --}}

    {{-- 友情链接 --}}
    {!! seo_friendly_urls() !!}

    <div class="icp">
        {!! cms_icp_info() !!}
        <p>
            本站所有图片和视频均来自互联网收集而来，版权归原创作者所有，本站只提供web页面服务，并不提供资源存储，也不参与录制，上传
        </p>
        <p>
            若本站收录的内容无意侵犯了贵司版权，请发邮件至 {{ 'support@' . get_domain() }}, 我们会在3个工作日内删除侵权内容，谢谢。
        </p>
    </div>
</footer>

@include('parts.js_for_footer')
