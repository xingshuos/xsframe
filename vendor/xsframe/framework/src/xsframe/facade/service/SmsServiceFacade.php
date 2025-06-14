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
 * @method static sendSMS(string $mobile, string $tplId, array $data = null, array $smsSet = null)
 * @method static checkSmsCode(string $mobile, string|int $verifyCode, string|int $testCode = null, bool $clear = true)
 * @method static customSendSMS(string $mobile, string $tplId, array $data = null, array $smsSet = null)
 * @method static send($accessKeyId, $accessKeySecret, $signName, $mobile, string $tplId, array $array)
 * @method static sendLoginCode(string $mobile, string $tplId = null, array $smsSet = null)
 * @method static sendRegisterCode(string $mobile, string $tplId = null, array $smsSet = null)
 * @method static sendUpdateCode(string $mobile, string $tplId = null, array $smsSet = null)
 * @method static sendChangeCode(string $mobile, string $tplId = null, array $smsSet = null)
 * @method static sendAuthCode(string $mobile, string $tplId = null, array $smsSet = null)
 * @method static getMobileCode(string $mobile)
 * @method static sendEmail(string $email, string $subject = null, string $body = null, array $smsSet = null)
 */
class SmsServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return SmsService::class;
    }
}