<?php

// //请求最新视频
// Route::get('/getlatestVideo', 'Api\VideoController@getLatestVideo');

// //視頻列表
// Route::get('videos', 'Api\VideoController@index'); //旧的api
// Route::get('/video/{id}', 'Api\VideoController@show');
// Route::middleware('auth:api')->post('/video', 'Api\VideoController@store'); //上传视频接口
// Route::middleware('auth:api')->post('/video/save', 'Api\VideoController@store'); //兼容1.0上传视频接口
// //获取视频截图
// Route::get('/{id}/covers', 'Api\VideoController@covers');

// //COS转码后的回调地址
// Route::any('/cos/video/hook', 'Api\VideoController@cosHookVideo');