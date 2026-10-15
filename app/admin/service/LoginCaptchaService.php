<?php

namespace app\admin\service;

/**
 * 后台登录验证码配置服务。
 *
 * 统一处理历史配置缺少开关时的默认值，确保登录页展示与服务端校验使用同一判断规则。
 */
class LoginCaptchaService
{
    /** 登录验证码在网站设置中的配置键。 */
    public const SETTING_KEY = 'login_captcha';

    /**
     * 判断后台登录验证码是否开启。
     *
     * @param array<string, mixed> $websiteSettings 网站设置
     * @return bool 未保存过开关时默认开启
     */
    public static function isEnabled(array $websiteSettings): bool
    {
        if (!array_key_exists(self::SETTING_KEY, $websiteSettings)) {
            return true;
        }

        // 只有已规范保存的0表示关闭，异常配置仍按开启处理。
        return strval($websiteSettings[self::SETTING_KEY]) !== '0';
    }

    /**
     * 将后台提交的验证码开关转换为可存储的整数值。
     *
     * @param mixed $value 后台提交值
     * @return int 仅返回0或1
     */
    public static function normalize($value): int
    {
        // 只有明确提交0才关闭，缺失值或异常值均保持安全的开启状态。
        return strval($value) === '0' ? 0 : 1;
    }
}
