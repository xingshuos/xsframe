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


use xsframe\service\WechatService;
use think\Facade;

/**
 * @method static getSignPackage($appId, $secret, string $url)
 * @method static sendTplNotice($appId, $secret, string $openid, array $msg, string $url, string $templateId)
 * @method static wechatAuth(string $code, string $appid, string $secret, string $snsapi = 'snsapi_base')
 */
class WechatServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return WechatService::class;
    }
}