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
    'frames' => array(
        'title'    => '框架',
        'subtitle' => '框架管理',
        'icon'     => 'icon-shopping-cart',
        'items'    => array(
            array(
                'title' => '版本列表',
                'route' => 'main',
                'route_in' => true,
            ),
        )
    ),

    'article' => array(
        'title'    => '应用',
        'subtitle' => '应用管理',
        'icon'     => 'icon-file-text-alt',
        'items'    => array(
            array(
                'title' => '应用列表',
                'route' => 'main',
            ),
        )
    ),

    'member' => array(
        'title'    => '用户',
        'subtitle' => '用户管理',
        'icon'     => 'icon-group',
        'items'    => array(
            array(
                'title' => '用户列表',
                'route' => 'main',
            ),
        )
    ),

];
return $menu;