## [星数引擎框架]
thinkphp6.0作为底层框架，基于ThinkPHP6.0开发

## [框架组织架构]
├─app           		应用根目录
│  ├─app_name           应用目录
│  │  ├─common.php      函数文件
│  │  ├─controller      控制器目录
│  │  │  ├─api          接口控制器
│  │  │  ├─pc           PC控制器
│  │  │  ├─mobile       H5控制器
│  │  │  ├─web          后台控制器
│  │  ├─enum            枚举目录
│  │  ├─facade          门面目录
│  │  ├─middleware      中间件目录
│  │  ├─model           模型目录
│  │  ├─packages        资源包目录
│  │  │  ├─mobile       h5静态资源
│  │  │  ├─pc           pc静态资源
│  │  │  ├─static       静态资源
│  │  │  ├─icon.png     应用图标
│  │  │  ├─install.php  应用安装sql文件
│  │  │  ├─manifest.xml 应用安装配置文件
│  │  │  ├─uninstall.xml应用卸载sql文件
│  │  │  ├─upgrade.xml  应用升级sql文件
│  │  ├─view            视图目录
│  │  ├─config          配置目录
│  │  │  ├─menu.php     后台菜单
│  │  ├─route           路由目录
│  │  │  ├─api.php      接口路由
│  │  │  ├─pc.php       pc端路由
│  │  │  ├─mobile.php   h5端路由
│  │  ├─view            模板目录
│  │  │  ├─web          后台模板
│  │  │  ├─mobile       H5端路由
│  │  │  ├─pc           PC端路由
│  │  └─ ...            更多类库目录
│  │
│  ├─common.php         公共函数文件
│  └─event.php          事件定义文件
│
├─config                全局配置目录
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─console.php        控制台配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─filesystem.php     文件磁盘配置
│  ├─lang.php           多语言配置
│  ├─log.php            日志配置
│  ├─middleware.php     中间件配置
│  ├─route.php          URL和路由配置
│  ├─session.php        Session配置
│  ├─trace.php          Trace配置
│  └─view.php           视图配置
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                Composer类库目录
│  ├─xsframe            框架核心包
├─.example.env          环境变量示例文件
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件


## [应用开发规范]
基于星数引擎框架，所有的应用层开发在app文件夹下，不用考虑系统其他文件夹

## [案例应用组织架构]
├─xs_form           	表单案例应用
│  │  ├─config          配置目录
│  │  │  ├─menu.php     后台菜单
│  │  ├─controller      控制器目录
│  │  │  ├─api          接口端文件（可以多级目录设置）
│  │  │  │  │─Index.php 接口控制器文件
│  │  │  ├─pc           PC端文件（可以多级目录设置）
│  │  │  │  │─Index.php PC端控制器文件
│  │  │  ├─mobile       H5端文件（可以多级目录设置）
│  │  │  │  │─Index.php H5端控制器文件
│  │  │  ├─web          后台文件（可以多级目录设置）
│  │  │  │  ├─form          后台文件
│  │  │  │  │  │─Basic.php  后端控制器文件
│  │  │  │  │  │─Icon.php  	后端控制器文件
│  │  │  │  │  │─Module.php 后端控制器文件
│  │  │  │  │─Sets.php  后端控制器文件
│  │  ├─enum            枚举目录
│  │  ├─facade          门面目录
│  │  ├─middleware      中间件目录
│  │  ├─model           模型目录
│  │  ├─packages        资源包目录
│  │  │  ├─mobile       h5静态资源
│  │  │  ├─pc           pc静态资源
│  │  │  ├─static       静态资源
│  │  │  ├─icon.png     应用图标
│  │  │  ├─install.php  应用安装sql文件
│  │  │  ├─manifest.xml 应用安装配置文件
│  │  │  ├─uninstall.xml应用卸载sql文件
│  │  │  ├─upgrade.xml  应用升级sql文件
│  │  ├─route           路由目录
│  │  │  ├─api.php      接口路由
│  │  │  ├─pc.php       pc端路由
│  │  │  ├─mobile.php   h5端路由
│  │  ├─view            模板目录
│  │  │  ├─web          后台模板
│  │  │  │  ├─form      后台文件
│  │  │  │  │  │─basic  后端basic页面相关
│  │  │  │  │  │  │─list.html  列表页面
│  │  │  │  │  │  │─post.html  详情及修改页面
│  │  │  │  │  │  │─table.html  模态框页面
│  │  │  │  │  │  │─tableListTpl.html  模态框页面
│  │  │  │  ├─sets      应用设置
│  │  │  │  │  │─tab  	页面tab
│  │  │  │  │  │  │─basic.html  列表页面
│  │  │  │  │  │─module.html  	配置页面
│  │  │  ├─mobile       H5端路由
│  │  │  │  ├─index     H5端文件
│  │  │  │  │  │─index.html  访问首页
│  │  │  ├─pc           PC端路由
│  │  ├─common.php      应用公共函数文件


## [案例应用具体对应的文件内容如下]

**1.应用菜单配置格式必须遵守以下格式**

**备注1: controller/web 目录，最多支持两级目录，比如1级:controller/web/Sets.php 比如2级:controller/web/form/Basic.php**
**备注2: 一级路由对应view视图 例如: view/sets/list.html,view/sets/post.html,view/sets/query.html等**
**备注3: 二级路由对应view视图 例如: view/form/basic/list.html,view/form/basic/post.html,view/form/basic/query.html等**
**备注4: 一级路由写法 items 数组中 route 列表写法 main ,query ,table 等等 语法: 方法名**
**备注5: 二级路由写法 items 数组中 route 列表写法 basic/main ,module/main , icon/main，其他写法 basic/list ,module/table , icon/post 等等 语法: 类名/方法名**
**参考下方: xs_form/config/menu.php 配置**

```php
<?php

// xs_form/config/menu.php

$menu = [
    'form' => [
        'title'    => '表单',
        'subtitle' => '表单案例',
        'icon'     => 'icon-file-text-o',
        'items'    => [
            [
                'title' => '基础表单',
                'route' => 'basic/main',
            ],
            [
                'title' => '组件表单',
                'route' => 'module/main',
            ],
            [
                'title' => '系统图标',
                'route' => 'icon/main',
            ],
        ]
    ],
    'sets' => [
        'title'    => '设置',
        'subtitle' => '设置管理',
        'icon'     => 'icon-cog',
        'items'    => [
            [
                'title' => '应用设置',
                'route' => 'module',
            ],
        ]
    ],
];
return $menu;
```

**2.API接口格式必须遵守以下格式**

```php
<?php

namespace app\xs_form\controller\api;

use xsframe\base\ApiBaseController;

class Index extends ApiBaseController
{
    public function index(): \think\response\Json
    {
        $result = [
            'name' => '张三',
        ];
        return $this->success($result);
    }
}

```


**3.后台操作格式必须遵守以下格式,显示修改编辑已经存在的根据实际情况重写或直接调用AdminTraits类的方法**

**备注:tableName不需要写ims_前缀**

```php
<?php

namespace app\xs_form\controller\web\form;

use xsframe\base\AdminBaseController;
use xsframe\facade\service\DbServiceFacade;

class Basic extends AdminBaseController
{
    protected $tableName = "xs_form_data_basic";

    // 引入模态框
    public function table()
    {
        return $this->template("table");
    }
}

```



**应用安装数据表配置**

```php
<?php
// 基本表结构目录地址: xs_form/packages/install.php
$sql = "
CREATE TABLE `ims_xs_form_data_basic` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '简介',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 0否 1是',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  `isrecommand` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐 0否 1是',
  `isnew` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最新 0否 1是',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最热 0否 1是',
  `type` int(10) NOT NULL DEFAULT '0' COMMENT '类型',
  `author_id` int(10) NOT NULL DEFAULT '0' COMMENT '作者id',
  `education` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '学历多选',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='常用表单保存数据';

return $sql;
```

**应用基础信息配置**

```php
<?xml version="1.0" encoding="utf-8"?>
<!--目录地址: xs_form/packages/manifest.xml-->
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


**应用卸载删除数据表**

```php
<?php

// 目录地址: xs_form/packages/uninstall.php

$sql = "
   DROP TABLE IF EXISTS `ims_xs_form_data_basic`;
";

return $sql;
```

**接口路由设置开发规范范例**

```php
<?php
// 目录地址: xs_form/route/api.php
use think\facade\Route;

Route::group('api', function () {

    // 无需登录
    Route::group(function () {
        Route::any('home/index', 'api.index/index'); // 完整访问路径 http://www.xsframe.com/xs_form/api/home/index.html?i=1
    })->allowCrossDomain();

    // 需要登陆
    Route::group(function () {

    });

});

