<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\AppLogService;

class AppLogServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return AppLogService::class;
    }
}