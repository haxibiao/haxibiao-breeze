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
        $isJs = Str::contains($path, '.js');

        abort_if(is_null($path), 404);

        return response(
            file_get_contents($path),
            200,
            [
                'Content-Type' => $isJs ? 'application/javascript' : 'text/css',
            ]
        )->setLastModified(DateTime::createFromFormat('U', (string) filemtime($path)));
    }
}
