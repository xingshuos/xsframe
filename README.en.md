# XsFrame (Star Number Engine)

> WE CAN DO IT JUST THINK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](http://www.php.net/)
[![License](https://img.shields.io/badge/License-Apache--2.0-blue.svg)](LICENSE)
[![ThinkPHP](https://img.shields.io/badge/ThinkPHP-6.0-green.svg)](https://github.com/topthink/framework)

## Table of Contents

- [Introduction](#introduction)
- [Core Features](#core-features)
- [Architecture](#architecture)
- [Directory Structure](#directory-structure)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Application Development Guide](#application-development-guide)
- [Core Components](#core-components)
- [Console Commands](#console-commands)
- [Ecosystem](#ecosystem)
- [Contributing](#contributing)
- [License](#license)

---

## Introduction

**XsFrame** (星数引擎) is a digital infrastructure development engine independently developed by **Xingshu Wei Lai (Hangzhou) Technology Co., Ltd.** Based on **ThinkPHP 6.0**, it is designed for enterprise-grade multi-tenant SaaS scenarios. The engine ships with a multi-application architecture, unified merchant management, payment integration, file storage, SMS services, and complete business components, enabling developers to efficiently deliver digital applications across industries such as finance, manufacturing, education, and healthcare within a single system.

**Official Resources:**
- 🌐 Website: [https://www.xsyq.cn](https://www.xsyq.cn)
- 📖 Documentation: [https://www.xsyq.cn/doc/pc](https://www.xsyq.cn/doc/pc)
- 🎓 Xingshu Academy: [https://www.xsyq.cn/articles](https://www.xsyq.cn/articles)
- 🛒 App Market: [https://www.xsyq.cn/store/app.html](https://www.xsyq.cn/store/app.html)
- 💬 Developer QQ Group: 139779262
- 📞 Developer Phone: +86 13282030470
- 🆔 Developer QQ: 786824455
- 📧 Developer Email: 786824455@qq.com

---

## Core Features

### Multi-Terminal Unified Architecture
- **PC**: Desktop browser application with full admin management
- **H5**: Mobile-adapted pages, one codebase for all devices
- **API**: Standardized JSON API, cross-platform ready
- **WeChat Ecosystem**: Mini Program, Official Account, Enterprise WeChat full-chain integration

### Multi-Merchant (Uniacid) System
- Merchant isolation via `uniacid`, supporting multi-tenant operations on one system
- Independent domain binding, automatic merchant detection, role-based access (owner / manager)
- Merchant-level config overrides system defaults for flexible business adaptation

### Enterprise Business Components
| Category | Components |
|----------|-----------|
| Payments | WeChat Pay (Native/App/H5/JSAPI), Alipay (App/Web/Mobile), Shouqianba |
| File Storage | Local, Alibaba Cloud OSS, Tencent Cloud COS, Qiniu Cloud |
| SMS | Alibaba Cloud SMS, Tencent Cloud SMS |
| Logistics | Real-time express tracking for major carriers |
| AI | Alibaba Cloud VOD, custom AI service integration |
| Data Export | PhpSpreadsheet full Excel export support |
| Others | Email (PHPMailer), QR Code (QRCode), Barcode (BCG) |

### Standardized Development Patterns
- Unified controller base classes (`AdminBaseController` / `ApiBaseController` / `MobileBaseController` / `WebBaseController`)
- Facade pattern for accessing all core services statically
- `AdminTraits` for reusable CRUD operations in admin panels
- Application lifecycle management: install / uninstall / upgrade with manifest.xml

---

## Architecture

```
┌──────────────────────────────────────────────────────────┐
│                      Application Layer (app/)             │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌────────────────┐ │
│  │ API     │ │ PC      │ │ H5      │ │ Admin Panel    │ │
│  └─────────┘ └─────────┘ └─────────┘ └────────────────┘ │
├──────────────────────────────────────────────────────────┤
│              Framework Core (vendor/xsframe/)            │
│  ┌────────────────────────────────────────────────────┐ │
│  │ Controller │ Service │ Facade │ Middleware          │ │
│  │ Enum/Exception │ Util │ Wrapper │ Traits            │ │
│  └────────────────────────────────────────────────────┘ │
├──────────────────────────────────────────────────────────┤
│                ThinkPHP 6.0 Foundation                  │
│  ORM │ Cache │ Session │ Log │ Middleware │ Console      │
├──────────────────────────────────────────────────────────┤
│                    PHP 7.4+ / PHP 8.x                   │
└──────────────────────────────────────────────────────────┘
```

### Layer Responsibilities

| Layer | Description |
|-------|-------------|
| **Application Layer** | Business code written by developers, isolated by module name, following unified directory conventions |
| **Framework Core Layer** | `xsframe/framework` package providing all base classes, services, and utilities with unified versioning |
| **Foundation Layer** | ThinkPHP 6.0 providing routing, container, middleware, ORM, and other infrastructure |

---

## Directory Structure

```
xsframe/                          # XsFrame root directory
│
├─app/                            # Application root (all business code goes here)
│  ├─{app_name}/                  # Application directory (by identifier name)
│  │  ├─common.php               # Application-level common functions
│  │  ├─controller/
│  │  │  ├─api/                 # API controllers (external JSON endpoints)
│  │  │  ├─pc/                  # PC controllers
│  │  │  ├─mobile/              # H5 mobile controllers
│  │  │  ├─web/                # Admin controllers (supports 2-level subdirs)
│  │  │  │  └─{module}/{Class}.php
│  │  ├─enum/                    # Enum classes
│  │  ├─facade/                  # Application facades
│  │  ├─middleware/               # Application middleware
│  │  ├─model/                   # Model directory
│  │  ├─packages/                # Application resources
│  │  │  ├─mobile/              # H5 static assets
│  │  │  ├─pc/                  # PC static assets
│  │  │  ├─static/              # Shared static assets
│  │  │  ├─icon.png             # Application icon
│  │  │  ├─install.php           # Install SQL file (required)
│  │  │  ├─manifest.xml          # Manifest config (required)
│  │  │  ├─uninstall.php        # Uninstall SQL
│  │  │  └─upgrade.php          # Upgrade SQL
│  │  ├─view/                    # View templates
│  │  │  ├─web/                # Admin templates (2-level subdirs)
│  │  │  ├─mobile/              # H5 templates
│  │  │  └─pc/                  # PC templates
│  │  ├─config/                  # App config
│  │  │  └─menu.php            # Admin menu config
│  │  └─route/                   # Route definitions
│  │     ├─api.php             # API routes
│  │     ├─pc.php              # PC routes
│  │     └─mobile.php          # H5 routes
│  │
│  ├─common.php                  # Global common functions
│  └─event.php                   # Global event definitions
│
├─config/                         # Global config files
│  ├─app.php                     # Application config
│  ├─cache.php                   # Cache config
│  ├─console.php                 # Console config
│  ├─cookie.php                  # Cookie / CORS config
│  ├─database.php                # Database config
│  ├─filesystem.php              # Filesystem disk config
│  ├─lang.php                    # Multi-language config
│  ├─log.php                     # Log config
│  ├─middleware.php              # Global middleware config
│  ├─route.php                   # Route config
│  ├─session.php                 # Session config
│  ├─trace.php                   # Trace config
│  └─view.php                    # View engine config
│
├─public/                         # Web root (publicly accessible)
│  ├─index.php                  # Entry point
│  └─.htaccess                  # Apache URL rewrite rules
│
├─runtime/                        # Runtime directory (writable)
├─vendor/                         # Composer dependencies
│  └─xsframe/framework/          # XsFrame core package
│     └─src/xsframe/
│        ├─base/                 # Base controllers/models/services
│        ├─service/             # Core business services
│        ├─facade/              # Facades (service entry points)
│        ├─wrapper/              # Wrappers (sys/merchant/module)
│        ├─enum/                # Enum classes
│        ├─exception/            # Exception handlers
│        ├─middleware/            # Middleware classes
│        ├─util/                # Utility classes
│        ├─library/             # Third-party libs (QR/barcode)
│        ├─pay/                 # Payment (WeChat/Alipay)
│        ├─traits/              # Reusable traits
│        └─function/            # Global helper functions
│
├─composer.json                  # Composer definition
├─.example.env                   # Environment variable template
├─think                          # CLI entry point
├─LICENSE                        # Apache-2.0 License
└─README.md                      # Chinese documentation
```

---

## Installation

### Requirements

| Item | Requirement |
|------|------------|
| PHP | 7.4 ~ 8.2 |
| Database | MySQL 5.7+ (requires `mysqli` extension) |
| PHP Extensions | gd / json / openssl / curl / mbstring / fileinfo / zip / bcmath / xmlwriter / exif / iconv / mysqli |
| Web Server | Nginx 1.18+ or Apache 2.4+ (requires mod_rewrite) |

### Installation Steps

**Method 1: Via Source Package**

1. Purchase a domain and prepare a server
2. Install LNMP environment (recommended: BT Panel / 宝塔面板)
3. Add a site in BT Panel, set 777 write permissions
4. Download the XsFrame framework archive, upload and extract to site root
5. Configure URL rewrite rules, open_basedir, SSL certificate
6. Visit your domain and follow the setup wizard to configure database and admin account
7. Log into admin panel and configure site info (Site ID + Auth Key)

**Method 2: Via Composer**

```bash
composer require xsframe/framework
```

**.env Configuration Example**

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

### Start Development Server

```bash
cd xsframe
php think run
# Visit http://localhost:8000
```

---

## Quick Start

### Creating Your First Application

XsFrame uses a multi-application architecture. Each application corresponds to one business module. This guide uses `xs_form` (a form case application) as a complete example.

#### 1. Create Application Directory Structure

```
app/
└─xs_form/
   ├─config/menu.php             # Admin menu config
   ├─controller/
   │  ├─api/Index.php           # API controller
   │  ├─web/Sets.php            # Settings controller
   │  ├─web/form/Basic.php     # Basic form controller
   │  └─web/form/Module.php    # Form module controller
   ├─model/                      # Model directory
   ├─packages/
   │  ├─icon.png
   │  ├─install.php             # Table creation SQL
   │  ├─manifest.xml           # Application manifest (required)
   │  ├─uninstall.php          # Uninstall SQL
   │  └─upgrade.php            # Upgrade SQL
   ├─route/api.php             # API routes
   └─view/web/
       ├─form/basic/
       │  ├─list.html           # List page
       │  ├─post.html          # Create/edit page
       │  └─table.html         # Modal dialog page
       └─sets/
           └─module.html         # Settings page
```

#### 2. Configure Manifest (manifest.xml)

The manifest file at `packages/manifest.xml` is **required** for application installation:

```xml
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="https://www.xsyq.cn" versionCode="1.0">
    <application setting="true">
        <name><![CDATA[Form Cases]]></name>
        <identifie><![CDATA[xs_form]]></identifie>
        <version><![CDATA[1.0.2]]></version>
        <type><![CDATA[business]]></type>
        <ability><![CDATA[Common Form Cases]]></ability>
        <description><![CDATA[Common Admin Form Management]]></description>
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

#### 3. Write Install SQL (install.php)

```php
<?php
$sql = "
CREATE TABLE `ims_xs_form_data_basic` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT 'Name',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT 'Description',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Enabled: 0=No 1=Yes',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT 'Create Time',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Deleted: 0=No 1=Yes',
  `isrecommand` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Recommended',
  `isnew` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is New',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is Hot',
  `type` int(10) NOT NULL DEFAULT '0' COMMENT 'Type',
  `author_id` int(10) NOT NULL DEFAULT '0' COMMENT 'Author ID',
  `education` varchar(50) NOT NULL DEFAULT '' COMMENT 'Education (multi-select)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Form Data Table';
";
return $sql;
```

---

## Application Development Guide

### 1. Admin Controller

All admin controllers must extend `xsframe\base\AdminBaseController`. The base class automatically handles:

- Login state verification (auto redirect or 403 response)
- Merchant (uniacid) context injection
- Admin menu loading and permission checks
- View variable injection (module / controller / action / uid / uniacid, etc.)

```php
<?php
namespace app\xs_form\controller\web\form;

use xsframe\base\AdminBaseController;
use xsframe\facade\service\DbServiceFacade;

class Basic extends AdminBaseController
{
    // Table name WITHOUT ims_ prefix
    protected $tableName = "xs_form_data_basic";

    // Open a modal dialog
    public function table()
    {
        return $this->template("table");
    }

    // Initialization hook (optional)
    public function _admin_initialize()
    {
        parent::_admin_initialize();
        // Custom initialization logic
    }
}
```

### 2. API Controller

All external API controllers must extend `xsframe\base\ApiBaseController`. The base class provides:

- Automatic CORS handling (OPTIONS requests intercepted automatically)
- Unified JSON response format `{code, msg, data}`
- Automatic UTF-8 encoding conversion (compatible with GBK environments)
- Get current logged-in user ID

```php
<?php
namespace app\xs_form\controller\api;

use xsframe\base\ApiBaseController;

class Index extends ApiBaseController
{
    public function index(): \think\response\Json
    {
        $result = ['name' => 'John Doe'];
        return $this->success($result);
    }

    public function detail(): \think\response\Json
    {
        $id = $this->request->param('id');
        return $this->success(['id' => $id]);
    }
}
```

### 3. Admin Menu Configuration (menu.php)

```php
<?php
$menu = [
    'form' => [
        'title'    => 'Forms',
        'subtitle' => 'Form Cases',
        'icon'     => 'icon-file-text-o',
        'items'    => [
            // 1st-level route: route = method name
            ['title' => 'Basic Form', 'route' => 'basic/main'],
            // 2nd-level route: route = class/method name
            ['title' => 'Module Form', 'route' => 'module/main'],
            ['title' => 'Icon Manager', 'route' => 'icon/main'],
        ]
    ],
    'sets' => [
        'title'    => 'Settings',
        'subtitle' => 'Settings Management',
        'icon'     => 'icon-cog',
        'items'    => [
            ['title' => 'App Settings', 'route' => 'module'],
        ]
    ],
];
return $menu;
```

**Route Rules:**

| Route Type | `route` Format | Controller Path | View Path |
|------------|----------------|-----------------|-----------|
| 1st-level | `main` | `controller/web/{module}/Main.php` | `view/{module}/{sub}/main.html` |
| 2nd-level | `basic/main` | `controller/web/{module}/basic/Main.php` | `view/{module}/{sub}/basic/main.html` |
| 1st-level | `sets` | `controller/web/{module}/Sets.php` | `view/{module}/sets/list.html` |

> **Note**: `controller/web` supports up to **2 levels** of subdirectories (`Sets.php` = level 1, `basic/Main.php` = level 2).

### 4. Route Configuration (route/api.php)

```php
<?php
use think\facade\Route;

Route::group('api', function () {

    // Public endpoints (no login required)
    Route::group(function () {
        Route::any('home/index', 'api.index/index');
    })->allowCrossDomain();

    // Authenticated endpoints
    Route::group(function () {
        Route::any('user/info', 'api.user/info');
    });

});
```

Full access path example: `http://www.example.com/xs_form/api/home/index.html?i=1`

### 5. View Template Conventions

Views use ThinkPHP native template syntax and extend the admin base template:

**List Page (list.html)**

```html
{extend name="../../admin/view/public/admin"}

{block name='content'}

<div class="page-header">
    <span>Current: <span class="text-primary">Basic Form</span></span>
</div>

<div class="page-content">
    <!-- Toolbar -->
    <form action="" method="get" class="form-horizontal table-search">
        <div class="page-toolbar">
            <div class="col-sm-4">
                <a class="btn btn-primary btn-sm"
                   href="{:webUrl('form.basic/add')}">
                   <i class="icon icon-plus"></i> Add
                </a>
            </div>
        </div>
    </form>

    {if empty($list)}
        <div class="panel panel-default">
            <div class="panel-body empty-data">No data found</div>
        </div>
    {else/}
        <table class="table table-responsive">
            <thead>
            <tr>
                <th style="width:40px;"><input type="checkbox"></th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            {foreach $list as $row}
            <tr>
                <td><input type="checkbox" value="{$row['id']}" class="checkone"/></td>
                <td>{$row['name']}</td>
                <td>
                    <span class="label {if $row['enabled']==1}label-primary{else}label-default{/if}">
                        {if $row['enabled']==1}Enabled{else}Disabled{/if}
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
                           data-confirm="Confirm delete?">
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

**Create/Edit Page (post.html)**

```html
{extend name="../../admin/view/public/admin"}

{block name='content'}

<div class="page-header">
    <span class="text-primary">{if !empty($item)}Edit{else}Create{/if} Form</span>
</div>

<div class="page-content">
    <form action="" method="post" class="form-validate form-horizontal">
        <input type="hidden" name="id" value="{$item['id']}"/>

        <div class="form-group">
            <label class="col-lg-2 control-label must">Sort Order</label>
            <div class="col-sm-9 col-xs-12">
                <input type="text" name="displayorder"
                       class="form-control"
                       value="{:intval($item['displayorder'])}"
                       data-rule-required='true'/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label must">Name</label>
            <div class="col-sm-9 col-xs-12">
                <input type="text" name="name"
                       class="form-control"
                       value="{$item['name']}"
                       data-rule-required='true'
                       data-msg-required='Please enter a name'/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label">Enabled</label>
            <div class="col-sm-9 col-xs-12">
                <label class="radio-inline">
                    <input type="radio" name="enabled" value="1"
                           {if $item['enabled']==1}checked{/if}> Yes
                </label>
                <label class="radio-inline">
                    <input type="radio" name="enabled" value="0"
                           {if $item['enabled']==0 || empty($item)}checked{/if}> No
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-2 control-label"></label>
            <div class="col-sm-9">
                <input type="submit" class="btn btn-primary" value="Save"/>
                <a class="btn btn-default" href="{:webUrl('form.basic/main')}">Back to List</a>
            </div>
        </div>
    </form>
</div>

{/block}
```

### 6. Standard Field Names

With `$tableName` + `AdminTraits`, CRUD operations are handled automatically. Use these standard field names in your views:

| Field Name | Description | Notes |
|------------|-------------|-------|
| `displayorder` | Sort order | Supports inline editing (ajaxEdit) |
| `enabled` | Enabled flag (0/1) | Auto-renders as toggle switch |
| `deleted` | Soft delete flag (0/1) | Auto-filters deleted records |
| `isrecommand` | Recommended flag | Supports recommend/new/hot tags |
| `isnew` | New flag | Renders with tag styles |
| `ishot` | Hot flag | Renders with tag styles |
| `createtime` | Create timestamp | Auto-formatted display |

### 7. Common Template Tags

| Tag | Description |
|-----|-------------|
| `{:webUrl('module.controller/action', [params])}` | Generate admin URL |
| `{:url('module/controller/action', [params])}` | Generate plain URL |
| `{:tpl_form_field_image('thumb', $item['thumb'])}` | Single image upload |
| `{:tpl_form_field_multi_image('thumbs', $item['thumbs'])}` | Multi-image upload with history |
| `{:tpl_form_field_date('date_time', $date)}` | Date picker |
| `{:tpl_form_field_daterange('time', $config)}` | Date range picker |
| `{:tpl_form_field_calendar('birthday', $date)}` | Calendar picker |
| `{:tpl_form_field_district('address', $address)}` | Province/City/District picker |
| `{:tpl_form_field_color('color', $color)}` | Color picker |
| `{:tpl_form_field_position(['lng'=>$lng,'lat'=>$lat])}` | Map coordinate picker |
| `{:tpl_ueditor('content', $content)}` | Rich text editor |
| `{:tpl_form_field_video2('video_url', $url)}` | Video upload |
| `{:tpl_form_field_audio('audio_url', $url)}` | Audio upload |
| `{:tpl_selector(...)}` | Data selector (modal dialog) |

---

## Core Components

### Controller Base Classes

| Base Class | Use Case | Key Features |
|------------|---------|-------------|
| `xsframe\base\BaseController` | All controllers | uniacid resolution / param injection / view variable init |
| `xsframe\base\AdminBaseController` | Admin panel | Login check / menu loading / permission control / AdminTraits |
| `xsframe\base\ApiBaseController` | API endpoints | CORS handling / unified JSON responses / UTF-8 conversion |
| `xsframe\base\MobileBaseController` | H5 mobile | Mobile adaptation / client context injection |
| `xsframe\base\WebBaseController` | PC frontend | PC page rendering / shared variables |

### Facades

Access core services statically via facades — no manual instantiation required:

```php
use xsframe\facade\service\SysMemberServiceFacade;   // Member service
use xsframe\facade\service\DbServiceFacade;          // Database service
use xsframe\facade\service\FileServiceFacade;       // File service
use xsframe\facade\service\OssServiceFacade;        // Object storage
use xsframe\facade\service\SmsServiceFacade;        // SMS service
use xsframe\facade\service\PayServiceFacade;        // Payment service
use xsframe\facade\service\RedisServiceFacade;      // Cache service
use xsframe\facade\service\AreaServiceFacade;       // Area data
use xsframe\facade\service\ExpressServiceFacade;    // Express tracking
use xsframe\facade\service\WechatServiceFacade;     // WeChat service
use xsframe\facade\service\AliPayServiceFacade;     // Alipay service
use xsframe\facade\service\WxappServiceFacade;       // Mini Program service
use xsframe\facade\service\WxPayServiceFacade;       // WeChat Pay
```

### Utilities

Located in `vendor/xsframe/framework/src/xsframe/util/`:

- `ArrayUtil` — Array manipulation
- `StringUtil` — String utilities (incl. Chinese text handling)
- `ValidateUtil` — Data validation
- `FileUtil` — File operations
- `ImgUtil` — Image processing (resize / crop / watermark)
- `ExcelUtil` — Excel import/export
- `PriceUtil` — Price calculation
- `TimeUtil` — Time/date utilities
- `RandomUtil` — Random token generation
- `PhoneUtil` — Phone number validation
- `PinYinUtil` — Chinese pinyin conversion
- `HtmlUtil` — HTML utilities
- `UrlUtil` — URL processing
- `JsonUtil` — JSON encode/decode
- `AesEncoderUtil` — AES encryption
- `OpensslUtil` — OpenSSL utilities
- `LoggerUtil` — Logging
- `LicenseUtil` — License verification

### Exception Classes

| Exception | Description |
|-----------|-------------|
| `xsframe\exception\BaseException` | Base exception |
| `xsframe\exception\ApiException` | API business exception (auto JSON error response) |
| `xsframe\exception\ThirdException` | Third-party service call exception |
| `xsframe\exception\ExceptionHandler` | Global exception handler |

---

## Console Commands

Using the `think` CLI tool:

```bash
# Start development server
php think run

# Create controller
php think make:controller api/Test

# Create model
php think make:model ModelName

# Create service
php think make:service ServiceName

# Create middleware
php think make:middleware Auth

# Create validator
php think make:validate ValidateName

# Create event
php think make:event EventName

# Create listener
php think make:listener ListenerName
```

---

## Ecosystem

| Resource | URL |
|----------|-----|
| App Market (plugins/templates/source) | [https://www.xsyq.cn/store/app.html](https://www.xsyq.cn/store/app.html) |
| Developer Documentation | [https://www.xsyq.cn/doc/pc](https://www.xsyq.cn/doc/pc) |
| Xingshu Academy (tutorials/cases) | [https://www.xsyq.cn/articles](https://www.xsyq.cn/articles) |
| Detailed Installation Guide | [https://www.xsyq.cn/store/article/47.html](https://www.xsyq.cn/store/article/47.html) |
| Gitee Repository | [https://gitee.com/xingshuos/xsframe](https://gitee.com/xingshuos/xsframe) |
| GitHub Repository | [https://github.com/xingshuos/xsframe](https://github.com/xingshuos/xsframe) |

---

## Contributing

1. **Fork** this repository
2. Create a feature branch: `git checkout -b feat/your-feature-name`
3. Commit your changes: `git commit -m 'Add: some feature'`
4. Push to the branch: `git push origin feat/your-feature-name`
5. Open a **Pull Request** with a clear description of the changes and use case

---

## License

This project is released under the **Apache-2.0** open-source license. It can be used freely in commercial projects.

- XsFrame Copyright © 2023~2024 [Xingshu Wei Lai (Hangzhou) Technology Co., Ltd.](https://www.xsyq.cn)
- ThinkPHP Framework Copyright © 2006-2024 [Shanghai Topthink Information Technology Co., Ltd.](http://www.thinkphp.cn)

See the [LICENSE](LICENSE) file for the full license text.

---

> **Slogan: WE CAN DO IT JUST THINK**
