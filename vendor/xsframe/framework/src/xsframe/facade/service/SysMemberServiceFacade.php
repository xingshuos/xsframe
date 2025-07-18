<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\facade\service;


use xsframe\base\BaseFacade;
use xsframe\service\SysMemberService;

/**
 * @method static getUserId(string $token = null)
 * @method static checkLogin(string $token = null)
 * @method static mobileLogin(string $mobile, string $password = null, int $code = null, int $testCode = null, bool $autoLogin = true)
 * @method static mobileBind(string $mobile, string $code, int|string $int = null)
 * @method static setCredit($userId, string $filed, int|mixed $value, array|string $remark = [])
 * @method static getToken(int $memberId)
 * @method static register(mixed|string $username, string $code = null, int $testCode = null, array $updateData = [])
 * @method static logout(int $memberId = null)
 * @method static setMember(array $updateData = [], array $where = [])
 * @method static getUserInfo(array $where = [])
 */
class SysMemberServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return SysMemberService::class;
    }
}