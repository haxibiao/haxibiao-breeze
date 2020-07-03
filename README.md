# 哈希表项目users模块
## 目录
- SNS模块
  - 微信Utils
  - 支付宝Utils
  - 绑定微信
  - 绑定支付宝
- 登录模块
  - 一键登录
  - 手动登录
  - 手动注册
- 头像模块
  - 上传头像
  - 默认头像
```bash
.
├── LICENSE
├── composer.json
├── config
│   ├── alipay_sns.php
│   └── wechat_sns.php
├── readme.md
└── src
    ├── Auth
    │   └── AuthHelper.php
    ├── Avatar
    │   └── AvatarHelper.php
    ├── Exceptions
    │   ├── SNSException.php
    │   └── SignInException.php
    ├── SNS
    │   ├── AlipayUtils.php
    │   ├── SNSHelper.php
    │   └── WechatUtils.php
    └── UserServiceProvider.php

6 directories, 13 files
```
## 依赖
1. haxibiao/helper
2. User需要avatar字段保存cos_path,否则null
3. User需要字段uuid,account和关系wallet, profile

#### 模型（Model）:
- App\User
- App\OAuth
- App\Wallet
- App\Withdraw
- Laravel Storage
- anerg2046/sns_auth
#### 异常（Exception）：
- SNSException，授权异常
- SignInException，登录异常

## 安装步骤

1. `composer.json`改动如下：
在`repositories`中添加 vcs 类型远程仓库指向 
`http://code.haxibiao.cn/packages/haxibiao-users` 
2. 执行`composer require Haxibiao\Users`
3. env('COS_DEFAULT_AVATAR') 设置为true，如果要自定义默认头像的话
