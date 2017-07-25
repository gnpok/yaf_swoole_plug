# yaf结合swoole httpserver写的api接口


## 如何使用

### 使用composer安装

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


```
