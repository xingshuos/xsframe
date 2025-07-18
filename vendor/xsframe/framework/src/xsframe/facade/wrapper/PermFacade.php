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

namespace xsframe\facade\wrapper;


use xsframe\wrapper\PermWrapper;
use think\Facade;

/**
 * @method static formatPerms($uniacid, $moduleName = null)
 * @method static checkPerm(string $permUrl, int $permType)
 */
class PermFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return PermWrapper::class;
    }
}