```


**查询显示列表页面开发规范范例（查询方式尽可能使用下方查询方式，在一行显示即可）**

```
<form action="" method="get" class="form-horizontal table-search" role="form" id="search">
    <input type="hidden" name="status" value="{$status}"/>
    <input type="hidden" name="refund" value="{$_GET['refund']}"/>
    <div class="page-toolbar">
        <div class="input-group">
            <span class="input-group-select">
                <select name="paytype" class="form-control" style="width:100px;padding:0 5px;">
                    <option value="" {if $_GET['paytype']==''}selected{/if}>支付方式</option>
                    {foreach $paytype as $key => $type}
                    <option value="{$key}" {if $_GET['paytype'] == "$key"} selected="selected" {/if}>{$type['name']}</option>
                    {/foreach}
                </select>
            </span>
            <span class="input-group-select">
                <select name='searchtime' class='form-control' style="width:100px;padding:0 5px;" id="searchtime">
                    <option value=''>不按时间</option>
                    <option value='create' {if $_GET['searchtime']=='create'}selected{/if}>下单时间</option>
                    <option value='pay' {if $_GET['searchtime']=='pay'}selected{/if}>付款时间</option>
                    <option value='send' {if $_GET['searchtime']=='send'}selected{/if}>发货时间</option>
                    <option value='finish' {if $_GET['searchtime']=='finish'}selected{/if}>完成时间</option>
                </select>
            </span>
            <span class="input-group-btn">
                {:tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d H:i', $starttime),'endtime'=>date('Y-m-d H:i', $endtime)),true);}
            </span>
            <span class="input-group-select">
                <select name='searchfield' class='form-control' style="width:110px;padding:0 5px;">
                    <option value='ordersn' {if $_GET['searchfield']=='ordersn'}selected{/if}>订单号</option>
                    <option value='member' {if $_GET['searchfield']=='member'}selected{/if}>会员信息</option>
                    <option value='address' {if $_GET['searchfield']=='address'}selected{/if}>收件人信息</option>
                    <option value='location' {if $_GET['searchfield']=='location'}selected{/if}>地址信息</option>
                    <option value='expresssn' {if $_GET['searchfield']=='expresssn'}selected{/if}>快递单号</option>
                    <option value='goodstitle' {if $_GET['searchfield']=='goodstitle'}selected{/if}>商品名称</option>
                    <option value='goodssn' {if $_GET['searchfield']=='goodssn'}selected{/if}>商品编码</option>
                </select>
            </span>
            <input type="text" class="form-control input-sm" name="keyword" id="keyword" value="{$_GET['keyword']}" placeholder="请输入关键词"/>
            <input type="hidden" name="export" id="export" value="0">
            <span class="input-group-btn">
                <button type="button" data-export="0" class="btn btn-primary btn-submit"> 搜索</button>
                <button type="button" data-export="1" class="btn btn-success btn-submit">导出</button>
            </span>
        </div>
    </div>
</form>
```

```php
<!-- 对应目录地址 table列表案例: xs_form/view/web/form/basic/list.html -->
{extend name="../../admin/view/public/admin"}

{block name='content'}

<div class="page-header">
    <span>
        当前位置：<span class="text-primary">基础表单</span>
    </span>
</div>
<div class="page-content">
    <form action="" method="get" class="form-horizontal table-search" role="form">
        <div class="page-toolbar">
            <div class="col-sm-4">
                <a class="btn btn-primary btn-sm" href="{:webUrl('form.basic/add',['type' => $type])}"><i class="icon icon-plus"></i> 添加表单</a>
            </div>
            <div class="col-md-8 input-group">
                <input type="hidden" name="kwFields" value="name">
                <input type="text" class="form-control" name="keyword" value="{$_GET['keyword']}" placeholder="可搜索表单名称">
                <span class="input-group-btn">
                    <button class="btn btn-primary" type="submit"> 搜索</button>
                </span>
            </div>
        </div>
    </form>

    {if empty($list)}
    <div class="panel panel-default">
        <div class="panel-body empty-data">未查询到相关数据</div>
    </div>
    {else/}
    <div class="row">
        <div class="col-md-12">
            <div class="page-table-header">
                <input type="checkbox">
                <div class="btn-group" style="margin-left: 18px;">
                    <button class="btn btn-default btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要删除?" data-href="{:url('form.basic/delete')}">
                        <i class="icow icow-shanchu1"></i> 删除
                    </button>
                </div>
            </div>
            <table class="table table-responsive">
                <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th style='width:80px;'>
                        <div class="btn-group sort-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                综合排序 <span class="icon icon-filter"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li class="sort-li {if $_GET['sort'] == 'displayorder' && $_GET['order'] == 'asc'}active{/if}" onclick="tableFieldSort('displayorder','asc')">
                                    <a><span class="icon icon-long-arrow-up"></span> 升序</a></li>
                                <li class="sort-li {if $_GET['sort'] == 'displayorder' && $_GET['order'] == 'desc'}active{/if}" onclick="tableFieldSort('displayorder','desc')">
                                    <a><span class="icon icon-long-arrow-down"></span> 降序</a></li>
                                <li class="sort-li" onclick="tableFieldSort()"><a><span class="icon icon-remove"></span> 清除</a>
                                </li>
                            </ul>
                        </div>
                    </th>
                    <th style='width:140px'>表单名称</th>
                    <th style="width: 80px;">状态</th>
                    <th style="width: 180px;">操作</th>
                </tr>
                </thead>
                <tbody>
                {foreach $list as $key=>$row }
                <tr>
                    <td style="position: relative; ">
                        <input type='checkbox' value="{$row['id']}" class="checkone"/>
                    </td>
                    <td class='full'>
                        <a href='javascript:;' data-toggle="ajaxEdit" data-href="{:webUrl('form.basic/change',array('type'=>'displayorder','id'=>$row['id']))}">{$row['displayorder']}</a>
                        <i class="icow icow-weibiaoti-- " data-toggle="ajaxEdit2"></i>
                    </td>
                    <td class='full'>
                        <span>
                            <span style="display: block;width: 100%;">
                                <a href='javascript:;' data-toggle="ajaxEdit" data-href="{:webUrl('form.basic/change',array('type'=>'name','id'=>$row['id']))}">{$row['name']}</a>
                                <i class="icow icow-weibiaoti-- " data-toggle="ajaxEdit2"></i>
                            </span>
                            {if $row['group_name']}
                            <span class="catetag">
                                 <span class="text-danger">服务人员:[{$row['group_name']}]</span>
                            </span>
                            {/if}
                        </span>
                    </td>
                    <td>
                        <span class='label {if $row['enabled']==1}label-primary{else}label-default{/if}'
                        data-toggle='ajaxSwitch'
                        data-switch-value='{$row['enabled']}'
                        data-switch-value0='0|隐藏|label label-default|{:webUrl('form.basic/change',array('type' => 'enabled','value'=>1,'id'=>$row['id']))}'
                        data-switch-value1='1|显示|label label-primary|{:webUrl('form.basic/change',array('type'=>'enabled','value' => 0,'id'=>$row['id']))}'
                        >
                        {if $row['enabled']==1}显示{else}隐藏{/if}
                        </span>
                    </td>
                    <td style="overflow:visible;">
                        <div class="btn-group">
                            <a href="javascript:;" class='btn btn-op btn-operation js-clip' data-url="{$row['id']}">
                                <span data-toggle="tooltip" data-placement="top"  data-original-title="复制">
                                    <i class='icon icon-link'></i>
                                </span>
                            </a>
                            <a class='btn btn-op btn-operation' href="{:webUrl('form.basic/edit', array('id' => $row['id'],'page'=>$page))}">
                                <span data-toggle="tooltip" data-placement="top" title="" data-original-title="编辑">
                                    <i class="icon icon-edit"></i>
                                </span>
                            </a>
                            <a class="btn btn-op btn-operation" data-toggle='ajaxRemove' href="{:webUrl('form.basic/delete',array('id' => $row['id']));}" data-confirm="确定要删除该表单吗？">
                                 <span data-toggle="tooltip" data-placement="top" title="" data-original-title="删除表单">
                                   <i class='icon icon-trash'></i>
                                </span>
                            </a>
                            <a data-toggle='ajaxModal' href="{:webUrl('form.basic/table', array('id' => $row['id']))}" class="btn btn-op btn-operation">
                                <span data-toggle="tooltip" data-placement="top" data-original-title="查看列表">
                                    <i class='icon icon-eye'></i>
                                </span>
                            </a>
                        </div>
                    </td>
                </tr>
                {/foreach}

                </tbody>
                <tfoot>
                <tr>
                    <td><input type="checkbox"></td>
                    <td colspan="1">
                        <div class="btn-group">
                            <button class="btn btn-default btn-sm btn-operation" type="button" data-toggle='batch-remove' data-confirm="确认要删除?" data-href="{:url('form.basic/delete')}">
                                <i class="icow icow-shanchu1"></i> 删除
                            </button>
                        </div>
                    </td>
                    <td colspan="3" class="text-right">
                        {$pager | raw}
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    {/if}
</div>
{/block}
```

**表单提交开发规范范例**

-- 表单中变量使用 {$item['name']} 不要使用 {$item['name']|default=''}
-- 表单验证遵循 设置 data-rule-required='true' data-msg-required='请输入XX' 系统已集成验证js

```php

<!-- 表单提交: xs_form/view/web/form/basic/post.html -->
{extend name="../../admin/view/public/admin"}

{block name='content'}

<div class="page-header">
    当前位置：<span class="text-primary">{if !empty($item)}编辑{else}新建{/if}表单
    	<small>{if !empty($item)}(名称: {$item['company']}){/if}</small>
    </span>
</div>

<div class="page-content">

    <form action="" method="post" class="form-validate form-horizontal ">
        <input type="hidden" name="id" value="{$item['id']}"/>
        <input type="hidden" name="type" value="{$type}"/>

        <div class="form-group-title">表单基本信息</div>

        <div class="form-group">
            <label class="col-lg control-label must">文本</label>
            <div class="col-sm-9 col-xs-12">
                <div class="form-control-static">这是静态的不可编辑的内容</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label must">排序</label>
            <div class="col-sm-9 col-xs-12">
                <input type="text" name="displayorder" class="form-control" value="{:intval($item['displayorder'])}" data-rule-required='true'/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label must">表单名称</label>
            <div class="col-sm-9 col-xs-12">
                <input type="text" name="name" class="form-control" value="{$item['name']}" data-rule-required='true'/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label must">简单描述</label>
            <div class="col-sm-9 col-xs-12">
                <textarea name="description" rows="4" placeholder="请输入简单描述" class="form-control" data-rule-required="true">{$item['description']}</textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">显示状态</label>
            <div class="col-sm-9 col-xs-12">
                <label class="radio-inline">
                    <input type="radio" name="enabled" value="1" {if $item['enabled']==1}checked="checked"{/if}> 显示
                </label>
                <label class="radio-inline">
                    <input type="radio" name="enabled" value="0" {if $item['enabled']==0 || empty($item)}checked="checked"{/if}> 隐藏
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">属性</label>

            <div class="col-sm-9 col-xs-12">
                <label for="isrecommand" class="checkbox-inline">
                    <input type="checkbox" name="isrecommand" value="1" id="isrecommand" {if $item['isrecommand'] == 1}checked="true"{/if}/> 推荐
                </label>

                <label for="isnew" class="checkbox-inline">
                    <input type="checkbox" name="isnew" value="1" id="isnew" {if $item['isnew'] == 1}checked="true"{/if}/> 最新
                </label>

                <label for="ishot" class="checkbox-inline">
                    <input type="checkbox" name="ishot" value="1" id="ishot" {if $item['ishot'] == 1}checked="true"{/if}/> 最热
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">类型</label>
            <div class="col-sm-9 col-xs-12">
                <select class="form-control" name="type">
                    <option value="0" {if empty($item['type'])}selected{/if}>默认</option>
                    <option value="1" {if $item['type'] == 1}selected{/if}>普通</option>
                    <option value="2" {if $item['type'] == 2}selected{/if}>高级</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">作者</label>
            <div class="col-sm-9 col-xs-12">
                <select class="form-control select2" name="author_id">
                    <option value="0" {if empty($item['author_id'])}selected{/if}>未选择</option>
                    <option value="1" {if $item['author_id'] == 1}selected{/if}>张三</option>
                    <option value="2" {if $item['author_id'] == 2}selected{/if}>李四</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">学历</label>
            <div class="col-sm-9 col-xs-12">
                <select class="form-control select2" name="education[]" multiple=''>
                    <option value="1" {if in_array(1,explode(",",$item['education']))}selected{/if}>小学</option>
                    <option value="2" {if in_array(2,explode(",",$item['education']))}selected{/if}>初中</option>
                    <option value="3" {if in_array(3,explode(",",$item['education']))}selected{/if}>高中</option>
                    <option value="4" {if in_array(4,explode(",",$item['education']))}selected{/if}>大学</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择日期</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_date('date_time',$item['date_time'])}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择日期时间段</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_daterange('time', array('sm'=>true, 'placeholder'=>'购买时间'),true)}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择年月日</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_calendar('birthday',$birthday)}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择时钟</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_clock('time_str',$item['time_str'])}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择地址</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_district('address', $address)}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label must">区域选择</label>
            <div class="col-sm-9 col-xs-12">
                <div id="areas" class="form-control-static">{$item['areas']?$item['areas']:'暂无'}</div>
                <a href="javascript:;" class="btn btn-default" onclick="selectAreas()">选择区域</a>
                <input type="hidden" id='selectedareas' name="areas" value="{$item['areas']}"/>
                <input type="hidden" id='selectedareas_code' name="areas_code" value="{$item['areas_code']}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择颜色</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_color('color',$item['color'])}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">单图样式一</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_image('thumb', $item['thumb'])}
                <span class="help-block">可以展示历史图片，点击图片可删除</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg control-label">单图样式二</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_image2('thumb2', $item['thumb2'])}
                <span class="help-block">无历史图片</span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">多图样式一</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_multi_image('thumbs', $item['thumbs'])}
                <span class="help-block">可以展示历史图片，点击图片可删除</span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">多图样式二</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_multi_image2('thumbs2', $item['thumbs2'])}
                <span class="help-block">无历史图片，可进行排序显示</span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择视频</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_video2('video_url', $item['video_url'])}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">选择音频</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_audio('audio_url', $item['audio_url'])}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">单数据选择器</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_selector('data_id',array('text'=>'name','preview'=>true,'type'=>'image', 'required'=>false,  'thumb'=>'logo','placeholder'=>'名称','buttontext'=>'选择数据', 'items'=>$accountInfo,'url'=>webUrl('form.module/query')))}
                <span class="help-block">在模态框进行选择数据信息</span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">富文本编辑器</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_ueditor('content',$item['content'],array('height'=>'300'))}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">坐标选择器</label>
            <div class="col-sm-9 col-xs-12">
                {:tpl_form_field_position('map',array('lng'=>$item['longitude'],'lat'=>$item['latitude']))}
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">自定义模态框</label>
            <div class="col-sm-9 col-xs-12">
                <div class="input-group">
                    <input type="text" name="" class="form-control" value=""/>
                    <span class="input-group-addon btn-default" data-href="{:webUrl('form.module/model')}" data-toggle="ajaxModal">打开模态框</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg control-label"></label>
            <div class="col-sm-9">
                <input type="hidden" name="backUrl" value="{:webUrl('form.basic/main')}">
                <input type="submit" class="btn btn-primary" value="保存">
                <a class="btn btn-default" href="{:webUrl('form.basic/main')}">返回列表</a>
            </div>
        </div>

    </form>
