<?php

use Illuminate\Support\Facades\Route;

//搜索
Route::get('/search/hot-queries', 'SearchController@hotQueries');
//个人搜索历史
Route::middleware('auth:api')->get('/search/latest-querylogs', 'SearchController@latestQuerylog');
//清空最近5个搜索
Route::middleware('auth:api')->delete('/search/clear-querylogs', 'SearchController@clearQuerylogs');
//清空单个搜索
Route::middleware('auth:api')->delete('/search/remove-querylog-{id}', 'SearchController@removeQuerylog');
