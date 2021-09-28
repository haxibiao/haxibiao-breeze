<?php

use Illuminate\Support\Facades\Route;

// api routes.
Route::group(
    [
        'prefix'     => 'api',
        'middleware' => ['api'],
        'namespace'  => 'Haxibiao\Breeze\Http\Api',
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

// auth routes.
Route::group(
    [
        'middleware' => ['web'],
        'namespace'  => 'Haxibiao\Breeze\Http\Controllers',
    ],
    __DIR__ . '/routes/auth.php'
);

//pwa routes.
Route::group(
    [
        'as'         => 'laravelpwa.',
        'middleware' => ['web'],
        'namespace'  => 'Haxibiao\Breeze\Http\Controllers',
    ],
    __DIR__ . '/routes/web.php'
);
