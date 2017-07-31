# yaf结合swoole httpserver写的api接口

## 为什么写这个
参照了 [LinkedDestiny](https://github.com/LinkedDestiny/swoole-yaf)   [xuebingwang](https://github.com/xuebingwang/xbw-swoole-yaf) ，觉得太裸露，就决定用composer形式来写，同时也是一种锻炼

程序我会在应用中进一步完善，同时希望大家给点意见

## Requirement
- yaf扩展
- swoole扩展

## 如何使用

### 1.生成yaf框架
[生成yaf框架](https://github.com/laruence/yaf/tree/master/tools/cg)

### 2.使用composer安装

在yaf框架根目录下面使用命令行输入
```
$ composer require gnp/yaf_swoole_plug dev-master 
```

然后更新composer
```
$ composer update
```

```php
//修改yaf框架入口文件

define('APPLICATION_PATH', dirname(__FILE__));
require_once dirname(__FILE__).'/vendor/autoload.php';

if (PHP_SAPI === 'cli'){
    //传入swoole的配置
	$config = array();
   	Ysp\Entry::startServer($config);
}else{
    //这边是传统的apache或php-fpm模式
	$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
	$application->bootstrap()->run();
}

//为了方便，我封装了get和post方法，存放在src/common/function.php
将代码拷贝出来，放入框架中去
```

启动服务
```php
//进入yaf框架入口文件对应目录，在命令行模式下输入
php index.php

//打开浏览器，输入localhost:9501
hello world
```

## 3.与nginx结合
[参考我另一个项目中的说明](https://github.com/gnpok/yafApi)

## 4.swoole的异步任务
[参考我另一个项目中的说明](https://github.com/gnpok/yafApi)
