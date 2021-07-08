<?php

use Illuminate\Support\Facades\Route;

//APK版本新打包上传成功回调
Route::get('/hook/apk/upload', 'AppController@hookApkUpload');
//IPA版本新打包上传成功回调
Route::get('/hook/ipa/upload', 'AppController@hookIpaUpload');

//广告的配置(方便激励视频每看一次更新)
Route::get('/ad-config', 'AppController@adConfig');
//app功能开关(含广告配置)
Route::get('/app-config', 'AppController@appConfig');
//app版本管理
Route::any('/app-version', 'AppController@version');

//app系统全局配置
Route::any('/configs', 'AppController@configs');