</div>

{/block}
```

**模态框开发规范范例**

```php

<!-- 对应目录地址 模态框打开页案例1: xs_form/view/web/form/basic/table.html -->
<form action="" method="post" class="form-horizontal form-validate" enctype="multipart/form-data">
    <input type="hidden" name="id" value="{$id}">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">查看列表数据</h4>
            </div>

            <div class="modal-body">

                <div class="tabs-container">

                    <div class="row" style="padding:15px 0;">
                        <div class="input-group">
                            <input type="text" class="form-control search"	placeholder="请输入关键字">
                            <span class="input-group-btn">
							<button type="button" class="btn btn-default pager-nav" page="1">搜索</button>
						</span>
                        </div>
                    </div>

                    <div class="tab-content content">

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <input type="submit" value="确认" class="btn btn-primary btn-lg"/>
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</form>

<script>
    require(['js/web/modal_selector'],function (Mselector) {
        Mselector.init("{:webUrl('form.basic/getTableList')}");
    });
</script>

```


**所有后台管理类继承父类 AdminBaseController**


```php

<?php

namespace xsframe\base;

use think\App;
use think\facade\Cache;
use think\facade\View;
use think\Request;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\exception\ApiException;
use xsframe\traits\AdminTraits;
use xsframe\util\LicenseUtil;
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;
use xsframe\wrapper\MenuWrapper;
use xsframe\wrapper\UserWrapper;

abstract class AdminBaseController extends BaseController
{
    protected $clientBaseType = 'admin';
    protected $isSystem = false;
    protected $adminSession = [];
    protected $_GPC = [];

    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);

        if (method_exists($this, '_admin_initialize')) {

            $this->checkAuth();

            if ($this->module == 'admin') {
                $this->isSystem = true;
            }

            $this->_admin_initialize();
        }
    }

    /**
     * 引入后台通用的traits
     */
    use AdminTraits;

    // 初始化
    public function _admin_initialize()
    {

    }

    // 校验用户登录
    protected function checkAuth()
    {
        $clientName = $this->params['client'] ?? 'web';
        $isFileUpload = strtolower($this->controller) == 'file';
        /*$clientName && $clientName != 'web' TODO zhaoxin 注释图片限定参数 */

        $loginResult = UserWrapper::checkUser();

        if (!empty($loginResult) && !empty($loginResult['adminSession'])) {
            $this->adminSession = $loginResult['adminSession'];
            $this->userId = $this->adminSession['uid'];
            $uniacid = $this->adminSession['uniacid'];

            // 记录操作时间，超过10分钟未操作则退出登录
            $lastOpTime = $_COOKIE['LAST_OP_TIME'] ?? 0;
            if (!empty($lastOpTime) && $lastOpTime < time() - 900) {
                setcookie('LAST_OP_TIME', '', time() - 3600, '/');
                UserWrapper::logout();
                header('location: ' . url('/admin/login'));
                exit();
            } else {
                setcookie('LAST_OP_TIME', time(), time() + 600, '/');
            }
        }

        if ($isFileUpload && $this->params['uid'] && $this->params['module'] != 'admin') {
            $this->userId = intval($this->params['uid']);
            // 调用用户是否登录 TODO 这里是个漏洞，没有校验用户是否登录（1.每个应用重做上传 2.统一登录 3.提供调用登录的接口校验 推荐第三种方式）
        } else {
            if (!empty($loginResult) && !empty($loginResult['adminSession'])) {
                if (!empty($uniacid)) {
                    $this->uniacid = $uniacid;
                    $_COOKIE['uniacid'] = $uniacid;
                }

                if ($this->adminSession['role'] != 'owner' && !empty($_GET['i']) && $_GET['i'] != $uniacid) {
                    if ($this->request->isAjax()) {
                        throw new ApiException("暂无该商户的访问权限，请联系管理员!", 403);
                    } else {
                        exit('权限不足');
                    }
                }
            }

            if (strtolower($this->controller) != 'login') {
                if (!$loginResult['isLogin']) {
                    header('location: ' . url('/admin/login'));
                    exit();
                }
            } else {
                if ($loginResult['isLogin'] && (!in_array($this->action, ['logout', 'verify']))) {
                    $url = UserWrapper::getLoginReturnUrl($loginResult['adminSession']['role'], $loginResult['adminSession']['uid']);
                    header('location: ' . $url);
                    exit();
                }
            }
        }
    }

    // 引入后端模板
    protected function template($name = null, $var = null)
    {
        if (is_array($name)) {
            $var = $name;
            $name = $this->action;
        } else {
            if (empty($name)) {
                $name = $this->action;
            }
        }

        # 解决 使用门面调用会报 未定义数组索引 的错误警告
        error_reporting(E_ALL ^ E_NOTICE);
        $var = $this->getDefaultVars($var);
        return view($name, $var);
    }

    /**
     * 生成静态文件
     * @throws \Exception
     */
    protected function buildHtml($htmlFile = '', $htmlPath = '', $templateFile = '', $templateVars = []): string
    {
        $templateVars = $this->getDefaultVars($templateVars);

        $content = View::fetch($templateFile, $templateVars);
        $htmlPath = !empty($htmlPath) ? $htmlPath : './appTemplate/';
        $htmlFile = $htmlPath . $htmlFile . '.' . config('view.view_suffix');
        $File = new \think\template\driver\File();
        $File->write($htmlFile, $content);
        return $content;
    }

    protected function success($data = [], $code = 1)
    {
        return show_json($code, $data);
    }

    protected function error($data = [], $code = 0)
    {
        return show_json($code, $data);
    }

    protected function successMsg($message, $code = 1)
    {
        return $this->success(["message" => $message], $code);
    }

    protected function errorMsg($message, $code = 0)
    {
        return $this->error(["message" => $message], $code);
    }

    private function getDefaultVars($params = null): array
    {
        if (!empty($this->moduleSetting['basic']) && !empty($this->moduleSetting['basic']['name'])) {
            $this->moduleInfo = array_merge(!empty($this->moduleInfo) ? $this->moduleInfo : [], $this->moduleSetting['basic']);
        }

        $var = [];
        $var['module'] = $this->module;
        $var['controller'] = $this->controller;
        $var['action'] = $this->action;
        $var['uniacid'] = $this->uniacid;
        $var['clientServiceName'] = $this->clientServiceName;
        $var['_GPC'] = $this->params;
        $var['uid'] = $this->userId;
        $var['url'] = $this->url;
        $var['siteRoot'] = $this->siteRoot;
        $var['moduleSiteRoot'] = $this->moduleSiteRoot;
        $var['moduleAttachUrl'] = $this->moduleAttachUrl;
        $var['token'] = RandomUtil::random(8);
        $var['isSystem'] = $this->isSystem;
        $var['userInfo'] = $this->adminSession;
        $var['websiteSets'] = $this->websiteSets;

        $menusList = [];
        if (!empty($this->adminSession) && is_array($this->adminSession)) {
            $menusList = MenuWrapper::getMenusList($this->adminSession['role'], $this->module, $this->controller, $this->action);
        }
        $var['menusList'] = $menusList;
        $var['pageTitle'] = strip_tags(empty($menusList['pageTitle']) ? $this->websiteSets['name'] : $menusList['pageTitle']);

        # 收缩菜单
        $var['foldNav'] = intval($_COOKIE["foldnav"] ?? 0);

        $var['account'] = $this->account;
        $var['moduleInfo'] = $this->moduleInfo;
        $var['attachUrl'] = getAttachmentUrl() . "/";
        $var['isLogin'] = $this->isLogin;

        # 选中系统菜单
        $var['selSystemNav'] = intval($_COOKIE[$this->module . "_systemnav"] ?? 0);
        $var['selSystemNavUrl'] = $this->getSelSystemNavUrl();

        # 菜单通知点
        $var['oneMenuNoticePoint'] = Cache::get($this->module . "_" . SysSettingsKeyEnum::ADMIN_ONE_MENU_NOTICE_POINT) ?? [];
        $var['twoMenuNoticePoint'] = Cache::get($this->module . "_" . SysSettingsKeyEnum::ADMIN_TWO_MENU_NOTICE_POINT) ?? [];

        # AI能力
        $var['aiGenerate'] = m('xs_aidrive', $this->uniacid);

        # 应用数量
        $var['appCount'] = $this->accountHostController->getAppCount($this->uniacid, $this->userId, $this->adminSession['role']);

        if (!empty($params)) {
            $var = array_merge($var, $params);
        }
        if( !isset($params['systemExpireShow']) ){
            # 系统倒计时查询
            $systemExpireShow = 0;
            $systemExpireText = "";
            $systemAuthSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::SYSTEM_AUTH_KEY);
            if (isset($systemAuthSets['need_auth']) && $systemAuthSets['need_auth'] == 1) {
                $license = $systemAuthSets['license'] ?? '';
                if (!empty($license)) {
                    $isNotExpired = LicenseUtil::validateLicense($license, env('AUTHKEY'));
                    $expireTime = LicenseUtil::getExpireTime($license, env('AUTHKEY'));

                    $systemExpireShow = $isNotExpired ? 0 : 1;
                    if ($expireTime - TIMESTAMP <= 7 * 86400) {
                        $systemExpireShow = 1;
                        $systemExpireText = "系统即将到期，请及时续费（到期时间：".date('Y-m-d H:i:s', $expireTime)."）"; # 过期提示信息')."）"; # 过期提示信息
                        if( $expireTime - TIMESTAMP <= 0 ){
                            $systemExpireText = "系统已到期，请及时续费（到期时间：" . date('Y-m-d H:i:s', $expireTime) . "）"; # 过期提示信息')."）"; # 过期提示信息
                        }
                    }
                }
            }

            # 校验用户时长倒计时，商户账号倒计时提醒 TODO
            if( !$systemExpireShow ){

            }

            $var['systemExpireShow'] = $systemExpireShow; # 是否显示提示 0否 1是
            $var['systemExpireText'] = $systemExpireText; # 过期提示信息')."）"; # 过期提示信息
        }

        return $var;
    }

    private function getSelSystemNavUrl()
    {
        $uniacid = $this->uniacid;
        $selSystemNavUrl = $_COOKIE[$this->module . "_systemnavurl"] ?? null;

        if (empty($selSystemNavUrl)) {
            $selSystemNavUrl = url('admin/system/index', ['i' => $uniacid, 'module' => $this->module]);
        } else {
            $urlParts = parse_url($selSystemNavUrl);

            // 解析查询字符串为数组
            parse_str($urlParts['query'], $queryParams);

            // 检查i参数是否存在，并且是否和uniacid不同
            if ((isset($queryParams['i']) && $queryParams['i'] != $uniacid) || empty($queryParams['i'])) {
                // 替换i参数的值
                $queryParams['i'] = $uniacid;

                // 增加module参数
                $queryParams['module'] = $queryParams['module'] ?: $this->module;

                // 重新构建查询字符串
                $newQuery = http_build_query($queryParams);

                // 重新组合URL
                $selSystemNavUrl = $urlParts['path'] . '?' . $newQuery;
            }

        }

        return strval($selSystemNavUrl);
    }
}
```

**所有接口类继承父类 ApiBaseController**

```php

