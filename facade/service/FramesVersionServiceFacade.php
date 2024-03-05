<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\FramesVersionService;

class FramesVersionServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return FramesVersionService::class;
    }
}