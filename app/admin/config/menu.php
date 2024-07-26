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
    ],
    'app'     => [
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-th-large',
    ],
    'users'   => [
        'title'    => '用户',
        'subtitle' => '用户管理',
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
                'title' => '站点设置',
                'route' => 'site',
            ],
            [
                'title' => '域名设置',
                'route' => 'host',
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

