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

## Roadmap & TODOs

1. base:install provide 一些默认头像
2. BaseUser 直接基于子系统模块，预先:

    # use 核心模块

-   Cms 内容管理系统
-   Content 内容
-   Sns 社交
-   Media 媒体
-   Question 答题 (待重构)
-   Store 商品(待重构)
-   Wallet 钱包

    # playWith 附加模块

*   Task 任务
*   Game 游戏
*   Live 直播
