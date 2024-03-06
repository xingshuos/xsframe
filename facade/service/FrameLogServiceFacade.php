<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\FrameLogService;

class FrameLogServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return FrameLogService::class;
    }
}