<?php

namespace app\xs_cloud\facade\service;

use xsframe\base\BaseFacade;
use app\xs_cloud\service\MemberService;

class MemberServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return MemberService::class;
    }
}