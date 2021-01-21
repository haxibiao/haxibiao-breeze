<?php

use Illuminate\Support\Facades\Auth;

//登录注册
Auth::routes();
//验证邮件
Auth::routes(['verify' => true]);
