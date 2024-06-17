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

use think\facade;
use xsframe\wrapper\SystemWrapper;

/**
 * @method static getAccountModuleList(int|mixed $uniacid, bool $reload = false)
 * @method static getAllModuleList(bool $reload = false)
 * @method static getUniacidList(bool $reload = false)
 * @method static getDisabledUniacidList(bool $reload = false)
 * @method static reloadUniacidList()
 * @method static reloadDisabledUniacidList()
 * @method static reloadAccountModuleList(mixed $uniacid)
 * @method static reloadAllModuleList()
 */
class SystemWrapperFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return SystemWrapper::class;
    }
}