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
    'app'     => [
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-th-large',
        'items'    => [
            [
                'title' => '未安装应用',
                'route' => '',
            ],
            [
                'title' => '已安装应用',
                'route' => '',
            ],
            [
                'title' => '已停用的应用',
                'route' => '',
            ],
            [
                'title' => '回收站',
                'route' => '',
            ],
            [
                'title' => '推荐的应用',
                'route' => '',
            ],
        ]
    ],
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
    'ops'     => [
        'title'    => '运维',
        'subtitle' => '运维管理',
        'icon'     => 'icon-user',
        'items'    => [
            [
                'title' => '系统运行情况',
                'route' => '',
            ],
            [
                'title' => '缓存处理',
                'route' => '',
            ],
            [
                'title' => '操作日志',
                'route' => '',
            ],
            [
                'title' => '检查BOM',
                'route' => '',
            ],
            [
                'title' => '数据库优化',
                'route' => '',
            ],
        ]
    ],
    'college' => [
        'title'    => '学院',
        'subtitle' => '星数学院',
        'icon'     => 'icon-user',
        'items'    => [
            [
                'title' => '发布文章',
                'route' => '',
            ],
            [
                'title' => '系统图标',
                'route' => '',
            ],
        ]
    ],

];

