<?php

namespace app\xs_cloud\facade\service;

use app\xs_cloud\service\MemberSiteService;
use xsframe\base\BaseFacade;

class MemberSiteServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return MemberSiteService::class;
    }
}