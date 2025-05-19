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


use think\Facade;
use xsframe\service\WeixinService;

/**
 * @method static getAccountList($isReload = false, string $appId = null, string $secret = null)
 * @method static getDepartmentList($isReload = false, string $appId = null, string $secret = null)
 * @method static getDepartmentUserList($departmentId = null, $isReload = false, string $appId = null, string $secret = null)
 * @method static getContactWay(string $openKFid, $isReload = false, string $appId = null, string $secret = null)
 * @method static getAccessToken(string $expire = 7000, $isReload = false, string $appId = null, string $secret = null)
 */
class WeixinServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return WeixinService::class;
    }
}