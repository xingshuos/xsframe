<?php


namespace app\store\facade\service;


use app\store\service\SmsService;
use think\Facade;

/**
 * @method static sendSMS($smsSet, string $mobile, $tplId)
 * @method static checkSmsCode(string $mobile, string $verifyCode, bool $clear = true)
 */
class SmsServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return SmsService::class;
    }
}