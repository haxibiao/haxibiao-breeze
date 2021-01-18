<?php

use Illuminate\Support\Facades\Route;

// api routes.
Route::group(
    [
        'prefix'     => 'api',
        'middleware' => ['api'],
        'namespace'  => 'Haxibiao\Breeze\Http',
    ],
    __DIR__ . '/routes/api.php'
);

// web routes.
Route::group(
    [
        'middleware' => ['web'],
        'namespace'  => 'Haxibiao\Breeze\Http\Controllers',
    ],
    __DIR__ . '/routes/web.php'
);
