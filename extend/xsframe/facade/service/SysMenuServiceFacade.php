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

namespace xsframe\facade\service;


use xsframe\base\BaseFacade;
use xsframe\service\SysMenuService;

/**
 * @method static addMenuNoticePointRoute(string $type = 'one', string $route = '', string $module = null)
 * @method static clearMenuNoticePointRoute(string $type = 'one', string $route = '', string $module = null)
 */
class SysMenuServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return SysMenuService::class;
    }
}