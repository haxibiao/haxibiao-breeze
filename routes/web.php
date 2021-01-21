<?php

use Illuminate\Support\Facades\Route;

//主页
Route::get('/', 'IndexController@index');
//app
Route::get('/app', 'IndexController@app');
Route::get('/about-us', 'IndexController@aboutUs');
Route::get('/trending', 'IndexController@trending');

//搜索
Route::get('/search', 'SearchController@search');
Route::get('/search/users', 'SearchController@searchUsers');
Route::get('/search/article', 'SearchController@searchArticles');
Route::get('/search/video', 'SearchController@searchVideos');
Route::get('/search/categories', 'SearchController@searchCategories');
Route::get('/search/collections', 'SearchController@searchCollections');

//管理专题
Route::get('/category/list', 'CategoryController@list');
Route::resource('/category', 'CategoryController');

Route::resource('/collection', 'CollectionController');
Route::get('/tag/{name}', 'TagController@tagname');
Route::resource('/tag', 'TagController');

//片段
Route::resource('/snippet', 'SnippetController');

//消息
Route::get('/notification', 'NotificationController@index');
Route::get('/chat/with/{user_id}', 'ChatController@chat');
//关注
Route::get('/follow', 'FollowController@index');

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

//多媒体
Route::resource('/image', 'ImageController');
Route::get('/video/list', 'VideoController@list');
Route::get('/video/{id}', 'VideoController@show');
Route::get('/video/{id}/process', 'VideoController@processVideo');
Route::resource('/video', 'VideoController');

//Route::get('/share/collection/{id}', CollectionController::class . '@shareCollection');

//weixin
Route::get('/wechat', 'WechatController@serve');

//支付
Route::get('/pay', 'PayController@pay');

//qrcode
Route::get('/share/qrcode', 'SharingController@qrcode');

//search_log
Route::get('/searchQuery', 'SearchController@search_all');

//返回URL二维码
Route::get('/share/qrcode/{url}', 'SharingController@qrcode');
