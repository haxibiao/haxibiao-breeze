<?php

use Illuminate\Http\Middleware\CheckResponseForModifications;
use Illuminate\Support\Facades\Route;

// Scripts & Styles...
Route::get('/css/{asset}', 'BreezeAssetController@frontend')->middleware(CheckResponseForModifications::class);
Route::get('/js/{asset}', 'BreezeAssetController@frontend')->middleware(CheckResponseForModifications::class);
Route::get('/serviceworker.js', 'BreezeAssetController@frontend')->middleware(CheckResponseForModifications::class);
Route::get('/service-worker.js', 'BreezeAssetController@frontend')->middleware(CheckResponseForModifications::class);

Route::get('/images/{asset}', 'BreezeAssetController@assets')->middleware(CheckResponseForModifications::class);
Route::get('/images/{folder}/{asset}', 'BreezeAssetController@assets')->middleware(CheckResponseForModifications::class);
Route::get('/img/{asset}', 'BreezeAssetController@assets')->middleware(CheckResponseForModifications::class);
Route::get('/img/{folder}/{asset}', 'BreezeAssetController@assets')->middleware(CheckResponseForModifications::class);

//ID默认数字化
Route::pattern('id', '\d+');

//主页
Route::get('/', 'IndexController@index');
//app
Route::get('/app', 'IndexController@app');
Route::get('/about-us', 'IndexController@aboutUs');
Route::get('/trending', 'IndexController@trending');

Route::get('/tag/{name}', 'TagController@tagname');
Route::resource('/tag', 'TagController');

//片段
Route::resource('/snippet', 'SnippetController');

//用户
Route::get('/settings', 'UserController@settings');
Route::get('/user/{id}/videos', 'UserController@videos');
Route::get('/user/{id}/articles', 'UserController@articles');
Route::get('/user/{id}/drafts', 'UserController@drafts');
Route::get('/user/{id}/favorites', 'UserController@favorites');
Route::get('/user/{id}/questions', 'UserController@questions');

Route::get('/user/{id}/likes', 'UserController@likes');
Route::get('/user/{id}/followed-categories', 'UserController@likes');
Route::get('/user/{id}/followed-collections', 'UserController@likes');
Route::get('/user/{id}/followings', 'UserController@follows');
Route::get('/user/{id}/followers', 'UserController@follows');
Route::middleware('auth')->get('/wallet', 'UserController@wallet');
Route::resource('/user', 'UserController');

//dashbord
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/profile', 'HomeController@profile')->name('profile');

//Route::get('/share/collection/{id}', CollectionController::class . '@shareCollection');

//weixin
Route::get('/wechat', 'WechatController@serve');

//qrcode
Route::get('/share/qrcode', 'SharingController@qrcode');

//返回URL二维码
Route::get('/share/qrcode/{url}', 'SharingController@qrcode');

//pwa
Route::get('/manifest.json', 'LaravelPWAController@manifestJson')->name('manifest');
Route::get('/offline/', 'LaravelPWAController@offline');
