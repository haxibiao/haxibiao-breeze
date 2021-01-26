<?php

use Illuminate\Support\Facades\Route;

//写作编辑器
Route::middleware('auth:api')->get('/collections', 'CollectionController@index');
Route::get('/collection/{id}', 'CollectionController@show');
Route::get('/collection/{id}/articles', 'CollectionController@articles');
Route::middleware('auth:api')->post('/collection/create', 'CollectionController@create');
Route::middleware('auth:api')->post('/collection/{id}', 'CollectionController@update');
Route::middleware('auth:api')->post('/collection/{id}/article/create', 'CollectionController@createArticle');
Route::middleware('auth:api')->get('/article-{id}-move-collection-{cid}', 'CollectionController@moveArticle');
Route::middleware('auth:api')->delete('/collection/{id}', 'CollectionController@delete');

Route::any('/collection/{collection_id}/posts', 'CollectionController@getCollectionVideos');
