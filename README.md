RESTful API Extension for Yii2
==========================

对[yiisoft/yii2-rest](https://www.yiiframework.com/doc/guide/2.0/en/rest-quick-start) 自定义补充和扩展。

- GET indexAction 友好化的筛选功能,增加指定筛选字段 
- PUT updateAction 增加只允许指定更新的字段功能
- 扩展输出类Serializer，增加可自定义的link链接地址
- 更友好格式化输出和错误处理

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require --prefer-dist "gybe/yii2-rest" "~1.0.0"
```

or add

```
"gybe/yii2-rest": "~1.0"
```

to the require section of your application's `composer.json` file.

Usage
-----
[yiisoft/yii2-rest](https://www.yiiframework.com/doc/guide/2.0/en/rest-quick-start)

