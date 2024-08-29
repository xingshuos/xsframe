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

namespace xsframe\util;

class ValidateUtil
{
    /**
     * 验证域名格式是否正确
     * @param string $domain 域名
     * @return bool
     */
    public static function isDomain(string $domain, $checkDns = false): bool
    {
        if ($checkDns) {
            return self::isDomain($domain) && self::isDomainResolvable($domain);
        }
        return preg_match('/^([a-z\d]([-a-z\d]*[a-z\d])?\.)+[a-z\d]{2,}$/i', $domain) === 1;
    }

    /**
     * 验证域名dns解析是否正确
     * @param string $domain 域名
     * @return bool
     */
    public static function isDomainResolvable($domain)
    {
        return checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA') || checkdnsrr($domain, 'MX');
    }

    /**
     * 验证手机号格式是否正确
     * @param string $mobile 手机号
     * @return bool
     */
    public static function isMobile(string $mobile): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $mobile);
    }

    /**
     * 验证身份证号码格式是否正确
     * @param string $idCard 身份证号
     * @return bool
     */
    public static function isIdCard(string $idCard): bool
    {
        // 这里可以添加更复杂的验证逻辑，比如校验码计算等
        return preg_match('/^(\d{15}|\d{17}[\dXx])$/', $idCard);
    }

    /**
     * 验证姓名是否包含中文字符
     * @param string $name 姓名
     * @return bool
     */
    public static function isChineseName(string $name): bool
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $name);
    }

    /**
     * 验证邮箱格式是否正确
     * @param string $email 邮箱地址
     * @return bool
     */
    public static function isEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 验证密码强度（例如：至少6位，包含数字和字母）
     * @param string $password 密码
     * @return bool
     */
    public static function isStrongPassword(string $password): bool
    {
        return preg_match('/^(?=.*[0-9])(?=.*[a-zA-Z]).{6,}$/', $password);
    }

    /**
     * 验证URL格式是否正确
     * @param string $url URL地址
     * @return bool
     */
    public static function isUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * 验证IP地址格式是否正确
     * @param string $ip IP地址
     * @return bool
     */
    public static function isIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * 验证是否包含特殊字符（可自定义特殊字符列表）
     * @param string $str 待验证字符串
     * @param string $specialChars 特殊字符列表，默认为一些常见特殊字符
     * @return bool
     */
    public static function containsSpecialChars(string $str, string $specialChars = '@#$%^&*()_+<>:"-}[{]'): bool
    {
        return preg_match('/[' . preg_quote($specialChars, '/') . ']/', $str);
    }

    /**
     * 验证是否为有效的日期格式
     * @param string $date 日期字符串
     * @param string $format 日期格式，默认为'Y-m-d'
     * @return bool
     */
    public static function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * 验证是否为有效的日期时间格式
     * @param string $dateTime 日期时间字符串
     * @param string $format 日期时间格式，默认为'Y-m-d H:i:s'
     * @return bool
     */
    public static function isValidDateTime(string $dateTime, string $format = 'Y-m-d H:i:s'): bool
    {
        $dt = \DateTime::createFromFormat($format, $dateTime);
        return $dt && $dt->format($format) === $dateTime;
    }

    /**
     * 验证是否为微信浏览器
     * @return bool
     */
    public static function isWechat(): bool
    {
        if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否为二维数组
     * @param $array
     * @return bool
     */
    public static function isArray2($array): bool
    {
        if (is_array($array)) {
            foreach ($array as $v) {
                return is_array($v);
            }
            return false;
        }
        return false;
    }
}