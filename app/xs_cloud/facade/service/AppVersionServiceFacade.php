<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\AppVersionService;

class AppVersionServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return AppVersionService::class;
    }
}