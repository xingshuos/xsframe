<?php

namespace app\admin\facade\service;

use xsframe\base\BaseFacade;
use app\admin\service\CommonService;

/**
 * @method static getAreas()
 * @method static getProvinceNameByCode(string $string)
 * @method static getProvinceCodeByName(string $string)
 */
class CommonServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return CommonService::class;
    }
}