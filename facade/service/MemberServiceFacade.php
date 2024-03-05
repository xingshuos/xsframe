<?php

namespace app\jt_mail\facade\service;

use xsframe\base\BaseFacade;
use app\jt_mail\service\MemberService;

/**
 * @method static login(string $username, string $password)
 * @method static register(string $username, string $mobile, string $password)
 * @method static forget(string $mobile, string $password)
 * @method static checkLogin()
 * @method static checkMember()
 * @method static logout()
 * @method static creditPay(array $orderInfo)
 * @method static mobileLogin(string $mobile, string $password = null, int $code = null)
 */
class MemberServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return MemberService::class;
    }
}