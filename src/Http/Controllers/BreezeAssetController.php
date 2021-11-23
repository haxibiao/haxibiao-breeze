<?php

namespace Haxibiao\Breeze\Http\Controllers;

use DateTime;
use Haxibiao\Breeze\Breeze;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BreezeAssetController extends Controller
{
    /**
     * 前端代码
     */
    public function frontend(Request $request)
    {
        $asset_path = $request->path();
        if (!Str::startsWith($asset_path, '/')) {
            $asset_path = "/" . $asset_path;
        }
        $path = Arr::get(Breeze::allAssets(), $asset_path);
        abort_if(is_null($path), 404);

        $contentType = "text/css";
        $isJs        = Str::contains($path, '.js');
        if ($isJs) {
            $contentType = 'application/javascript';
        }
        return response(
            file_get_contents($path),
            200,
            ['Content-Type' => $contentType]
        )->setLastModified(DateTime::createFromFormat('U', (string) filemtime($path)));
    }

    /**
     * 图片字体资源类
     */
    public function assets(Request $request)
    {
        $asset_path = $request->path();
        if (!Str::startsWith($asset_path, '/')) {
            $asset_path = "/" . $asset_path;
        }
        $path = Arr::get(Breeze::allAssets(), $asset_path);
        abort_if(is_null($path), 404);

        $contentType = "image/jpeg";
        //图片
        $isPng = Str::contains($path, '.png');
        if ($isPng) {
            $contentType = 'image/png';
        }
        if (Str::contains($path, '.svg')) {
            $contentType = 'image/svg+xml';
        }
        //字体
        if (Str::contains($path, '.woff')) {
            $contentType = 'application/font-woff';
        }
        if (Str::contains($path, '.ttf')) {
            $contentType = 'application/x-font-truetype';
        }
        return response(
            file_get_contents($path),
            200,
            ['Content-Type' => $contentType]
        )->setLastModified(DateTime::createFromFormat('U', (string) filemtime($path)));
    }
}
