<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

return [
    'account' => array(
        'title'    => '项目',
        'subtitle' => '项目管理',
        'icon'     => 'icon-th-large',
    ),
    'app'     => array(
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-puzzle-piece',
    ),
    'users'   => array(
        'title'    => '用户',
        'subtitle' => '用户管理',
        'icon'     => 'icon-user',
        'items'    => array(
            array(
                'title' => '账号设置',
                'route' => 'profile',
            ),
            array(
                'title' => '用户管理',
                'route' => 'list',
            ),
        )
    ),
    'sysset'  => array(
        'title'    => '系统',
        'subtitle' => '系统设置',
        'icon'     => 'icon-desktop',
        'items'    => array(
            array(
                'title' => '站点设置',
                'route' => 'site',
            ),
            array(
                'title' => '域名设置',
                'route' => 'host',
            ),
            array(
                'title' => '附件设置',
                'route' => 'attachment',
            ),
            array(
                'title' => '系统升级',
                'route' => 'upgrade',
            ),
            array(
                'title' => '常用工具',
                'route' => '',
                'items' => array(
                    array(
                        'title' => '系统表单',
                        'route' => 'form',
                    ),
                    array(
                        'title' => '系统图标',
                        'route' => 'icon',
                    ),
                    array(
                        'title' => '静态页面',
                        'route' => 'static',
                    ),
                    array(
                        'title' => '检测BOM',
                        'route' => 'bom',
                    ),
                )
            ),
        )
    ),
];