<?php

namespace xsframe\base;

use xsframe\facade\service\SysMemberServiceFacade;

abstract class ApiBaseController extends BaseController
{
    protected $clientBaseType = 'api';

    protected function _initialize()
    {
        if (method_exists($this, '_api_initialize_before')) {
            $this->_api_initialize_before();
        }

        parent::_initialize(); // TODO: Change the autogenerated stub

        if (method_exists($this, '_api_initialize')) {
            $this->_api_initialize();
        }
    }

    // 初始化
    protected function _api_initialize_before()
    {
    }

    // 初始化
    protected function _api_initialize()
    {
        // 解决重复提交与跨域问题 start
        header("Access-Control-Allow-Origin:" . config('cookie.header.Access-Control-Allow-Origin'));
        header("Access-Control-Allow-Headers:" . config('cookie.header.Access-Control-Allow-Headers'));
        header('Access-Control-Allow-Methods:' . config('cookie.header.Access-Control-Allow-Methods'));

        if ($this->request->method() === "OPTIONS") {
            exit();
        }
        // 解决重复提交与跨域问题 end
    }

    /**
     * 正确的数组数据
     * @param array $data
     * @param string $code
     * @param string $message
     * @return \think\response\Json
     */
    protected function success(array $data = [], string $code = "200", string $message = 'success'): \think\response\Json
    {
        $code = $data['code'] ?? $code;
        $message = $data['msg'] ?? $message;

        $retData = [
            'code' => (string)$code,
            'msg'  => $message,
            'data' => $data
        ];
        $retData = $this->utf8ize($retData);
        return json($retData);
    }

    /**
     * 错误的数组数据
     * @param string $message
     * @param string $code
     * @return array
     */
    protected function error(string $message = 'fail', string $code = "404"): array
    {
        $code = $data['code'] ?? $code;
        $message = $data['msg'] ?? $message;

        $retData = [
            'code' => (string)$code,
            'msg'  => $message,
            'data' => [],
        ];
        $retData = $this->utf8ize($retData);
        die(json_encode($retData));
    }

    // 默认获取userid
    protected function getUserId()
    {
        $this->userId = SysMemberServiceFacade::getUserId();
        return $this->userId;
    }

    // 在返回 JSON 前对数据进行编码转换
    public function utf8ize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->utf8ize($value);
            }
        } else if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,ASCII');
        }
        return $data;
    }
}
```

**所有类继承祖类 BaseController**

```php

<?php

namespace xsframe\base;

use think\App;
use think\facade\Config;
use think\Request;
use think\route\dispatch\Controller;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\exception\ApiException;
use xsframe\facade\wrapper\SystemWrapperFacade;
use xsframe\util\ArrayUtil;
use xsframe\util\FileUtil;
use xsframe\util\LoggerUtil;
use xsframe\util\RequestUtil;
use xsframe\util\StringUtil;
use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\SettingsWrapper;

abstract class BaseController extends Controller
{
    protected $uniacid;
    protected $disUniacid = false; # 是否禁用校验uniacid
    protected $request;
    protected $header;
    protected $params;
    protected $pIndex;
    protected $pSize;
    protected $app;
    protected $siteRoot;
    protected $ip;
    protected $view;
    protected $clientBaseType;
    protected $clientServiceName;

    protected $module;
    protected $controller;
    protected $action;

    protected $url;

    protected $isLogin;
    protected $userId;
    protected $userInfo;
    protected $memberInfo;
    protected $iaRoot;
    protected $moduleSiteRoot;
    protected $moduleAttachUrl;
    protected $moduleIaRoot;
    protected $authkey;
    protected $expire;
    protected $attachment;
    protected $settingsController;
    protected $accountHostController;

    protected $websiteSets = [];
    protected $account = [];
    protected $accountSetting = [];
    protected $moduleInfo = [];
    protected $moduleSetting = [];

    public function __construct(Request $request, App $app)
    {
        $this->request = $request;
        $this->header = $request->header();
        $this->app = $app;
        $this->params = $this->request->param();

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
        }

