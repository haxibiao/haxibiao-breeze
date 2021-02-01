<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

//breeze通过composer 模式加载时AuthServiceProvider也许加载更晚，找不带Auth Facade..

//登录注册
// Auth::routes();
Route::mixin(new \Laravel\Ui\AuthRouteMethods());
//验证邮件
// Auth::routes(['verify' => true]);
Route::auth(['verify' => true]);
