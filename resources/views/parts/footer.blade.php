<footer id="footer">
    {{-- 站点地图 --}}
    @include('parts.sitemap_links')

    {{-- 友情链接 --}}
    {!! seo_friendly_urls() !!}

    <div class="icp">
        @if ($icp = cms_icp_info())
            <div>
                <a target="_blank" href="http://beian.miit.gov.cn/ ">{{ seo_value('备案', 'copyright') }}</a><br>
                <a target="_blank" href="http://beian.miit.gov.cn/ ">{{ seo_value('备案', '备案号') }}
                    邮箱：support@beian.gov.cn</a><br>
                <a target="_blank" href="http://beian.miit.gov.cn/ ">
                    <img src="http://cos.haxibiao.com/images/yyzz.png" alt="电子安全监督">
                    {{ seo_value('备案', '公安网备号') }}
                </a><br>
            </div>
        @endif
        <p>
            本站所有图片和视频均来自互联网收集而来，版权归原创作者所有，本站只提供web页面服务，并不提供资源存储，也不参与录制，上传
        </p>
        <p>
            若本站收录的内容无意侵犯了贵司版权，请发邮件至neihandianying@gmail.com, 我们会在3个工作日内删除侵权内容，谢谢。
        </p>
    </div>
</footer>

@include('parts.js_for_footer')
