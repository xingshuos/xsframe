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

class PriceUtil
{
    /**
     * 转换
     * @param $money
     * @param int $precision 精度
     * @param bool $isReserveNum 是否保留位数
     * @return float
     */
    public static function numberFormat($money, $precision = 2, $isReserveNum = false)
    {
        $price = round($money, $precision);
        if ($isReserveNum) {
            $price = number_format($money, $precision, '.', '0');
        }
        return $price;
    }

    /**
     * 元转角
     *
     * @param $money
     * @return int
     */
    public static function yuan2jiao($money)
    {
        return intval(round(floatval($money) * 10));
    }

    /**
     * 角转元
     *
     * @param $money
     * @return int
     */
    public static function jiao2yuan($money)
    {
        return round($money / 10, 2);
    }

    /**
     * 元转分
     *
     * @param $money
     * @return int
     */
    public static function yuan2fen($money)
    {
        return intval(round(floatval($money) * 100));
    }

    /**
     * 分转元
     *
     * @param $money
     * @return int
     */
    public static function fen2yuan($money)
    {
        return round($money / 100, 2);
    }

    /**
     * 通过类型计算金额
     * @param $basePrice
     * @param $numberPrice
     * @param int $calcType
     * @param int $digits
     * @return float
     */
    public static function getMoney($basePrice = 0.00, $numberPrice = 0.00, $calcType = 1, $digits = 100)
    {
        $money = 0.00;
        if ($calcType == 1) { // 固定金额
            $money = $numberPrice;
        } elseif ($calcType == 2) { // 百分比
            $money = self::numberFormat(($basePrice * $numberPrice) / $digits, 2);
        }
        return $money;
    }
}