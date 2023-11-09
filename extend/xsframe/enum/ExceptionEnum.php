<?php

namespace xsframe\enum;

use xsframe\base\BaseEnum;

class ExceptionEnum extends BaseEnum
{
    // 参考1：https://www.mysubmail.com/chs/documents/developer/c8ujr
    // 参考2：https://blog.csdn.net/qq_31059985/article/details/80505874

    const API_SUCCESS_CODE            = "200"; // 响应成功
    const API_REDIRECT_CODE           = "301"; // 永久性重定向
    const API_REDIRECT_TEMPORARY_CODE = "302"; // 暂时性重定向
    const API_LOGIN_ERROR_CODE        = "401"; // 用户未登录
    const API_PARAMS_ERROR_CODE       = "402"; // 参数异常
    const API_AUTH_ERROR_CODE         = "403"; // 权限不够
    const API_ERROR_CODE              = "404"; // 请求失败
    const SYSTEM_ERROR_CODE           = "500"; // 服务器错误

    // 验证码
    const SMS_VERIFY_CODE_ERROR = "90000";
    const SMS_RATE_ERROR        = "90001";
    const SMS_MOBILE_ERROR      = "90002";
    const SMS_SMSID_ERROR       = "90003";
    const SMS_PARAMS_ERROR      = "90004";

    public static function getText(string $type): string
    {
        $list = [
            self::API_SUCCESS_CODE            => 'success',
            self::API_REDIRECT_CODE           => 'redirect 301',
            self::API_REDIRECT_TEMPORARY_CODE => 'redirect 302',
            self::API_LOGIN_ERROR_CODE        => 'User is not logged in !',
            self::API_AUTH_ERROR_CODE         => 'Insufficient user permissions !',
            self::API_ERROR_CODE              => 'fail',
            self::SYSTEM_ERROR_CODE           => 'system error',

            self::SMS_VERIFY_CODE_ERROR => '验证码错误',
            self::SMS_MOBILE_ERROR      => '手机号格式错误',
            self::SMS_RATE_ERROR        => '60秒内只能发送一次',
            self::SMS_SMSID_ERROR       => '短信发送失败(NOSMSID)',
            self::SMS_PARAMS_ERROR      => '配置参数错误',
        ];
        $text = array_key_exists($type, $list) ? $list[$type] : '';
        return $text;
    }
}