星数引擎 1.0
===============

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](http://www.php.net/)
[![License](https://poser.pugx.org/topthink/framework/license)](https://packagist.org/packages/topthink/framework)


[官方服务](https://www.xsyq.cn) | [`XsFrame`——官方统一API](https://www.xsyq.cn/doc)

## 主要新特性

* 采用`PHP7`强类型（严格模式）
* 支持更多的`PSR`规范
* 原生多应用支持
* 系统服务注入支持
* ORM作为独立组件使用
* 全新的事件系统
* 模板引擎分离出核心
* 内部功能中间件化
* SESSION机制改进
* 日志多通道支持
* 规范扩展接口
* 更强大的控制台
* 对Swoole以及协程支持改进
* 对IDE更加友好
* 统一和精简大量用法


> XsFrame的运行环境要求PHP7.4.0+，最高兼容PHP8.2

## 安装

~~~
composer require xsframe/framework
~~~

启动服务

~~~
cd tp
php think run
~~~

然后就可以在浏览器中访问

~~~
http://localhost:8000
~~~

如果需要更新框架使用
~~~
composer update xsframe/framework
~~~

## 文档

[完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## 命名规范

`XsFrame`遵循PSR-2命名规范和PSR-4自动加载规范。

## 参与开发

直接提交PR或者Issue即可

## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2024 by XsFrame (http://xsframe.cn) All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
