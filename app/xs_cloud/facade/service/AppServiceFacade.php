<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\AppService;

class AppServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return AppService::class;
    }
}