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

class WeekUtil
{

    public static function getWeek($key = 0)
    {
        switch ($key) {
            case 0:
                $week = "星期日";
                break;
            case 1:
                $week = "星期一";
                break;
            case 2:
                $week = "星期二";
                break;
            case 3:
                $week = "星期三";
                break;
            case 4:
                $week = "星期四";
                break;
            case 5:
                $week = "星期五";
                break;
            case 6:
                $week = "星期六";
                break;
            default:
                $week = "未知";
        }
        return $week;
    }
}