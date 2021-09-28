<?php

namespace Haxibiao\Breeze\Http\Controllers;

use Haxibiao\Breeze\Services\ManifestService;
use Illuminate\Routing\Controller;

class LaravelPWAController extends Controller
{
    public function manifestJson()
    {
        $output = (new ManifestService)->generate();
        return response()->json($output);
    }

    public function offline()
    {
        return view('laravelpwa::offline');
    }
}
