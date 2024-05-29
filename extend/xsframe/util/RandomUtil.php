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


class RandomUtil
{

    /**
     * 生成随机订单号
     * @param $prefix
     * @param $length
     * @param $numeric
     * @return string
     */
    public static function createOrderNum($prefix, $length = 18, $numeric = true): string
    {
        return $prefix . self::random($length, $numeric);
    }

    /**
     * 生成随机数
     * @param $length
     * @param bool $numeric 1是数字
     * @return string
     */
    public static function random($length, $numeric = false): string
    {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }

    //订单号.流水号
    public static function orderNum($prefix, $length = 4): string
    {
        $time = microtime();
        [$w, $t] = explode(' ', $time);
        [$a, $b] = explode('.', $w);

        return $prefix . self::randomAll($length, 'letter', 1) . $t . $b;
    }

    /**
     * 随机字符
     * @param int $length 长度
     * @param string $type 类型
     * @param int $convert 转换大小写
     * @return string
     */
    public static function randomAll($length = 6, $type = 'all', $convert = 0): string
    {
        $config = [
            'number' => '1234567890',
            'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'all'    => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789'
        ];

        if (!isset($config[$type])) {
            $type = 'number';
        }

        $string = $config[$type];

        $code = '';
        $strlen = strlen($string) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[mt_rand(0, $strlen)];
        }

        if ($convert == 1) {
            $code = strtoupper($code);
        } else if ($convert == 2) {
            $code = strtolower($code);
        }

        return $code;
    }

    /**
     * @param $key string 用户ID或唯一值
     * @param int $length
     * @return string
     */
    public static function generateAppId(string $key, int $length = 18): string
    {
        // 字符集：小写字母和数字
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);

        // 使用用户ID作为秘钥的一部分
        $appId = '';
        $userIdHex = dechex($key); // 将用户ID转换为十六进制
        // 确保用户ID部分至少占一定长度（这里假设至少4位）
        $appId .= str_pad(strtoupper($userIdHex), 4, '0', STR_PAD_LEFT)[0] . substr(strtoupper($userIdHex), 1); // 取首字母大写，其余小写

        // 生成剩余的随机部分
        $remainingLength = $length - strlen($appId);
        for ($i = 0; $i < $remainingLength; $i++) {
            $index = @random_int(0, $charactersLength - 1);
            $appId .= $characters[$index];
        }

        // 打乱字符串以增加随机性（可选）
        return str_shuffle($appId);
    }

}