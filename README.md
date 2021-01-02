# 哈希表 Base 模块

基于 laravel helpers,auth,ui 模块之后，提供基本类库，用户行为（注册，登录，找回密码，手机验证）接口等一些通用项目基础能力..

## 依赖

1. haxibiao/helpers

#### 配置说明

-   默认用户名:匿名用户，可修改 config('auth.default_name')
-   默认用户头像，可修改 config('auth.default_avatar')

## 安装步骤

1. `composer.json`改动如下：
   在`repositories`中添加 vcs 类型远程仓库指向
   `http://code.haxibiao.cn/packages/haxibiao-base`
2. 执行`composer require haxibiao\base`
3. env('COS_DEFAULT_AVATAR') 设置为 true，如果要自定义默认头像的话
