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
    public function show(Request $request)
    {
        $asset_path = $request->path();
        if (!Str::startsWith($asset_path, '/')) {
            $asset_path = "/" . $asset_path;
        }
        $path = Arr::get(Breeze::allAssets(), $asset_path);
        abort_if(is_null($path), 403);

        $contentType = "image/jpeg";
        $isJs        = Str::contains($path, '.js');
        if ($isJs) {
            $contentType = 'application/javascript';
        }
        $isCss = Str::contains($path, '.css');
        if ($isCss) {
            $contentType = 'text/css';
        }

        //images
        $isPng = Str::contains($path, '.png');
        if ($isPng) {
            $contentType = 'image/png';
        }
        if (Str::contains($path, '.svg')) {
            $contentType = 'image/svg+xml';
        }

        //fonts
        if (Str::contains($path, '.woff')) {
            $contentType = 'application/font-woff';
        }
        if (Str::contains($path, '.ttf')) {
            $contentType = 'application/x-font-truetype';
        }

        //FIXME: 用完善的 后缀+contentType 表

        return response(
            file_get_contents($path),
            200,
            [
                'Content-Type' => $contentType,
            ]
        )->setLastModified(DateTime::createFromFormat('U', (string) filemtime($path)));
    }
}
