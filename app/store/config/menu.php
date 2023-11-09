<?php
// +----------------------------------------------------------------------
// | 星数 [ xsframe赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2023 https://www.xsframe.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 星数PHP并不是自由软件，未经许可不能去掉xsframe相关版权
// +----------------------------------------------------------------------
// | Author: GuiHai <786824455@qq.com>
// +----------------------------------------------------------------------

$menu = [
    'web.goods' => array(
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-shopping-cart',
        'items'    => array(
            array(
                'title' => '上架中',
                'route' => 'index/main',
            ),
            array(
                'title' => '回收站',
                'route' => 'index/cycle',
            ),
            array(
                'title' => '应用分类',
                'route' => 'category/main',
            ),
            array(
                'title' => '应用标签',
                'route' => 'label/main',
            ),
        )
    ),

    'web.order' => array(
        'title'    => '订单',
        'subtitle' => '订单管理',
        'icon'     => 'icon-reorder',
        'items'    => array(
            array(
                'title' => '订单概述',
                'route' => 'index/index',
            ),

            array(
                'title' => '应用',
                'route' => 'index',
                'items' => array(
                    array(
                        'title' => '待付款',
                        'route' => 'status0',
                    ),
                    array(
                        'title' => '已付款',
                        'route' => 'status1',
                    ),
                    array(
                        'title' => '待结算',
                        'route' => 'status2',
                    ),
                    array(
                        'title' => '已结算',
                        'route' => 'status3',
                    ),
                    array(
                        'title' => '已关闭',
                        'route' => 'status_1',
                    ),
                    array(
                        'title' => '全部订单',
                        'route' => 'main',
                    ),
                )
            ),

            array(
                'title' => '维权',
                'route' => 'index',
                'items' => array(
                    array(
                        'title' => '维权申请',
                        'route' => 'status4'
                    ),
                    array(
                        'title' => '维权完成',
                        'route' => 'status5'
                    )
                )
            ),

        )
    ),

    'web.article' => array(
        'title'    => '文章',
        'subtitle' => '文章管理',
        'icon'     => 'icon-file-text-alt',
        'items'    => array(
            array(
                'title' => '列表',
                'route' => 'index/main',
            ),
            array(
                'title' => '分类',
                'route' => 'category/main',
            ),
        )
    ),

    'web.member' => array(
        'title'    => '用户',
        'subtitle' => '用户管理',
        'icon'     => 'icon-group',
        'items'    => array(
            array(
                'title' => '用户管理',
                'route' => 'main',
            ),
        )
    ),

    'web.sets' => array(
        'title'    => '设置',
        'subtitle' => '设置管理',
        'icon'     => 'icon-cog',
        'items'    => array(
            array(
                'title' => '访问入口',
                'route' => 'cover',
            ),
            array(
                'title'    => '当前账号',
                'route'    => 'profile',
                'route_in' => true,
            ),
            array(
                'title' => '应用设置',
                'route' => 'module',
            ),
        )
    ),
];
return $menu;