        if (!$this->accountHostController instanceof AccountHostWrapper) {
            $this->accountHostController = new AccountHostWrapper();
        }

        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    /**
     * @throws ApiException
     */
    protected function _initialize()
    {
        $this->authkey = env('AUTHKEY') ?? 'xsframe_';
        $this->expire = 3600 * 24 * 10; // 10天有效期

        $this->view = $this->app['view'];

        $this->pIndex = max(1, intval($this->request->param('page') ?? 1));
        $this->pSize = max(1, intval($this->request->param('size') ?? 10));

        $this->siteRoot = request()->domain();
        if (StringUtil::strexists($this->request->server('HTTP_REFERER'), 'https')) {
            $this->siteRoot = str_replace("http:", "https:", $this->siteRoot);
        }

        if (empty($this->module)) {
            $this->module = app('http')->getName(); // 获取的是真实应用名称不是别名
        }

        $this->iaRoot = str_replace("\\", '/', dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))));

        // 后台运行时需要获取到module的值时可以设置下cookie start
        if (empty($this->module) && !empty($_COOKIE['module'])) {
            $this->module = $_COOKIE['module'];
        }
        // end

        $this->ip = $this->request->ip();

        $this->attachUrl = $this->siteRoot . "/attachment";
        $this->moduleSiteRoot = $this->siteRoot . "/" . $this->module;
        $this->moduleAttachUrl = $this->siteRoot . "/app/" . $this->module;
        $this->moduleIaRoot = $this->iaRoot . "/app/" . $this->module;

        $this->controller = strtolower($this->request->controller());
        $this->action = strtolower($this->request->action());
        $this->url = $this->request->url();
        $this->clientServiceName = explode(".", $this->controller)[0];

        $this->checkCors();
        $this->autoLoad();

        # 验证是否独立数据库应用 start
        if (!is_file($this->moduleIaRoot . "/config/database.php")) {
            $this->getDefaultSets();
        } else {
            $this->getUniacid();
        }
        # 验证是否独立数据库应用 end

        if (method_exists($this, '_initialize2')) {
            $this->_initialize2();
        }

        # 系统授权验证
        $systemAuthSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::SYSTEM_AUTH_KEY);
        if ($systemAuthSets && $systemAuthSets['check_date'] !== date('Ymd')) {
            $needAuthRet = RequestUtil::cloudHttpPost("frame/needAuth", ['authKey' => $this->authkey]);
            $needAuth = $needAuthRet['data']['isNeedAuth'] ?? 0;
            $license = $needAuthRet['data']['license'] ?? '';
            $updateData = ['need_auth' => $needAuth, 'check_date' => date('Ymd')];
            if (empty($systemAuthSets['license'])) {
                $updateData['license'] = $license ?? '';
            }
            $systemAuthSetsData = array_merge($systemAuthSets, $updateData);
            $this->settingsController->setSysSettings(SysSettingsKeyEnum::SYSTEM_AUTH_KEY, $systemAuthSetsData);
        }
    }

    // 初始化2
    protected function _initialize2()
    {
    }

    // 解决重复提交与跨域问题
    private function checkCors()
    {
        header("Access-Control-Allow-Origin:" . config('cookie.header.Access-Control-Allow-Origin'));
        header("Access-Control-Allow-Headers:" . config('cookie.header.Access-Control-Allow-Headers'));
        header('Access-Control-Allow-Methods:' . config('cookie.header.Access-Control-Allow-Methods'));

        if ($this->request->method() === "OPTIONS") {
            exit();
        }
    }

    protected function autoLoad()
    {
        $path = $this->iaRoot . '/vendor/xsframe/framework/src/xsframe/function';
        $files = FileUtil::getDir($path);

        if (!empty($files)) {
            foreach ($files as $fileInfo) {
                if (is_file($fileInfo['path'])) {
                    if (!class_exists($fileInfo['path'])) {
                        include_once $fileInfo['path'];
                    }
                }
            }
        }
    }

    protected function success()
    {

    }

    protected function error()
    {

    }

    /**加载默认配置信息
     * @throws ApiException
     */
    protected function getDefaultSets()
    {
        # 系统网站设置
        $this->websiteSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::WEBSITE_KEY);

        # 设置模块
        $uniacid = $this->getUniacid();

        # 附件设置
        $attachmentSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);

        # 项目信息
        $this->account = $this->settingsController->getAccountSettings($uniacid);
        $this->accountSetting = $this->settingsController->getAccountSettings($uniacid, SysSettingsKeyEnum::SETTING_KEY);

        # 模块信息
        $this->moduleInfo = $this->settingsController->getModuleInfo($this->module);
        $this->moduleSetting = $this->getModuleSettings($uniacid);

        if ($uniacid > 0) {
            $accountSets = $this->account['settings'] ?? [];
            if (!empty($accountSets) && !empty($accountSets['attachment'])) {
                $attachmentSets['remote'] = $accountSets['attachment']['remote'];
            }
        }

        $this->attachment = $attachmentSets;
    }

    // 获取模块配置信息
    protected function getModuleSettings($uniacid)
    {
        $moduleSetting = $this->settingsController->getModuleSettings(null, $this->module, $uniacid);
        if (!empty($moduleSetting)) {
            if (!empty($moduleSetting['basic']))
                $moduleSetting['basic']['logo'] = tomedia($moduleSetting['basic']['logo']);
            if (!empty($moduleSetting['share']))
                $moduleSetting['share']['imageUrl'] = tomedia($moduleSetting['share']['imageUrl']);
            if (!empty($moduleSetting['website'])) {
                $moduleSetting['website']['logo'] = tomedia($moduleSetting['website']['logo']);
                $moduleSetting['website']['favicon'] = tomedia($moduleSetting['website']['favicon']);
                $this->websiteSets = $moduleSetting['website'];
            }
        }
        return $moduleSetting;
    }

    // 获取项目uniacid
    protected function getUniacid($checkUrl = false)
    {
        if (empty($this->uniacid)) {
            $uniacid = $this->params['uniacid'] ?? ($_GET['i'] ?? ($_COOKIE['uniacid'] ?? 0));
            $this->module = empty($this->module) ? app('http')->getName() : $this->module;

            // 唯一作用就是验证异步回调时，如果有调用应用service 并且继承了 \xsframe\service\BaseService 这时候需要获取到uniacid
            $notifyUniacid = $this->notifyBackUniacid($uniacid);

            if (!empty($notifyUniacid)) {
                $uniacid = $notifyUniacid;
                if ($this->module != 'admin') {
                    $this->checkDisabledUniacid($uniacid);
                }
            } else {
                # 获取独立域名绑定的uniacid
                if (empty($uniacid) && $this->module != 'admin' && !empty($_SERVER['HTTP_HOST'])) {
                    $uniacid = $this->accountHostController->getAccountHostUniacid($_SERVER['HTTP_HOST']);
                } else {
                    if ($checkUrl && empty($this->params['uniacid']) && empty($this->params['i'])) {
                        $uniacid = $this->accountHostController->getAccountHostUniacid($_SERVER['HTTP_HOST']);
                    }
                }
            }

            if (!empty($uniacid)) {
                $uniacidList = SystemWrapperFacade::getUniacidList();
                if ($uniacidList && !in_array($uniacid, $uniacidList)) { // 1、判定是否存在uniacid，如果不存在就按照域名获取uniacid
                    if ($this->module != 'admin') {
                        $this->throwDisabledException();
                    }
                } else {
                    if ($this->module && $this->module != 'admin') { // 2、判定商户是否有应用权限
                        $systemModuleList = SystemWrapperFacade::getAllModuleList();
                        $accountModuleList = SystemWrapperFacade::getAccountModuleList($uniacid);
                        // 应该获取到真实的应用标识
                        $realModule = $this->realModuleName($this->module);
                        if (empty($accountModuleList) || empty($systemModuleList) || !in_array($realModule, $accountModuleList) || !in_array($realModule, $systemModuleList)) {
                            if ($this->request->isAjax()) {
                                throw new ApiException("当前商户暂无应用（{$this->module}）的访问权限，请联系管理员!", 403);
                            } else {
                                exit('权限不足');
                            }
                        }
                    }
                }

                if (!StringUtil::strexists($this->controller, "web.")) {
                    isetcookie('uniacid', $uniacid); // 缓存当前所选商户uniacid
                }
            } else {
                $uniacid = $this->websiteSets['uniacid'] ?? 0; // 默认uniacid
                if ($this->module != 'admin') {
                    $this->checkDisabledUniacid($uniacid);
                }
            }

            if ($this->module != 'admin' && empty($uniacid)) {
                if( !$this->disUniacid ){
                    exit("<p style='width:100%;height:80px;line-height:80px;text-align: center;font-size: 15px;'>商户不存在,请联系管理员配置默认商户</p>");
                }
            }

            $this->uniacid = intval($uniacid);
        }

        return $this->uniacid;
    }

    // 获取应用真实文件夹名称匹配权限
    private function realModuleName($moduleName)
    {
        $appMaps = config('app.app_map') ?? [];
        return $appMaps[$moduleName] ?? $moduleName;
    }

    // 校验uniacid是否被禁用或删除
    private function checkDisabledUniacid($uniacid)
    {
        // 判断是否被禁用或删除 start
        if ($uniacid) {
            $disabledUniacidList = SystemWrapperFacade::getDisabledUniacidList();
            if ($disabledUniacidList && in_array($uniacid, $disabledUniacidList)) {
                $this->throwDisabledException();
            }
        }
        // end
        return $uniacid;
    }

    // 抛出禁用异常
    private function throwDisabledException()
    {
        if( !$this->disUniacid ){
            if ($this->request->isAjax() || $this->request->isPost()) {
                throw new ApiException("抱歉！该站点{$uniacid}已暂停服务，请联系管理员了解详情!", 403);
            } else {
                exit('权限不足');
            }
        }
    }

    // 验证是否是异步回调
    private function notifyBackUniacid($uniacid)
    {
        // 判断是微信支付
        $isWechatPay = false;

        if (empty($this->params) && !empty($this->request->getContent())) {
            $get = ArrayUtil::xml2array($this->request->getContent());
            if (is_array($get) && !empty($get['attach']) && !empty($get['mch_id']) && !empty($get['transaction_id'])) {
                $isWechatPay = true;
                $this->params = $get;
            }
        }

        // 1.判断是支付宝回调
        $isAliPay = !empty($this->params['body']) && !empty($this->params['sign_type']) && !empty($this->params['app_id']) && !empty($this->params['out_trade_no']);
        if ($isWechatPay || $isAliPay) {
            $attachArr = $this->params['attach'] ?? $this->params['body'];
            $attachArr = explode(":", $attachArr);
            $uniacid = $attachArr[1] ?? 0;
        } else {
            // 2.判断是微信退款
            $isWxRefund = !empty($this->params['appid']) && !empty($this->params['mch_id']) && !empty($this->params['nonce_str']) && !empty($this->params['req_info']);
            if ($isWxRefund) {
                $uniacid = 1;
            } else {
                // 3.判断是收钱吧退款
                $isSqbPay = !empty($this->params['client_tsn']) && !empty($this->params['payer_login']) && !empty($this->params['payer_uid']) && !empty($this->params['trade_no']);
                if ($isSqbPay) {
                    $attachArr = $this->params['reflect'];
                    $attachArr = explode(":", $attachArr);
                    $uniacid = $attachArr[1] ?? 0;
                }
            }
        }

        return $uniacid;
    }

    // 校验插件路由
    protected function checkAppRouter(): string
    {
        $url = $this->url;

        $appControllerPath = $this->iaRoot . "/app/{$this->module}/controller";
        // $appControllerDirs = FileUtil::dirsOnes($appControllerPath);

        $urlPath = $appControllerPath . "/{$this->controller}/" . ucfirst($this->action) . ".php";

        # 校验控制器是否存在
        if (!is_file($urlPath)) {
            if (is_mobile()) {
                $this->controller = 'mobile';
            } else {
                $this->controller = 'web';
            }
        }

        if (count(explode("/", $url)) > 2) {
            $url = "{$this->controller}.{$this->action}";
        } else {
            $url = "{$this->module}/{$this->controller}.{$this->action}";
        }

        return $url;
    }

    // 自动执行客户端页面
    protected function runPc($filename = 'index', $version = null)
    {
        $this->baseRun('pc', $filename, $version);
    }

    // 自动执行客户端页面
    protected function runWeb($filename = 'index', $version = null)
    {
        $this->baseRun('pc', $filename, $version);
    }

    // 自动执行客户端页面
    protected function runMobile($filename = 'index', $version = null)
    {
        $this->baseRun('mobile', $filename, $version);
    }

    // 自动访问资源模版
    protected function baseRun($entry, $filename, $version = null)
    {
        $addonsName = $this->module;

        $template = "{$addonsName}/{$entry}/{$filename}.html";
        $source = IA_ROOT . "/public/app/" . $template;

        if (!StringUtil::strexists($filename, 'version')) {
            $versionPath = IA_ROOT . "/public/app/" . $addonsName . "/{$entry}/version";

            if (empty($version)) {
                $trees = FileUtil::dirsOnes($versionPath);
                $version = end($trees);
                if (!empty($version)) {
                    $template = "{$addonsName}/{$entry}/version/{$version}/{$filename}.html";
                }
            }

            $source = IA_ROOT . "/public/app/{$addonsName}/{$entry}/version/{$version}/{$filename}.html";
        }

        if (!is_file($source)) {
            exit("template source '{$template}' is not exist!");
        } else {
            echo "<script>let uniacid = `{$this->uniacid}`;</script>";
            echo "<script>let version = '1.0';</script>";
            echo "<script>let module = `{$this->module}`;</script>";
            echo "<script>let apiroot = `{$this->siteRoot}`;</script>";
            require_once $source;
        }
    }
}
```


**数据操作门面类**

```php
<?php

