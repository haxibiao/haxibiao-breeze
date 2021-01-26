<?php

use Illuminate\Support\Facades\Route;

//用户设置
Route::middleware('auth:api')->get('/user', "UserController@getSetting");

//未读消息
Route::middleware('auth:api')->get('/unreads', 'UserController@unreads');

//用户
Route::get('/user/index', 'UserController@index');
Route::get('/user/recommend', 'UserController@recommend');
Route::middleware('auth:api')->post('/user/save-avatar', 'UserController@saveAvatar');
Route::middleware('auth:api')->post('/user', 'UserController@save');
Route::middleware('auth:api')->post('/user/{id}/follow', 'UserController@follows');
Route::middleware('auth:api')->get('/user/editors', 'UserController@editors');
Route::get('/user/{id}', 'UserController@show');

//获取at相关用户
Route::middleware('auth:api')->get('/related-users', 'UserController@relatedUsers');

//按用户名搜索用户
Route::get('/user/name/{name}', 'UserController@name');
//获取用户上传的图片，可以按标题搜索
Route::get('/user/{id}/images', 'UserController@images');
//获取用户上传的视频，可以按标题搜索
Route::get('/user/{id}/videos', 'UserController@videos');
//获取用户发布的文章，可以按标题搜索
Route::get('/user/{id}/articles', 'UserController@articles');

//获取用户上传的视频
Route::any('/user/{id}/videos/relatedVideos', 'UserController@relatedVideos');
