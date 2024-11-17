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
            ]
        ]
    ],
    'app'     => [
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-th-large',
        'items'    => [
            [
                'title' => '未安装应用',
                'route' => 'not_installed',
            ],
            [
                'title' => '已安装应用',
                'route' => 'installed',
            ],
            [
                'title' => '已停用的应用',
                'route' => 'recycle',
            ],
            [
                'title' => '回收站',
                'route' => 'delete',
            ],
            [
                'title' => '推荐的应用',
                'route' => 'recommend',
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
        'title'    => '用户',
        'subtitle' => '管理员管理',
        'icon'     => 'icon-user-secret',
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
        'icon'     => 'icon-gears',
        'items'    => [
            [
                'title' => '运行情况',
                'route' => 'overview',
            ],
            [
                'title' => '缓存处理',
                'route' => 'optimize',
            ],
            [
                'title' => '操作日志',
                'route' => 'oplog',
            ],
            [
                'title' => '检查BOM',
                'route' => 'bom',
            ],
            [
                'title' => '数据库优化',
                'route' => 'database',
            ],
        ]
    ],
    'college' => [
        'title'    => '学院',
        'subtitle' => '星数学院',
        'icon'     => 'icon-book',
        'items'    => [
            [
                'title' => '文章管理',
                'route' => 'article',
            ],
            [
                'title' => '系统图标',
                'route' => 'icon',
            ],
        ]
    ],

];

