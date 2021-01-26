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
register_routes(dirname(__FILE__).'/api');



//如果不是生产环境，就按require的方式，不然Unit Test会出现404
// if ($is_testing) {
// 	require 'api/user.php';
//     require 'api/follow.php';
//     require 'api/notification.php';
//     require 'api/issue.php';
//     require 'api/article.php';
//     require 'api/comment.php';
//     require 'api/collection.php';

//     //保存文章相关片段数据
//     require 'api/relation.php';
//     require 'api/app.php';
//     //搜索
//     require 'api/search.php';
// }else{
//     require_once 'api/user.php';
//     require_once 'api/follow.php';
//     require_once 'api/notification.php';
//     require_once 'api/issue.php';
//     require_once 'api/article.php';
//     require_once 'api/comment.php';
//     require_once 'api/collection.php';

//     //保存文章相关片段数据
//     require_once 'api/relation.php';
//     require_once 'api/app.php';
//     //搜索
//     require_once 'api/search.php';
// }

//FIXME: 部分重构到content包里的api 可以清理了

Route::post('/image/upload', 'Api\ImageController@upload');

//收藏
Route::middleware('auth:api')->post('/favorite/{id}/{type}', 'Api\FavoriteController@toggle');
Route::middleware('auth:api')->get('/favorite/{id}/{type}', 'Api\FavoriteController@get');

//获取VOD上传签名
Route::get('/signature/vod-{site}', 'Api\VodController@signature');

// Route::post('/live/screenShots', 'Api\LiveController@screenShots');
// Route::post('/live/cutOut', 'Api\LiveController@cutOutLive');
// Route::post('/live/recording', 'Api\LiveController@recording');

//like赞
Route::middleware('auth:api')->post('/like/{id}/{type}', 'Api\LikeController@toggle');
Route::middleware('auth:api')->get('/like/{id}/{type}', 'Api\LikeController@get');
Route::get('/like/{id}/{type}/guest', 'Api\LikeController@getForGuest');

//图片
Route::get('/image', 'Api\ImageController@index');
Route::middleware('auth:api')->post('/image', 'Api\ImageController@store');
Route::middleware('auth:api')->post('/image/save', 'Api\ImageController@store'); //兼容1.0 or vue上传视频接口

////注释的原因：RestFul方法废弃了，现在统一用GQL解析抖音视频
//Route::post('/article/resolverDouyin', 'Api\ArticleController@resolverDouyinVideo');
Route::post('/media/import', 'Api\SpiderController@importDouyinSpider');
Route::any('/media/oldHook', 'Api\SpiderController@hook');

Route::namespace ('Api')->middleware('auth:api')->group(function () {
    Route::post('/background', 'UserController@saveBackground');
});

Route::post('/douyin/import', 'Api\SpiderController@importDouYin');

Route::post('/withdraw', 'Api\WithdrawController@withdraws');

//Route::any('/movie/history', 'Api\MovieController@movieHistory');
//Route::post('/movie/toggle-like', 'Api\MovieController@toggoleLike');
//Route::post('/movie/toggle-fan', 'Api\MovieController@toggoleFan');
