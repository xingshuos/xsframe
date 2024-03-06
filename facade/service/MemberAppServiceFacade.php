<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\MemberAppService;

class MemberAppServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return MemberAppService::class;
    }
}