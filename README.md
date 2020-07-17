# 哈希表项目 Base 模块

基础 Model,User,Tests,Exceptions,资料和用户数据读写，后期可添加拉黑，禁用，IP 手机号 位置管理等基础能力

## 依赖

1. haxibiao/helpers

#### 异常（Exception）：

- GQLException，GQL 异常

## 安装步骤

1. `composer.json`改动如下：
   在`repositories`中添加 vcs 类型远程仓库指向
   `http://code.haxibiao.cn/packages/haxibiao-base`
2. 执行`composer require haxibiao\base`
3. env('COS_DEFAULT_AVATAR') 设置为 true，如果要自定义默认头像的话
