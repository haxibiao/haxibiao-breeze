<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
register_routes(dirname(__FILE__) . '/api');

// Route::post('/live/screenShots', 'Api\LiveController@screenShots');
// Route::post('/live/cutOut', 'Api\LiveController@cutOutLive');
// Route::post('/live/recording', 'Api\LiveController@recording');

Route::middleware('auth:api')->post('/background', 'UserController@saveBackground');

Route::post('/withdraw', 'WithdrawController@withdraws');
Route::any('dimension/data', 'DimensionDataController@index');
