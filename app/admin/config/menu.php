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
            // [
            //     'title' => '系统授权',
            //     'route' => 'auth',
            // ]
        ]
    ],
    'app'     => [
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-th-large',
        'items'    => [
            [
                'title' => '已安装应用',
                'route' => 'installed',
            ],
            [
                'title' => '未安装应用',
                'route' => 'not_installed',
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
                'title' => '用户列表',
                'route' => 'list',
            ],
            [
                'title' => '添加用户',
                'route' => 'add',
            ],
            [
                'title' => '当前账号',
                'route' => 'profile',
            ],
            [
                'title' => '登录日志',
                'route' => 'login_log',
            ],
            [
                'title' => '授权码',
                'route' => 'auth',
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
                'title' => '性能优化',
                'route' => 'optimize',
            ],
            [
                'title' => '检查BOM',
                'route' => 'bom',
            ],
            [
                'title' => '数据维护',
                'route' => 'database',
            ],
            [
                'title' => '更新缓存',
                'route' => 'cache',
            ],
        ]
    ],
    'college' => [
        'title'    => '学院',
        'subtitle' => '星数学院',
        'icon'     => 'icon-book',
        'items'    => [
            [
                'title' => '文档教程',
                'route' => 'document',
            ],
            [
                'title' => '应用开发',
                'route' => 'develop',
            ],
            [
                'title' => '使用反馈',
                'route' => 'feedback',
            ],
            [
                'title' => '优化建议',
                'route' => 'optimize',
            ],
            [
                'title' => '开源交流',
                'route' => 'exchange',
            ],
            [
                'title' => '创客学堂',
                'route' => 'affiliate',
            ],
            [
                'title' => '应用玩法',
                'route' => 'guide',
            ],
            [
                'title' => '我的文章',
                'route' => 'mine',
            ],
            [
                'title' => '系统图标',
                'route' => 'icon',
            ],
        ]
    ],

];