namespace xsframe\base;

use think\Facade;

/**
 * @method static name(string $tableName)
 * @method static getInfo(array $condition, string $field = '*', string $order = "")
 * @method static getList(array $condition, string $field = "*", string $order = "", int $pIndex = 0, int $pSize = 10)
 * @method static getAll(array $condition, string $field = "*", string $order = "", $keyField = '')
 * @method static getTotal(array $condition, string $field = "*")
 * @method static deleteInfo(array $condition)
 * @method static insertInfo(array $data)
 * @method static insertAll(array $data)
 * @method static updateInfo(array $updateData, array $condition)
 * @method static getValue(array $condition, string $field = "id", string $order = "")
 * @method static getFullCategory(array $condition, bool $fullName = false)
 */
abstract class BaseFacade extends Facade
{
}

```

**管理后台类公共复用方法，继承AdminBaseController所有后台类都默认可以使用或重写里边的方法**

```php

<?php

namespace xsframe\traits;

use think\facade\Db;
use xsframe\util\ExcelUtil;
use xsframe\util\StringUtil;

trait AdminTraits
{
    protected $tableName = ''; // 表名
    private $fieldList = []; // 当前表字段
    protected $condition = []; // 查询条件
    protected $orderBy = ""; // 列表排序
    protected $result = []; // 可以自定义返回多个值到前端页面
    protected $backUrl = null; // post提交后返回的url
    protected $backData = []; // post提交后返回的数据
    protected $isBackMain = true; // post提交后是否返回到列表页 默认返回到列表页
    protected $deleteField = "deleted"; // 软删除字段
    protected $template = null; // 设置模板名称
    protected $pageSize = 10; // 分页显示数量

    public function index()
    {
        return $this->main();
    }

    public function main()
    {
        if ($this->pSize != $this->pageSize) {
            $this->pSize = $this->pageSize;
        }
        if (!empty($this->tableName)) {
            $fieldList = $this->getFiledList();

            $keyword = trim($this->params['keyword']) ?? '';
            $kwFields = trim($this->params['kwFields']) ?? '';
            $field = trim($this->params['field']) ?? '';
            $status = trim($this->params['status']) ?? '';
            $enabled = trim($this->params['enabled']) ?? 0;
            $searchTime = trim($this->params["searchtime"]) ?? '';
            $sort = trim($this->params["sort"] ?? '');
            $order = trim($this->params["order"] ?? '');

            $export = trim($this->params['export']);
            $exportTitle = trim($this->params['export_title']);
            $exportColumns = trim($this->params['export_columns']);
            $exportKeys = trim($this->params['export_keys']);

            $startTime = strtotime("-1 month");
            $endTime = time();

            $condition = (array)$this->condition;
            $condition['uniacid'] = $this->uniacid;

            if (array_key_exists($this->deleteField, $fieldList)) {
                $condition[] = Db::raw($this->deleteField . " is null or " . $this->deleteField . " = '' or " . $this->deleteField . " = '0' ");
            }

            if (array_key_exists('is_deleted', $fieldList)) {
                $condition['is_deleted'] = 0;
            }

            if (is_numeric($status)) {
                $condition['status'] = $status;
            }

            if (is_numeric($enabled)) {
                $condition['enabled'] = $enabled;
            }

            if (is_array($this->params["time"])) {
                $startTime = strtotime($this->params["time"]["start"]);
                $endTime = strtotime($this->params["time"]["end"]);

                if (array_key_exists($searchTime . "time", $fieldList)) {
                    $condition[$searchTime . "time"] = Db::raw("between {$startTime} and {$endTime} ");
                } else {
                    if (array_key_exists($searchTime . "_time", $fieldList)) {
                        $condition[$searchTime . "_time"] = Db::raw("between {$startTime} and {$endTime} ");
                    }
                }
            }

            if (!empty($keyword) && !empty($kwFields)) {
                $field = $kwFields;
            }

            if (!empty($keyword) && !empty($field)) {
                $field = str_replace(" ", "|", $field);
                $field = str_replace("，", "|", $field);
                $field = str_replace(",", "|", $field);
                $condition[] = [$field, 'like', "%" . trim($keyword) . "%"];
            }

            foreach ($this->params as $field => $value) {
                if (array_key_exists($field, $fieldList) && (!empty($value) || is_numeric($value)) && !array_key_exists($field, $condition)) {
                    $condition[$field] = $value;
                }
            }
            unset($item);

            $this->setMainCondition($condition);

            $field = "*";

            if (empty($this->orderBy)) {
                if (array_key_exists('displayorder', $fieldList)) {
                    $this->orderBy = "displayorder desc, id desc";
                } else {
                    $this->orderBy = "id desc";
                }
            }

            if (!empty($sort) && !empty($order)) {
                $this->orderBy = "{$sort} {$order}";
            }

            $this->beforeMainResult();

            if ($export) {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->select()->toArray();
            } else {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->page($this->pIndex, $this->pSize)->select()->toArray();
            }

            // 导出支持简单导出列表功能，复杂导出可以自行实现 exportExcelData
            foreach ($list as &$item) {
                $item = $this->listItemFormat($item);
            }
            unset($item);

            if ($export) {
                $this->exportExcelData($list, $exportColumns, $exportKeys, $exportTitle);
            }

            $total = Db::name($this->tableName)->where($condition)->count();
            $pager = pagination2($total, $this->pIndex, $this->pSize);

            $this->result['list'] = $list;
            $this->result['pager'] = $pager;
            $this->result['total'] = $total;
            $this->result['starttime'] = $startTime;
            $this->result['endtime'] = $endTime;
        }

        $this->afterMainResult($this->result);

        return $this->template($this->template ?: 'list', $this->result);
    }

    // 设置列表页查询条件
    public function setMainCondition(&$condition)
    {
    }

    // 列表返回以前执行
    public function beforeMainResult()
    {
    }

    // 列表返回以后执行
    public function afterMainResult(&$result)
    {
    }

    // 列表页导出Excel
    public function exportExcelData($list = [], $column = null, $keys = null, $title = null, $last = null)
    {
        if (!empty($list)) {
            ini_set('memory_limit', '1024M'); // 根据需要调整内存大小
            set_time_limit(0); // 设置为0表示无限制，但注意服务器配置可能限制此设置

            $title = ($title ?? "数据列表") . "_" . date('YmdHi');
            if (!empty($column) && !empty($keys)) {
                $column = explode(",", $column);
                $keys = explode(",", $keys);
                $last = explode(",", $last);

                $setWidth = [];
                for ($i = 0; $i < count($column); $i++) {
                    $setWidth[$i] = 30;
                }

                ExcelUtil::export((string)$title, (array)$column, (array)$setWidth, (array)$list, (array)$keys, (array)$last, (string)$title);
            } else {
                $data = $this->setExportExcelData($list);
                if (!empty($data)) {
                    extract($data);

                    if (!empty($column) && !empty($keys)) {
                        ExcelUtil::export((string)$title, (array)$column, (array)$setWidth, (array)$list, (array)$keys, (array)$last, (string)$title);
                    }
                }
            }
        }
    }

    // 自定义导出数据格式
    public function setExportExcelData(&$list)
    {
        return [
            'list'     => $list,
            'column'   => [],
            'keys'     => [],
            'setWidth' => [],
            'last'     => [],
            'title'    => "",
        ];
    }

    // 编辑数据
    public function edit()
    {
        return $this->post();
    }

    // 添加数据
    public function add()
    {
        return $this->post();
    }

    // 更新数据
    public function post()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"] ?? 0);
            $backUrl = trim($this->params['backUrl'] ?? '');

            if ($this->request->isPost()) {
                $fieldList = $this->getFiledList();
                $updateData = [];
                foreach ($fieldList as $filed => $fieldItem) {

                    if (!in_array($filed, ['uniacid', 'createtime', 'create_time', 'updatetime', 'update_time']) && !array_key_exists($filed, $this->params)) {
                        continue;
                    }

                    $updateData[$filed] = $this->params[$filed] ?? '';
                    if (!is_array($updateData[$filed])) {
                        switch ($fieldItem['type']) {
                            case 'text':
                                $updateData[$filed] = htmlspecialchars_decode($updateData[$filed]);
                                break;
                            case 'datetime':
                                $updateData[$filed] = strtotime($updateData[$filed]);
                                break;
                            case 'decimal':
                                $updateData[$filed] = floatval($updateData[$filed]);
                                break;
                            default:
                                $updateData[$filed] = trim($updateData[$filed]);
                        }
                    }

                    if (empty($updateData[$filed])) {
                        switch ($filed) {
                            case 'uniacid':
                                $updateData[$filed] = $this->uniacid;
                                break;
                            case 'create_time':
                            case 'createtime':
                                if (empty($id)) {
                                    $updateData[$filed] = TIMESTAMP;
                                } else {
                                    unset($updateData[$filed]);
                                }
                                break;
                            case 'update_time':
                            case 'updatetime':
                                $updateData[$filed] = TIMESTAMP;
                                break;
                            case $this->deleteField:
                                $type = explode('(', $fieldItem['type'])[0];
                                if ($type == 'tinyint') {
                                    $updateData[$filed] = 0;
                                } else {
                                    $updateData[$filed] = TIMESTAMP;
                                }
                                break;
                        }
                    }
                }

                $this->beforeSetPostData($updateData);

                if (!empty($id)) {
                    Db::name($this->tableName)->where(['id' => $id])->update($updateData);
                } else {
                    $id = Db::name($this->tableName)->insertGetId($updateData);
                }

                $this->afterSetPostData($id);

                if ($this->params['isModel']) {
                    $this->success();
                } else {
                    if (empty($backUrl)) {
                        if (!empty($this->backUrl)) {
                            $backUrl = $this->backUrl;
                        } else {
                            if ($this->isBackMain) {
                                $backUrl = $this->request->controller() . "/main";
                            }
                        }
                        if (!empty($backUrl) && !StringUtil::strexists($backUrl, "http") && !StringUtil::strexists($backUrl, "web.")) {
                            $backUrl = "web." . $backUrl;
                        }
                    }

                    if (!empty($backUrl)) {
                        if (!StringUtil::strexists($backUrl, "http")) {
                            $backUrl = webUrl(rtrim($backUrl, ".html"));
                        }
                    } else {
                        $backUrl = referer();
                        $params = ['id' => $id, 'tab' => str_replace("#tab_", "", $this->params['tab'])];

                        $parsedUrl = parse_url($backUrl);
                        $query = $parsedUrl['query'];
                        parse_str($query, $queryParams);
                        $queryParams = array_merge($queryParams, $params);

                        $uniqueQueryParams = [];
                        foreach ($queryParams as $key => $value) {
                            $uniqueQueryParams[$key] = $value;
                        }
                        $newQuery = http_build_query($uniqueQueryParams);

                        $backUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '?' . $newQuery;
                    }
                    $this->backData['url'] = $backUrl;
                    $this->success($this->backData);
                }
            }

