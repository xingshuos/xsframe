<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\util;


class RandomUtil
{
    public static function createOrderNum($prefix, $length = 18, $numeric = true)
    {
        return $prefix . self::random($length, $numeric);
    }

    /**
     * 生成随机数
     * @param $length
     * @param bool $numeric 1是数字
     * @return string
     */
    public static function random($length, $numeric = FALSE)
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
    public function orderNum($prefix, $length = 4)
    {
        $time = microtime();
        list($w, $t) = explode(' ', $time);
        list($a, $b) = explode('.', $w);

        return $prefix . $this->randomAll($length, 'letter', 1) . $t . $b;
    }

    /**
     * 随机字符
     * @param int $length 长度
     * @param string $type 类型
     * @param int $convert 转换大小写
     * @return string
     */
    public function randomAll($length = 6, $type = 'all', $convert = 0)
    {
        $config = array(
            'number' => '1234567890',
            'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'all'    => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789'
        );

        if (!isset($config[$type]))
            $type = 'number';
        $string = $config[$type];

        $code   = '';
        $strlen = strlen($string) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[mt_rand(0, $strlen)];
        }

        if ($convert == 1) {
            $code = strtoupper($code);
        } elseif ($convert == 2) {
            $code = strtolower($code);
        }

        return $code;
    }

}