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

use think\Facade;
use xsframe\service\AreaService;

/**
 * @method static getAreas(bool $street = false)
 * @method static formatAreas(array $areas = [])
 * @method static getNameByCode(string $areaCode = '')
 * @method static getCodeByName(string $name = '', string $type = '')
 */
class AreaServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return AreaService::class;
    }
}