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

////注释的原因：RestFul方法废弃了，现在统一用GQL解析抖音视频
//Route::post('/article/resolverDouyin', 'Api\ArticleController@resolverDouyinVideo');
Route::post('/media/import', 'SpiderController@importDouyinSpider');
Route::any('/media/oldHook', 'SpiderController@hook');

Route::middleware('auth:api')->post('/background', 'UserController@saveBackground');

Route::post('/douyin/import', 'SpiderController@importDouYin');

Route::post('/withdraw', 'WithdrawController@withdraws');
