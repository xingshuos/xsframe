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


use xsframe\service\SmsService;
use think\Facade;

/**
 * @method static sendSMS($smsSet, string $mobile, $tplId)
 * @method static checkSmsCode(string $mobile, string $verifyCode, bool $clear = true)
 * @method static send($accessKeyId, $accessKeySecret, $signName, $mobile, string $tplId, array $array)
 * @method static sendLoginCode(string $mobile)
 */
class SmsServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return SmsService::class;
    }
}