            $field = "*";
            $condition = ['id' => $id];
            $item = Db::name($this->tableName)->field($field)->where($condition)->find();

            $this->result['item'] = $item;
        }

        $this->afterPostResult($this->result);
        return $this->template($this->template ?: 'post', $this->result);
    }

    // 保存数据之前处理
    public function beforeSetPostData(&$updateData)
    {
    }

    // 保存数据之后处理
    public function afterSetPostData($id)
    {
    }

    // 渲染视图页参数
    public function afterPostResult(&$result)
    {
    }

    // 改变字段数据
    public function change()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $type = trim($this->params["type"]);
            $value = trim($this->params["value"]);

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                $this->beforeChangeData($item);
                Db::name($this->tableName)->where("id", '=', $item['id'])->update([$type => $value]);
                $this->afterChangeData($item);
            }
        }

        $this->success();
    }

    // 修改数据之前处理
    public function beforeChangeData(&$item)
    {
    }

    // 修改数据之后处理
    public function afterChangeData(&$item)
    {
    }

    // 删除数据
    public function delete()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $updateData = [];
            $fieldList = $this->getFiledList();
            if (array_key_exists($this->deleteField, $fieldList)) {
                $updateData[$this->deleteField] = 1;

                $type = explode('(', $fieldList[$this->deleteField]['type'])[0];
                if ($type == 'tinyint' || $type == 'int') {
                    $updateData[$this->deleteField] = 1;
                } else {
                    $updateData[$this->deleteField] = TIMESTAMP;
                }
            }
            if (array_key_exists('delete_time', $fieldList)) {
                $updateData['delete_time'] = TIMESTAMP;
            }
            if (array_key_exists('is_deleted', $fieldList)) {
                $updateData['is_deleted'] = 1;
            }

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                if (!empty($item['is_default'])) {
                    $this->error("默认项不能被删除");
                }

                $this->beforeDeleteData($item);

                Db::name($this->tableName)->where(['uniacid' => $this->uniacid, "id" => $item['id']])->update($updateData);

                $this->afterDeleteData($item);
            }
        }
        $this->success(["url" => referer()]);
    }

    // 删除数据之前处理
    public function beforeDeleteData(&$item)
    {
    }

    // 删除数据之后处理
    public function afterDeleteData(&$item)
    {
    }

    // 更新数据
    public function update()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);
            $updateData = $this->params["data"] ?? [];

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            if (!empty($updateData)) {
                $items = Db::name($this->tableName)->where(['id' => $id])->select();
                foreach ($items as $item) {
                    Db::name($this->tableName)->where(['uniacid' => $this->uniacid, "id" => $item['id']])->update($updateData);
                }
            }
        }
        $this->success(["url" => referer()]);
    }

    // 真实删除
    public function destroy()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $items = Db::name($this->tableName)->where(['uniacid' => $this->uniacid, 'id' => $id])->select();
            foreach ($items as $item) {
                if (!empty($item['is_default'])) {
                    $this->error("默认项不能被删除");
                }
                Db::name($this->tableName)->where(["id" => $item['id']])->delete();
            }
        }
        $this->success(["url" => referer()]);
    }

    // 还原数据
    public function restore()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $updateData = [];
            $fieldList = $this->getFiledList();
            if (array_key_exists($this->deleteField, $fieldList)) {
                $updateData[$this->deleteField] = 0;
            }
            if (array_key_exists('is_deleted', $fieldList)) {
                $updateData['is_deleted'] = 0;
            }

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                Db::name($this->tableName)->where(["id" => $item['id']])->update($updateData);
            }
        }
        $this->success(["url" => referer()]);
    }

    // 回收站
    public function recycle()
    {
        if (!empty($this->tableName)) {
            $condition = [
                'uniacid'          => $this->uniacid,
                $this->deleteField => 1,
            ];

            $field = "*";
            $order = "id desc";
            $list = Db::name($this->tableName)->field($field)->where($condition)->order($order)->page($this->pIndex, $this->pSize)->select()->toArray();
            $total = Db::name($this->tableName)->where($condition)->count();
            $pager = pagination2($total, $this->pIndex, $this->pSize);

            $this->result['list'] = $list;
            $this->result['pager'] = $pager;
            $this->result['total'] = $total;
        }

        return $this->template('recycle', $this->result);
    }

    // 访问入口
    public function cover()
    {
        $moduleName = realModuleName($this->module);
        $coverUrl = $this->siteRoot . "/{$moduleName}.html?i=" . $this->uniacid;
        $mobileUrl = $this->siteRoot . "/{$moduleName}/mobile.html?i=" . $this->uniacid;
        $pcUrl = $this->siteRoot . "/{$moduleName}/pc.html?i=" . $this->uniacid;
        return $this->template('cover', ['coverUrl' => $coverUrl, 'mobileUrl' => $mobileUrl, 'pcUrl' => $pcUrl]);
    }

    // 设置项目应用配置信息
    public function moduleSettings()
    {
        $moduleSettings = $this->settingsController->getModuleSettings(null, $this->module, $this->uniacid);
        if ($this->request->isPost()) {
            $settingsData = $this->params['data'] ?? [];

            if (!empty($settingsData['contact'])) {
                $settingsData['contact']['about'] = htmlspecialchars_decode($settingsData['contact']['about']);
            }
            if (!empty($settingsData['user'])) {
                $settingsData['user']['agreement'] = htmlspecialchars_decode($settingsData['user']['agreement']);
            }

            $settingsData = array_merge($moduleSettings, $settingsData);

            if (!empty($settingsData)) {
                $data['settings'] = serialize($settingsData);
                Db::name('sys_account_modules')->where(["uniacid" => $this->uniacid, 'module' => $this->module])->update($data);
                # 更新缓存
                $this->settingsController->reloadModuleSettings($this->module, $this->uniacid);
            }

            $moduleSettings = $settingsData;
        }
        return $moduleSettings;
    }

    // 当前项目应用配置信息
    public function module()
    {
        $moduleSettings = $this->moduleSettings();
        if ($this->request->isPost()) {
            $this->success(["url" => webUrl("sets/module", ['tab' => str_replace("#tab_", "", $this->params['tab'])])]);
        }

        $var = [
            'moduleSettings' => $moduleSettings
        ];
        return $this->template('module', $var);
    }

    // 获取字段列表
    public function getFiledList($tableName = null): array
    {
        if (!empty($tableName)) {
            $this->fieldList = Db::name($tableName)->getFields();
        } else {
            if (!empty($this->tableName) && empty($this->fieldList)) {
                $this->fieldList = Db::name($this->tableName)->getFields();
            } else {
                $this->fieldList = [];
            }
        }
        return $this->fieldList;
    }

    // 自动格式化列表数据
    public function listItemFormat($item)
    {
        return $item;
    }
}



**应用公共函数库,所有继承BaseController类的子类都能直接调用的方法**


```php

<?php

use think\facade\Config;
use think\response\View;
use xsframe\facade\wrapper\SystemWrapperFacade;
use xsframe\util\OpensslUtil;
use xsframe\util\StringUtil;
use xsframe\wrapper\SettingsWrapper;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\facade\wrapper\PermFacade;
use think\facade\Env;


define('IA_ROOT', str_replace("\\", '/', dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))));
define('APP_PATH', IA_ROOT . "/app");
define('TIMESTAMP', time());

// 获取完整表
if (!function_exists('tablename')) {
    function tablename($table, $separator = true)
    {
        $tablepre = Env::get('database.prefix');
        $tablename = "{$tablepre}{$table}";
        if ($separator) {
            $tablename = " `{$tablepre}{$table}` ";
        }
        return $tablename;
    }
}

// 返回分页数据
if (!function_exists('getPageNum')) {
    function getPageNum($total, $pageSize = 10)
    {
        $pageNum = 1;
        if ($pageSize < $total) {
            $pageNum = intval($total / $pageSize) + (($total % $pageSize) > 0 ? 1 : 0);
        }

        return $pageNum;
    }
}

// 补全图片路径
if (!function_exists('tomedia')) {
    function tomedia($src, $suffix = null, $uniacid = null)
    {
        if (empty($src)) {
            return '';
        }
        $t = strtolower($src);
        if ((substr($t, 0, 7) == 'http://') || (substr($t, 0, 8) == 'https://') || (substr($t, 0, 2) == '//')) {
            return $src;
        }

        if (substr($src, 0, 4) === "app/") {
            $hostUrl = getAttachmentUrl(false, $uniacid);
        } else {
            $hostUrl = getAttachmentUrl(true, $uniacid);
        }

        return $hostUrl . "/" . ltrim($src, '/') . ($suffix ?: '');
    }
}

// 设置图片完整路径
if (!function_exists('set_medias')) {
    function set_medias($list = [], $fields = null, $suffix = null, $uniacid = null)
    {
        if (empty($list)) {
            return [];
        }

        if (empty($fields)) {
            foreach ($list as &$row) {
                $row = tomedia($row, $suffix, $uniacid);
            }

            return $list;
        }

        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        if (is_object($list)) {
            $list = $list->toArray();
        }

        if (is_array2($list)) {
            foreach ($list as $key => &$value) {
                foreach ($fields as $field) {
                    if (strexists($field, ".")) {
                        $str = explode(".", $field);
                        if (isset($value[$str[0]][$str[1]])) {
                            $value[$str[0]][$str[1]] = tomedia($value[$str[0]][$str[1]], $suffix, $uniacid);
                        }
                    }

                    if (isset($list[$field])) {
                        $list[$field] = tomedia($list[$field], $suffix, $uniacid);
                    }

                    if (is_array($value) && isset($value[$field])) {
                        $value[$field] = tomedia($value[$field], $suffix, $uniacid);
                    }
                }
            }

            return $list;
        }

        foreach ($fields as $field) {
            if (isset($list[$field])) {
                $list[$field] = tomedia($list[$field], $suffix, $uniacid);
            }
        }

        return $list;
    }
}

// 是否二维数组
if (!function_exists('is_array2')) {
    function is_array2($array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                return is_array($v);
            }
            return false;
        }
        return false;
    }
}

if (!function_exists('iserializer')) {
    function iserializer($value)
    {
        return serialize($value);
    }
}


[前端JS引入与使用开发规范]

#### 前端JS组件集成规范

**模板结构**
```html
{extend name="../../admin/view/public/admin"}

