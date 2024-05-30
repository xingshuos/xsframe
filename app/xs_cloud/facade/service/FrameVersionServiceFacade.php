<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\FrameVersionService;

class FrameVersionServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return FrameVersionService::class;
    }
}