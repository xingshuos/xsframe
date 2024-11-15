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
    'account' => [
        'title'    => '商户',
        'subtitle' => '商户管理',
        'icon'     => 'icon-archive',
        'items'    => [
            [
                'title' => '商户管理',
                'route' => 'list',
            ],
            [
                'title' => '独立域名',
                'route' => 'host',
            ],
        ]
    ],
    'app'     => [
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-th-large',
    ],
    'users'   => [
        'title'    => '管理员',
        'subtitle' => '管理员管理',
        'icon'     => 'icon-user',
        'items'    => [
            [
                'title' => '账号设置',
                'route' => 'profile',
            ],
            [
                'title' => '用户管理',
                'route' => 'list',
            ],
        ]
    ],
    'sysset'  => [
        'title'    => '系统',
        'subtitle' => '系统设置',
        'icon'     => 'icon-desktop',
        'items'    => [
            [
                'title' => '基础配置',
                'route' => 'site',
            ],
            [
                'title' => '通信设置',
                'route' => 'communication',
            ],
            [
                'title' => '附件设置',
                'route' => 'attachment',
            ],
            [
                'title' => '系统升级',
                'route' => 'upgrade',
            ],
            [
                'title' => '常用工具',
                'items' => [
                    [
                        'title' => '系统图标',
                        'route' => '/icon',
                    ],
                    [
                        'title' => '检测BOM',
                        'route' => '/bom',
                    ],
                ]
            ],
        ]
    ],
];

