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


use xsframe\service\ZiShuService;
use think\Facade;

/**
 * @method static doImage(array $params)
 * @method static getUserId()
 * @method static getUserInfo()
 */
class ZiShuServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return ZiShuService::class;
    }
}