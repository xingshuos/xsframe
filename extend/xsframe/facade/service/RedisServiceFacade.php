<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\facade\service;

use xsframe\service\RedisService;
use think\Facade;

/**
 * @method static set(string $key, $value, $uniacid, $expire = null)
 * @method static get(string $key, $uniacid)
 * @method static getArray(string $key, $uniacid)
 * @method static getString(string $key, $uniacid)
 * @method static del(string $key, $uniacid)
 */
class RedisServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return RedisService::class;
    }
}