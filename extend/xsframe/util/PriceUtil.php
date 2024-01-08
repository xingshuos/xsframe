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

class PriceUtil
{
    /**
     * 转换
     * @param $money
     * @param int $precision 精度
     * @param bool $isReserveNum 是否保留位数
     * @return float
     */
    public static function numberFormat($money, int $precision = 2, bool $isReserveNum = false): float
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
    public static function yuan2jiao($money): int
    {
        return intval(round(floatval($money) * 10));
    }

    /**
     * 角转元
     *
     * @param $money
     * @return float
     */
    public static function jiao2yuan($money): float
    {
        return round($money / 10, 2);
    }

    /**
     * 元转分
     *
     * @param $money
     * @return int
     */
    public static function yuan2fen($money): int
    {
        return intval(round(floatval($money) * 100));
    }

    /**
     * 分转元
     *
     * @param $money
     * @return float
     */
    public static function fen2yuan($money): float
    {
        return round($money / 100, 2);
    }

    /**
     * 通过类型计算金额
     * @param float $basePrice
     * @param float $numberPrice
     * @param int $calcType
     * @param int $digits
     * @return float
     */
    public static function getMoney(float $basePrice = 0.00, float $numberPrice = 0.00, int $calcType = 1, int $digits = 100): float
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