{block name="title"}{/block}

{block name='style'}
<!-- 页面专属CSS -->
{/block}

{block name='content'}
<!-- 页面内容区域 -->
{/block}

{block name='script'}
<!-- 页面专属JS逻辑 -->
{/block}
```

```js

```

#### 已集成的完整组件库清单

```
核心框架类
jQuery ($) - 1.11.1版本
Bootstrap - 完整UI框架
Vue - 渐进式JavaScript框架


可视化
echarts - 百度图表库（完整版和min版）
jquery.qrcode - 二维码生成
viewer - 图片查看器
jquery.img.enlarge - 图片放大镜
swiper - 轮播图组件
```

### 基于JS组件库，实现[功能描述]。要求：

- 包含完整的错误处理
- 使用统一的消息提示
- 确保页面加载性能
- js中使用后台变量 "{$item['id']}" 需使用双引号包裹


### 使用规范及要求

**1. jquery,Bootstrap,tip 请使用require(['jquery','bootstrap', 'tip'], function($) {}) 这种方式引入**
```js
{block name='script'}
    // 页面逻辑
    $(function() {
        // DOM就绪后执行
        tip.msgbox.suc("DOM就绪");
    });
{/block}
```


**2. vue2使用规范及要求 需要使用require(['vue', 'h7.axios'], (Vue, axios) => {});引入**
```js
{block name="script"}
<script>
    require(['vue', 'h7.axios'], (Vue, axios) => {

        new Vue({
            el: '#app-root',
            data: {
                appList: []
            },
            mounted() {
                this.getAppList();
            },
            methods: {
                getAppList() {
                    let url = "{:webUrl('app/getRecommendAppList')}";
                    axios.post(url, {}).then((res) => {
                        this.appList = res.appList || [];
                    });
                },
            }
        });

    });
</script>
{/block}

```

## JS调用示例


**1、系统消息提示 **
```js
// 1.成功/错误提示框
tip.msgbox.suc('操作成功', '跳转URL可选');
tip.msgbox.err('操作失败', '跳转URL可选');

// 2. 自动消失的成功/错误提示
// 用途：显示自动消失的成功/错误提示（顶部弹出）
// 调用方法：
tip.success('操作成功');
tip.error('操作失败', 3000); // 可选延迟时间

// 3. 确认对话框
// 用途：需要用户确认的操作（确定/取消）
// 调用方法：
tip.confirm('确定要删除吗？', function() {
    // 确定回调
}, function() {
    // 取消回调
});

// 4. 警告对话框
// 用途：仅显示信息的警告框
// 调用方法：
tip.alert('这是一个提示', function() {
    // 关闭回调
});

// 5. 输入对话框
// 用途：需要用户输入内容的对话框
// 调用方法：

tip.prompt('请输入姓名', function(value) {
    console.log('用户输入：', value);
});

// 带选项
tip.prompt('请输入内容', {
    maxlength: 20,
    required: true,
    callback: function(value) {
        console.log('输入内容：', value);
    }
});

// 密码输入
tip.prompt('请输入密码', function(value) {}, true);

// 6. 模态对话框
// 用途：显示自定义HTML内容的模态框
// 调用方法：
tip.dialog({
    title: '标题',
    content: '<p>HTML内容</p>',
    width: 500,
    buttons: [
        {
            text: '确定',
            class: 'btn btn-primary',
            handler: function($modal) {
                // 处理逻辑
                $modal.remove();
            }
        },
        {
            text: '取消',
            class: 'btn btn-default',
            handler: function($modal) {
                $modal.remove();
            }
        }
    ]
});

// 7. 权限确认对话框
// 用途：特殊样式的确认对话框
// 调用方法：
tip.impower('权限说明', function() {
    // 重新上传回调
}, function() {
    // 审核完成回调
});

// 8. 模态对话框 (tip.dialog)
// 用途：使用 Bootstrap 模态框显示自定义HTML内容
// 特点：
// 完全使用 Bootstrap 的 modal 方法
// 支持 Bootstrap 的所有模态框事件
// 自动管理模态框的生命周期
// 支持响应式设计
// 调用方法：
tip.dialog({
    title: '标题',
    content: 'HTML内容',
    width: 500,
    buttons: [
        {
            text: '确定',
            class: 'btn btn-primary',
            handler: function ($modal) {
                // 点击确定按钮的处理逻辑
                $modal.modal('hide');
            }
        },
        {
            text: '取消',
            class: 'btn btn-default',
            handler: function ($modal) {
                $modal.modal('hide');
            }
        }
    ],
    onShow: function () {
        // 模态框显示前触发
    },
    onShown: function () {
        // 模态框完全显示后触发
    },
    onHide: function () {
        // 模态框隐藏前触发
    },
    onHidden: function () {
        // 模态框完全隐藏后触发
    },
    onClose: function () {
        // 模态框关闭后的回调（兼容旧代码）
    }
});

```
**2、加载中提示**

```js
显示加载:openLoading(1500)
关闭加载:closeLoading()
```

**3、事件绑定**
```js
$(document).on('click', '.ajax-btn', function() {
    tip.msgbox.suc('点击成功');
});
```

**4、ajax 请求 **
```js

// ajax get请求
$(document).on('click', '.ajax-btn', function() {
    openLoading();
    $.get("{:webUrl('license/verifyLicense')}", {
        id: "{$item['id']}"
    }, function(res) {
        closeLoading();
        let data = res.result // 返回值在result变量中
        if (res.status == 1) {
            tip.msgbox.err('许可证验证成功');
        } else {
            tip.msgbox.err('许可证验证失败');
        }
    },'json').error((err) => {
        console.log('err:',err)
    });
});

// ajax post请求
$(document).on('click', '.ajax-btn', function() {
    openLoading();
    $.post("{:webUrl('license/verifyLicense')}", {
        id: "{$item['id']}"
    }, function(res) {
        closeLoading();
        let data = res.result // 返回值在result变量中
        if (res.status == 1) {
            tip.msgbox.err('许可证验证成功');
        } else {
            tip.msgbox.err('许可证验证失败');
        }
    },'json').error((err) => {
        console.log('err:',err)
    });
});

// ajax getJSON请求
$(document).on('click', '.ajax-btn', function() {
    openLoading();
    $.getJSON("{:webUrl('license/verifyLicense')}", function (res) {
        closeLoading();
        if (res.status == 1) {
            tip.msgbox.err('许可证验证成功');
        } else {
            tip.msgbox.err('许可证验证失败');
        }
    })
});
```
[数据表查询要求与规范]
1.遵循BaseFacade门面查询方式，复杂查询遵循thinkphp6语法规范
2.数据表查询规范(table_name 不需要填写ims_前缀):
    1).查询单条数据:DbServiceFacade::name("table_name")->getInfo(['uniacid' => $this->uniacid, 'deleted' => 0], "*");
    2).分页查询:DbServiceFacade::name("table_name")->getList(['uniacid' => $this->uniacid, 'deleted' => 0], "*");
    3).查询全部:DbServiceFacade::name("table_name")->getAll(['uniacid' => $this->uniacid, 'deleted' => 0], "*");
    4).更新数据:DbServiceFacade::name("table_name")->updateInfo(['name' => '张三','age' => 28], ['id' => 1]);
    5).删除数据:DbServiceFacade::name("table_name")->deleteInfo(['id' => 1]);
    6).增加数据:$id = DbServiceFacade::name("table_name")->insertInfo(['name' => '张三','age' => 28]);
    7).获取单个字段:$name = DbServiceFacade::name("table_name")->getValue(['id' => 1], "name");
    8).获取统计数据:$total = DbServiceFacade::name("table_name")->getTotal(['uniacid' => 1]);
[要求与学习]
你的任务是：
- 根据用户需求生成完整、可运行的PHP代码
- 代码必须安全，避免使用危险函数（如eval、exec、os.system等）
- 添加适当的注释和错误处理
- 只返回代码，不要包含额外的解释
- 如果用户需求不明确，询问澄清问题
- 生成的代码应遵循PHP7.4规范

安全要求：
- 禁止执行系统命令
- 禁止访问敏感文件
- 禁止网络操作（除非明确要求）
- 禁止创建无限循环
- 确保代码在thinkphp6环境运行

格式要求：
- 生成完整的PHP代码
- 代码可以直接复用使用
- 包含必要的导入
- 包含错误处理
- 添加适当的注释
- 确保代码安全
- 必须基于框架应用结构
- 必须基于提供的代码结构与写法
- 前端必须使用thinkphp的think模板语法，不要使用Smarty语法
- 管理后台控制器继承了AdminTraits中的所有方法，复杂逻辑必须结合这些方法补充实现
- 简单数据增删改查必须使用DbServiceFacade方式，复杂逻辑无法实现的组合查询请必须使用thinkphp6语法
- 后端代码遵循bootstrap开发规范
- 页面需要美观大气整洁，考虑用户的操作使用习惯，体验必须好性能稳定
- 后端如果使用到其他js尽可能考虑使用require方式引入第三方的js文件
- 管理后台控制器不需要写route路由，例如:app/{应用名称}/route/web.php(不需要)
- 管理后台PHP代码路径遵循 namespace app\{应用名称}\controller\web;
- 管理后台前端代码路径遵循 app\{应用名称}\view\web;
- 后台管理PHP类遵循继承AdminBaseController，AdminBaseController类遵循继承BaseController类
- 列表查询样式尽可能参考“查询显示列表页面开发规范范例”中查询部分，显示在一行即可，特别多查询条件的可以多行
- AdminTraits类已经存在的空方法不要写在后台控制器中，main,post,add,edit,change,delete等这些方法已经存在，后台控制器逻辑只需要在setMainCondition，beforeMainResult，afterMainResult，exportExcelData，setExportExcelData，beforeSetPostData，afterSetPostData，afterPostResult，beforeChangeData，afterChangeData，beforeDeleteData，afterDeleteData中实现逻辑即可
- 复杂视图中表关联查询请重写main方法，如果是单表查询请使用AdminTraits中main方法即可