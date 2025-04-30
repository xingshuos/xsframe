<?php

namespace app\admin\facade\service;

use xsframe\base\BaseFacade;
use app\admin\service\CommonService;

/**
 * @method static getAreas()
 */
class CommonServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return CommonService::class;
    }
}