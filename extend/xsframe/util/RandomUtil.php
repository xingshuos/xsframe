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

use think\Exception;
use xsframe\exception\ApiException;

class RandomUtil
{

    /**
     * 生成随机订单号
     * @param $prefix
     * @param int $length
     * @param bool $numeric
     * @return string
     */
    public static function createOrderNum($prefix, int $length = 18, bool $numeric = true): string
    {
        return $prefix . self::random($length, $numeric);
    }

    /**
     * 生成随机数
     * @param $length
     * @param bool $numeric 1是数字
     * @return string
     */
    public static function random($length, bool $numeric = false): string
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
    public static function randomAll(int $length = 6, string $type = 'all', int $convert = 0): string
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
     * 生成AppID
     * @param string $prefix 前缀
     * @param string $key 唯一值
     * @param int $length 长度
     * @param bool $numeric 是否为数字
     * @return string
     */
    public static function generateAppId(string $prefix, string $key, int $length = 18, bool $numeric = false): string
    {
        $appId = self::random($length - strlen($key), $numeric) . $key;
        return $prefix . strtolower(str_shuffle($appId));
    }


}