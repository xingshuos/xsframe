# 星数引擎（XsFrame）

> WE CAN DO IT JUST THINK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](http://www.php.net/)
[![License](https://img.shields.io/badge/License-Apache--2.0-blue.svg)](LICENSE)
[![ThinkPHP](https://img.shields.io/badge/ThinkPHP-6.0-green.svg)](https://github.com/topthink/framework)

## 目录

- [简介](#简介)
- [核心特性](#核心特性)
- [技术架构](#技术架构)
- [目录结构](#目录结构)
- [安装部署](#安装部署)
- [快速入门](#快速入门)
- [应用开发指南](#应用开发指南)
- [核心组件](#核心组件)
- [命令行工具](#命令行工具)
- [生态资源](#生态资源)
- [参与贡献](#参与贡献)
- [许可证](#许可证)

---

## 简介

**星数引擎（XsFrame）** 是由[星数为来（杭州）科技有限公司](https://www.xsyq.cn)自主研发的数字化底座开发引擎，基于 **ThinkPHP 6.0** 深度构建。引擎面向企业级多商户 SaaS 场景，内置多应用架构、统一商户管理、支付集成、文件存储、短信服务等完整业务组件，开发者可在同一套系统下高效交付面向金融、制造、教育、医疗等多行业的数字化应用。

**官方资源：**
- 🌐 官网：[https://www.xsyq.cn](https://www.xsyq.cn)
- 📖 文档：[https://www.xsyq.cn/doc/pc](https://www.xsyq.cn/doc/pc)
- 🎓 星数学院：[https://www.xsyq.cn/articles](https://www.xsyq.cn/articles)
- 🛒 应用市场：[https://www.xsyq.cn/store/app.html](https://www.xsyq.cn/store/app.html)
- 💬 开发者QQ群：139779262
- 📞 开发者手机号：13282030470
- 🆔 开发者QQ号：786824455
- 📧 开发者邮箱：786824455@qq.com

---

## 核心特性

### 多终端统一架构
- **PC 端**：桌面浏览器应用，支持完整后台管理
- **H5 端**：移动端适配页面，一套代码多端运行
- **API 接口**：标准化 JSON API，支持跨平台接入
- **微信生态**：微信小程序、公众号、企业微信全链路集成

### 多商户（Uniacid）体系
- 基于 `uniacid` 的商户隔离，同一系统支撑多租户运营
- 独立域名绑定、自动商户识别、权限分级（owner / manager）
- 商户级配置覆盖系统默认配置，灵活适配不同业务需求

### 企业级业务组件
| 类别 | 组件 |
|------|------|
| 支付 | 微信支付（Native/App/H5/JSAPI）、支付宝（App/电脑/手机网站）、收钱吧 |
| 文件存储 | 本地存储、阿里云 OSS、腾讯云 COS、七牛云 |
| 短信服务 | 阿里云短信、腾讯云短信 |
| 快递物流 | 主流快递公司实时查询 |
| AI 能力 | 阿里视频点播、自定义 AI 服务接入 |
| 数据导出 | PhpSpreadsheet 完整支持 Excel 导出 |
| 其他 | 邮箱（PHPMailer）、二维码（QRCode）、条形码（BCG） |

### 标准化开发范式
- 统一控制器基类（`AdminBaseController` / `ApiBaseController` / `MobileBaseController` / `WebBaseController`）
- 门面（Facade）模式封装所有核心服务
- Trait 复用后台管理通用增删改查逻辑（`AdminTraits`）
- 应用插件化：安装 / 卸载 / 升级全生命周期管理，配备 manifest.xml 清单文件

---

## 技术架构

```
┌─────────────────────────────────────────────────────────┐
│                      应用层 (app/)                       │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  │
│  │  API 接口  │ │  PC 端    │ │  H5 端    │ │  后台管理  │  │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘  │
├─────────────────────────────────────────────────────────┤
│               框架核心层 (vendor/xsframe/)              │
│  ┌──────────────────────────────────────────────────┐  │
│  │ Controller │  Service │   Facade  │  Middleware │  │
│  │  Enum/Exception │ Util │ Wrapper │  Traits      │  │
│  └──────────────────────────────────────────────────┘  │
├─────────────────────────────────────────────────────────┤
│               底层框架 (ThinkPHP 6.0)                   │
│  ORM │ Cache │ Session │ Log │ Middleware │ Console     │
├─────────────────────────────────────────────────────────┤
│                      PHP 7.4+ / PHP 8.x                │
└─────────────────────────────────────────────────────────┘
```

### 层级职责

| 层级 | 说明 |
|------|------|
| **应用层** | 开发者编写的业务代码，按应用名（module）隔离，遵循统一目录规范 |
| **框架核心层** | `xsframe/framework` 包，提供所有基础类、服务、工具类，统一版本迭代 |
| **底层框架** | ThinkPHP 6.0，提供路由、容器、中间件、ORM 等基础设施 |

---

## 目录结构

```
xsframe/                          # 星数引擎根目录
│
├─app/                            # 应用根目录（所有业务开发在此之下）
│  ├─{app_name}/                  # 具体应用目录（应用标识名）
│  │  ├─common.php               # 应用公共函数文件
│  │  ├─controller/
│  │  │  ├─api/                 # API 接口控制器（对外 JSON 接口）
│  │  │  ├─pc/                  # PC 端控制器
│  │  │  ├─mobile/               # H5 移动端控制器
│  │  │  ├─web/                 # 后台管理控制器（支持二级目录）
│  │  │  │  └─{模块名}/{类名}.php
│  │  ├─enum/                    # 枚举类目录
│  │  ├─facade/                  # 应用门面目录
│  │  ├─middleware/               # 应用中间件目录
│  │  ├─model/                   # 模型目录
│  │  ├─packages/                # 应用资源包
│  │  │  ├─mobile/               # H5 静态资源
│  │  │  ├─pc/                  # PC 静态资源
│  │  │  ├─static/              # 公共静态资源
│  │  │  ├─icon.png             # 应用图标
│  │  │  ├─install.php           # 应用安装 SQL 文件
│  │  │  ├─manifest.xml          # 应用清单配置（必选）
│  │  │  ├─uninstall.php         # 卸载时执行的 SQL
│  │  │  └─upgrade.php          # 升级时执行的 SQL
│  │  ├─view/                    # 视图模板目录
│  │  │  ├─web/                 # 后台模板（含二级目录）
│  │  │  ├─mobile/               # H5 模板
│  │  │  └─pc/                  # PC 模板
│  │  ├─config/                  # 应用配置目录
│  │  │  └─menu.php             # 后台菜单配置
│  │  └─route/                   # 路由目录
│  │     ├─api.php              # API 路由
│  │     ├─pc.php               # PC 路由
│  │     └─mobile.php           # H5 路由
│  │
│  ├─common.php                  # 全局公共函数
│  └─event.php                   # 全局事件定义文件
│
├─config/                         # 全局配置文件
│  ├─app.php                     # 应用配置
│  ├─cache.php                   # 缓存配置
│  ├─console.php                 # 控制台配置
│  ├─cookie.php                  # Cookie / CORS 配置
│  ├─database.php                # 数据库配置
│  ├─filesystem.php              # 文件磁盘配置
│  ├─lang.php                    # 多语言配置
│  ├─log.php                     # 日志配置
│  ├─middleware.php              # 全局中间件配置
│  ├─route.php                   # 路由配置
│  ├─session.php                 # Session 配置
│  ├─trace.php                   # Trace 配置
│  └─view.php                    # 视图配置
│
├─public/                         # WEB 根目录（对外可访问）
│  ├─index.php                   # 入口文件
│  └─.htaccess                   # Apache URL 重写规则
│
├─runtime/                        # 运行时目录（可写）
├─vendor/                         # Composer 依赖包
│  └─xsframe/framework/          # 星数引擎核心包
│     └─src/xsframe/
│        ├─base/                 # 基础控制器/模型/服务基类
│        ├─service/              # 核心业务服务
│        ├─facade/               # 门面（服务调用入口）
│        ├─wrapper/              # 封装器（系统/商户/模块封装）
│        ├─enum/                 # 枚举类
│        ├─exception/            # 异常处理
│        ├─middleware/            # 中间件
│        ├─util/                 # 工具类
│        ├─library/              # 第三方库（二维码/条形码）
│        ├─pay/                  # 支付封装（微信/支付宝）
│        ├─traits/               # Trait 复用
│        └─function/             # 全局函数
│
├─composer.json                  # Composer 定义文件
├─.example.env                   # 环境变量示例文件
├─think                          # 命令行入口
├─LICENSE                        # Apache-2.0 许可证
└─README.md                      # 本文档
```

---

## 安装部署

### 环境要求

| 项目 | 要求 |
|------|------|
| PHP 版本 | 7.4 ~ 8.2 |
| 数据库 | MySQL 5.7+（需支持 `mysqli` 扩展） |
| PHP 扩展 | gd / json / openssl / curl / mbstring / fileinfo / zip / bcmath / xmlwriter / exif / iconv / mysqli |
| Web 服务器 | Nginx 1.18+ 或 Apache 2.4+（需开启 mod_rewrite） |

### 安装步骤

**方式一：通过源码包安装**

1. 购买域名并准备服务器
2. 使用宝塔面板（推荐）或手动安装 LNMP 环境
3. 在宝塔面板添加站点，配置 777 可写权限
4. 下载星数引擎框架压缩包，上传至站点根目录并解压
5. 配置伪静态规则、反跨站（open_basedir）、SSL 证书
6. 访问域名，按引导完成数据库连接和超级管理员设置
7. 进入管理后台，完善站点信息（站点ID + 通信密钥）

**方式二：通过 Composer 安装核心包**

```bash
composer require xsframe/framework
```

**环境变量配置（.env）**

```env
APP_DEBUG = false
DEFAULT_APP = xs_store
AUTHKEY = xsframe_your_key_here
ADMIN_ACCOUNT_MANAGER = true

[CACHE]
driver=file
host=127.0.0.1
port=6379
password=
prefix=xsframe

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = xsframe
USERNAME = root
PASSWORD = root
HOSTPORT = 3306
PREFIX = ims_
```

### 启动开发服务器

```bash
cd xsframe
php think run
# 访问 http://localhost:8000
```

---

## 快速入门

### 创建第一个应用

星数引擎采用多应用架构，每个应用对应一个业务模块。以下以 `xs_form` 表单案例应用为例说明完整开发流程。

#### 1. 创建应用目录结构

```
app/
└─xs_form/
   ├─config/menu.php             # 后台菜单配置
   ├─controller/
   │  ├─api/Index.php           # API 控制器
   │  ├─web/Sets.php            # 设置控制器
   │  ├─web/form/Basic.php      # 基础表单控制器
   │  └─web/form/Module.php     # 组件表单控制器
   ├─model/                      # 模型目录
   ├─packages/
   │  ├─icon.png
   │  ├─install.php             # 建表 SQL
   │  ├─manifest.xml            # 应用清单（必选）
   │  ├─uninstall.php           # 卸载 SQL
   │  └─upgrade.php             # 升级 SQL
   ├─route/api.php              # API 路由
   └─view/web/
       ├─form/basic/
       │  ├─list.html           # 列表页
       │  ├─post.html           # 新建/编辑页
       │  └─table.html          # 模态框页
       └─sets/
           └─module.html         # 设置页
```

#### 2. 配置应用清单（manifest.xml）

清单文件位于 `packages/manifest.xml`，是应用安装的必选文件：

```xml
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="https://www.xsyq.cn" versionCode="1.0">
    <application setting="true">
        <name><![CDATA[表单案例]]></name>
        <identifie><![CDATA[xs_form]]></identifie>
        <version><![CDATA[1.0.2]]></version>
        <type><![CDATA[business]]></type>
        <ability><![CDATA[常见表单案例]]></ability>
        <description><![CDATA[后台常用表单案例管理]]></description>
        <author><![CDATA[GuiHai]]></author>
        <url><![CDATA[https://www.xsyq.cn]]></url>
    </application>
    <platform>
        <supports>
            <item type="pc" />
        </supports>
    </platform>
    <install><![CDATA[install.php]]></install>
    <uninstall><![CDATA[uninstall.php]]></uninstall>
    <upgrade><![CDATA[upgrade.php]]></upgrade>
</manifest>
```

#### 3. 编写安装 SQL（install.php）

```php
<?php
$sql = "
CREATE TABLE `ims_xs_form_data_basic` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `isrecommand` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `isnew` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最新',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最热',
  `type` int(10) NOT NULL DEFAULT '0' COMMENT '类型',
  `author_id` int(10) NOT NULL DEFAULT '0' COMMENT '作者id',
  `education` varchar(50) NOT NULL DEFAULT '' COMMENT '学历多选',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='常用表单保存数据';
";
return $sql;
```

---

## 应用开发指南

### 一、后台管理控制器

所有后台管理控制器需继承 `xsframe\base\AdminBaseController`，该基类自动完成：

- 登录态校验（自动跳转登录页或返回 403）
- 商户（uniacid）上下文注入
- 后台菜单加载与权限判断
- 视图变量自动注入（module / controller / action / uid / uniacid 等）

```php
<?php
namespace app\xs_form\controller\web\form;

use xsframe\base\AdminBaseController;
use xsframe\facade\service\DbServiceFacade;

class Basic extends AdminBaseController
{
    // 指定操作的表名（不含 ims_ 前缀）
    protected $tableName = "xs_form_data_basic";

    // 引入模态框
    public function table()
    {
        return $this->template("table");
    }

    // 初始化钩子（可选）
    public function _admin_initialize()
    {
        parent::_admin_initialize();
        // 自定义初始化逻辑
    }
}
```

### 二、API 接口控制器

所有对外 API 接口需继承 `xsframe\base\ApiBaseController`，该基类提供：

- CORS 跨域自动处理（OPTIONS 请求自动拦截）
- 统一 JSON 响应格式 `{code, msg, data}`
- 自动 UTF-8 编码转换（兼容 GBK 环境）
- 获取当前登录用户 ID

```php
<?php
namespace app\xs_form\controller\api;

use xsframe\base\ApiBaseController;

class Index extends ApiBaseController
{
    public function index(): \think\response\Json
    {
        $result = ['name' => '张三'];
        return $this->success($result);
    }

    public function detail(): \think\response\Json
    {
        $id = $this->request->param('id');
        return $this->success(['id' => $id]);
    }
}
```

### 三、后台菜单配置（menu.php）

```php
<?php
$menu = [
    'form' => [
        'title'    => '表单',
        'subtitle' => '表单案例',
        'icon'     => 'icon-file-text-o',
        'items'    => [
            // 一级路由：route = 方法名
            ['title' => '基础表单', 'route' => 'basic/main'],
            // 二级路由：route = 类名/方法名
            ['title' => '组件表单', 'route' => 'module/main'],
            ['title' => '图标管理', 'route' => 'icon/main'],
        ]
    ],
    'sets' => [
        'title'    => '设置',
        'subtitle' => '设置管理',
        'icon'     => 'icon-cog',
        'items'    => [
            // 一级路由：route = 方法名
            ['title' => '应用设置', 'route' => 'module'],
        ]
    ],
];
return $menu;
```

**路由规则说明：**

| 路由类型 | `route` 写法 | 对应控制器 | 对应视图 |
|---------|-------------|-----------|---------|
| 一级 | `main` | `controller/web/{模块}/Main.php` | `view/{模块}/{子模块}/main.html` |
| 二级 | `basic/main` | `controller/web/{模块}/basic/Main.php` | `view/{模块}/{子模块}/basic/main.html` |
| 一级 | `sets` | `controller/web/{模块}/Sets.php` | `view/{模块}/sets/list.html` |

> **注意**：`controller/web` 目录最多支持两级目录（`Sets.php` 为一级，`basic/Main.php` 为二级）。

### 四、路由配置（route/api.php）

```php
<?php
use think\facade\Route;

Route::group('api', function () {

    // 无需登录的公开接口
    Route::group(function () {
        Route::any('home/index', 'api.index/index');
    })->allowCrossDomain();

    // 需要登录的接口（自动携带 uniacid 和 uid）
    Route::group(function () {
        Route::any('user/info', 'api.user/info');
    });

});
```

完整访问路径示例：`http://www.example.com/xs_form/api/home/index.html?i=1`

### 五、视图模板规范

视图采用 ThinkPHP 原生模板语法，继承后台公共模板：

**列表页（list.html）**

```html
{extend name="../../admin/view/public/admin"}

{block name='content'}

<div class="page-header">
    <span>当前位置：<span class="text-primary">基础表单</span></span>
</div>

<div class="page-content">
    <!-- 搜索栏 -->
    <form action="" method="get" class="form-horizontal table-search">
        <div class="page-toolbar">
            <div class="col-sm-4">
                <a class="btn btn-primary btn-sm"
                   href="{:webUrl('form.basic/add')}">
                   <i class="icon icon-plus"></i> 添加
                </a>
            </div>
        </div>
    </form>

    {if empty($list)}
        <div class="panel panel-default">
            <div class="panel-body empty-data">未查询到相关数据</div>
        </div>
    {else/}
        <table class="table table-responsive">
            <thead>
            <tr>
                <th style="width:40px;"><input type="checkbox"></th>
                <th>表单名称</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            {foreach $list as $row}
            <tr>
                <td><input type="checkbox" value="{$row['id']}" class="checkone"/></td>
                <td>{$row['name']}</td>
                <td>
                    <span class="label {if $row['enabled']==1}label-primary{else}label-default{/if}">
                        {if $row['enabled']==1}显示{else}隐藏{/if}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-op btn-operation"
                           href="{:webUrl('form.basic/edit', ['id' => $row['id']])}">
                           <i class="icon icon-edit"></i>
                        </a>
                        <a class="btn btn-op btn-operation"
                           data-toggle='ajaxRemove'
                           href="{:url('form.basic/delete', ['id' => $row['id']])}"
                           data-confirm="确定要删除？">
                           <i class="icon icon-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            {/foreach}
            </tbody>
        </table>
        {$pager | raw}
    {/if}
</div>

{/block}
```

**新建/编辑页（post.html）**

```html
{extend name="../../admin/view/public/admin"}

{block name='content'}

<div class="page-header">
    当前位置：<span class="text-primary">{if !empty($item)}编辑{else}新建{/if}表单</span>
</div>

<div class="page-content">
    <form action="" method="post" class="form-validate form-horizontal">
        <input type="hidden" name="id" value="{$item['id']}"/>

        <div class="form-group">
            <label class="col-lg-2 control-label must">排序</label>
            <div class="col-sm-9 col-xs-12">
                <input type="text" name="displayorder"
                       class="form-control"
                       value="{:intval($item['displayorder'])}"
                       data-rule-required='true'/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label must">表单名称</label>
            <div class="col-sm-9 col-xs-12">
                <input type="text" name="name"
                       class="form-control"
                       value="{$item['name']}"
                       data-rule-required='true'
                       data-msg-required='请输入表单名称'/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label">是否显示</label>
            <div class="col-sm-9 col-xs-12">
                <label class="radio-inline">
                    <input type="radio" name="enabled" value="1"
                           {if $item['enabled']==1}checked{/if}> 显示
                </label>
                <label class="radio-inline">
                    <input type="radio" name="enabled" value="0"
                           {if $item['enabled']==0 || empty($item)}checked{/if}> 隐藏
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label"></label>
            <div class="col-sm-9">
                <input type="submit" class="btn btn-primary" value="保存"/>
                <a class="btn btn-default" href="{:webUrl('form.basic/main')}">返回列表</a>
            </div>
        </div>
    </form>
</div>

{/block}
```

### 六、表单字段规范

控制器中通过 `$tableName` + `AdminTraits` 自动处理增删改查，无需手写 SQL。只需在对应视图中使用以下规范字段名：

| 字段名 | 说明 | 用法 |
|--------|------|------|
| `displayorder` | 排序号 | 支持点击直接编辑（ajaxEdit） |
| `enabled` | 是否显示（0/1） | 自动渲染为开关标签 |
| `deleted` | 软删除标记（0/1） | 自动过滤不显示已删除数据 |
| `isrecommand` | 推荐标记 | 支持推荐/最新/最热多标签 |
| `isnew` | 最新标记 | 配合属性标签渲染 |
| `ishot` | 最热标记 | 配合属性标签渲染 |
| `createtime` | 创建时间（时间戳） | 自动格式化显示 |

### 七、常用模板标签

| 标签函数 | 说明 |
|---------|------|
| `{:webUrl('模块.控制器/方法', [参数])}` | 生成后台 URL |
| `{:url('模块/控制器/方法', [参数])}` | 生成普通 URL |
| `{:tpl_form_field_image('thumb', $item['thumb'])}` | 单图片上传 |
| `{:tpl_form_field_multi_image('thumbs', $item['thumbs'])}` | 多图片上传（带历史记录） |
| `{:tpl_form_field_date('date_time', $date)}` | 日期选择 |
| `{:tpl_form_field_daterange('time', $config)}` | 日期范围选择 |
| `{:tpl_form_field_calendar('birthday', $date)}` | 日历选择 |
| `{:tpl_form_field_district('address', $address)}` | 省市区选择 |
| `{:tpl_form_field_color('color', $color)}` | 颜色选择器 |
| `{:tpl_form_field_position(['lng'=>$lng,'lat'=>$lat])}` | 坐标选择（地图） |
| `{:tpl_ueditor('content', $content)}` | 富文本编辑器 |
| `{:tpl_form_field_video2('video_url', $url)}` | 视频上传 |
| `{:tpl_form_field_audio('audio_url', $url)}` | 音频上传 |
| `{:tpl_selector(...)}` | 数据选择器（弹窗选择数据） |

---

## 核心组件

### 控制器基类

| 基类 | 适用场景 | 主要能力 |
|------|---------|---------|
| `xsframe\base\BaseController` | 所有控制器基类 | uniacid 解析 / 参数注入 / 视图变量初始化 |
| `xsframe\base\AdminBaseController` | 后台管理 | 登录校验 / 菜单加载 / 权限控制 / AdminTraits |
| `xsframe\base\ApiBaseController` | API 接口 | CORS 处理 / 统一 JSON 响应 / UTF-8 转换 |
| `xsframe\base\MobileBaseController` | H5 移动端 | 移动端适配 / 客户端上下文注入 |
| `xsframe\base\WebBaseController` | PC 前台 | PC 端页面渲染 / 公共变量 |

### 门面（Facade）

通过门面以静态方式访问核心服务，无需手动实例化：

```php
use xsframe\facade\service\SysMemberServiceFacade;   // 会员服务
use xsframe\facade\service\DbServiceFacade;          // 数据库服务
use xsframe\facade\service\FileServiceFacade;       // 文件服务
use xsframe\facade\service\OssServiceFacade;        // 对象存储
use xsframe\facade\service\SmsServiceFacade;        // 短信服务
use xsframe\facade\service\PayServiceFacade;        // 支付服务
use xsframe\facade\service\RedisServiceFacade;      // 缓存服务
use xsframe\facade\service\AreaServiceFacade;       // 地区数据
use xsframe\facade\service\ExpressServiceFacade;    // 快递查询
use xsframe\facade\service\WechatServiceFacade;     // 微信服务
use xsframe\facade\service\AliPayServiceFacade;     // 支付宝服务
use xsframe\facade\service\WxappServiceFacade;       // 小程序服务
use xsframe\facade\service\WxPayServiceFacade;       // 微信支付
```

### 工具类

位于 `vendor/xsframe/framework/src/xsframe/util/`：

- `ArrayUtil` — 数组处理
- `StringUtil` — 字符串工具（含中文处理）
- `ValidateUtil` — 数据验证
- `FileUtil` — 文件操作
- `ImgUtil` — 图片处理（缩放/裁剪/水印）
- `ExcelUtil` — Excel 导入导出
- `PriceUtil` — 价格计算
- `TimeUtil` — 时间处理
- `RandomUtil` — 随机数/Token 生成
- `PhoneUtil` — 手机号验证
- `PinYinUtil` — 中文拼音转换
- `HtmlUtil` — HTML 工具
- `UrlUtil` — URL 处理
- `JsonUtil` — JSON 编解码
- `AesEncoderUtil` — AES 加解密
- `OpensslUtil` — OpenSSL 工具
- `LoggerUtil` — 日志记录
- `LicenseUtil` — 授权校验

### 异常类

| 异常类 | 说明 |
|--------|------|
| `xsframe\exception\BaseException` | 基础异常 |
| `xsframe\exception\ApiException` | API 业务异常（自动返回 JSON 错误） |
| `xsframe\exception\ThirdException` | 第三方服务调用异常 |
| `xsframe\exception\ExceptionHandler` | 全局异常处理器 |

---

## 命令行工具

使用 `think` 命令行工具：

```bash
# 启动开发服务器
php think run

# 创建控制器
php think make:controller api/Test

# 创建模型
php think make:model ModelName

# 创建服务
php think make:service ServiceName

# 创建中间件
php think make:middleware Auth

# 创建验证器
php think make:validate ValidateName

# 创建事件
php think make:event EventName

# 创建监听器
php think make:listener ListenerName
```

---

## 生态资源

| 资源 | 地址 |
|------|------|
| 应用市场（插件/模板/源码） | [https://www.xsyq.cn/store/app.html](https://www.xsyq.cn/store/app.html) |
| 开发文档 | [https://www.xsyq.cn/doc/pc](https://www.xsyq.cn/doc/pc) |
| 星数学院（教程/案例） | [https://www.xsyq.cn/articles](https://www.xsyq.cn/articles) |
| 详细安装教程 | [https://www.xsyq.cn/store/article/47.html](https://www.xsyq.cn/store/article/47.html) |
| Gitee 源码仓库 | [https://gitee.com/xingshuos/xsframe](https://gitee.com/xingshuos/xsframe) |
| GitHub 源码仓库 | [https://github.com/xingshuos/xsframe](https://github.com/xingshuos/xsframe) |

---

## 参与贡献

1. **Fork** 本仓库
2. 创建特性分支：`git checkout -b feat/your-feature-name`
3. 提交更改：`git commit -m 'Add: some feature'`
4. 推送分支：`git push origin feat/your-feature-name`
5. 提交 **Pull Request**，描述清楚改动内容和使用场景

---

## 许可证

本项目基于 **Apache-2.0** 开源协议发布，可免费在商业项目中使用的开源框架。

- 星数引擎版权所有 © 2023~2024 [星数为来（杭州）科技有限公司](https://www.xsyq.cn)
- ThinkPHP 框架版权所有 © 2006-2024 [上海顶想信息科技有限公司](http://www.thinkphp.cn)

详细协议内容请参阅 [LICENSE](LICENSE) 文件。

---

> **Slogan：WE CAN DO IT JUST THINK**
