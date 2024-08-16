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

class TimeUtil
{
    // 批量设置时间格式
    public static function setTimes($list = [], $fields = null, $format = null)
    {
        if (empty($list)) {
            return [];
        }

        if (empty($fields)) {
            foreach ($list as &$row) {
                $row = date($format ?: 'Y-m-d H:i:s', $row);
            }

            return $list;
        }

        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        if (is_object($list)) {
            $list = $list->toArray();
        }

        if (is_array2($list)) {
            foreach ($list as $key => &$value) {
                foreach ($fields as $field) {
                    if (strexists($field, ".")) {
                        $str = explode(".", $field);
                        if (isset($value[$str[0]][$str[1]])) {
                            $value[$str[0]][$str[1]] = date($format ?: 'Y-m-d H:i:s', $value[$str[0]][$str[1]]);
                        }
                    }

                    if (isset($list[$field])) {
                        $list[$field] = date($format ?: 'Y-m-d H:i:s', $list[$field]);
                    }

                    if (is_array($value) && isset($value[$field])) {
                        $value[$field] = date($format ?: 'Y-m-d H:i:s', $value[$field]);
                    }
                }
            }

            return $list;
        }

        foreach ($fields as $field) {
            if (isset($list[$field])) {
                $list[$field] = tomedia($list[$field]);
                $list[$field] = date($format ?: 'Y-m-d H:i:s', $list[$field]);
            }
        }

        return $list;
    }

    // 获取星期几
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

    // 秒转时分秒
    public static function changeTimeType($seconds)
    {
        if ($seconds > 3600) {
            $hours = intval($seconds / 3600);
            $time = $hours . ":" . gmstrftime('%M:%S', $seconds);
        } else {
            $time = gmstrftime('%H:%M:%S', $seconds);
        }
        return $time;
    }

    // 将时分秒转换成秒数
    public static function timeToDuration($time)
    {
        $parsed = date_parse($time);
        $duration = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
        return $duration;
    }

    // 获取时间段
    public static function timeSlot($time)
    {
        $datetime = date('H', $time);
        $text = "";

        if ($datetime >= 0 && $datetime < 5) {
            $text = "凌晨";
        } else if ($datetime >= 5 && $datetime < 11) {
            $text = "上午";
        } else if ($datetime >= 11 && $datetime < 13) {
            $text = "中午";
        } else if ($datetime >= 13 && $datetime < 17) {
            $text = "下午";
        } else if ($datetime >= 17 && $datetime < 19) {
            $text = "傍晚";
        } else if ($datetime >= 19 && $datetime < 24) {
            $text = "晚上";
        }

        return $text;
    }

    // 计算多少时间以前
    public static function formatTimeToStr($time)
    {
        $diff = time() - $time;
        $day = floor($diff / 86400);
        $free = $diff % 86400;

        if ($day > 0) {
            $month = floor($day / 30);
            $monthNum = intval($day / 30);
            if ($month > 0) {
                if ($monthNum > 12) {
                    $yearNum = intval($monthNum / 12);
                    $timeStr = $yearNum . "年前";
                } else {
                    $timeStr = $monthNum . "月前";
                }
            } else {
                $timeStr = $day . "天前";
            }
        } else {
            if ($free > 0) {
                $hour = floor($free / 3600);
                $free = $free % 3600;
                if ($hour > 0) {
                    $timeStr = $hour . "小时前";
                } else {
                    if ($free > 0) {
                        $min = floor($free / 60);
                        $free = $free % 60;
                        if ($min > 0) {
                            $timeStr = $min . "分钟前";
                        } else {
                            if ($free > 0) {
                                $timeStr = $free . "秒前";
                            } else {
                                $timeStr = '刚刚';
                            }
                        }
                    } else {
                        $timeStr = '刚刚';
                    }
                }
            } else {
                $timeStr = '刚刚';
            }
        }

        return $timeStr;
    }

    /**
     * 获取时间差，毫秒级 64 位系统函数
     *
     * @return string
     */
    public static function getMillisecond()
    {
        [$s1, $s2] = explode(' ', microtime());
        return sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 获取时间差，毫秒级 32 位系统函数
     *
     * @return string
     */
    public static function getMillisecond32()
    {
        [$s1, $s2] = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /*
     * 获取时间差，毫秒级
     */
    public static function getSubtraction()
    {
        $t1 = microtime(true);
        $t2 = microtime(true);
        return (($t2 - $t1) * 1000) . 'ms';
    }

    /*
     * microsecond 微秒     millisecond 毫秒
     *返回时间戳的毫秒数部分
     */
    public static function getMillisecondMerge()
    {
        [$usec, $sec] = explode(" ", microtime());
        $msec = round($usec * 1000);
        return $msec;
    }

    /*
     *
     *返回字符串的毫秒数时间戳
     */
    public static function getTotalMillisecond()
    {
        $time = explode(" ", microtime());
        $time = $time [1] . ($time [0] * 1000);
        $time2 = explode(".", $time);
        $time = $time2 [0];
        return $time;
    }

    /*
     *
     *返回当前 Unix 时间戳和微秒数(用秒的小数表示)浮点数表示，常用来计算代码段执行时间
     */
    public static function microtimeFloat()
    {
        [$usec, $sec] = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function formatString(string $string)
    {
        $length = strlen($string);
        if ($length == 8) {
            $year = mb_substr($string, 0, 4);
            $month = mb_substr($string, 4, 2);
            $day = mb_substr($string, 6, 2);
            try {
                $data = new \DateTime($year . '-' . $month . '-' . $day);
            } catch (\Exception $e) {
            }
            $string = $data->format('Y年m月d日') . self::formatWeek($year . '-' . $month . '-' . $day);
            return $string;
        }
        return '';
    }

    // 获取当前时间是星期几
    public static function formatWeek(string $timeString)
    {
        $week = date('w', strtotime($timeString));
        $weekarray = ["日", "一", "二", "三", "四", "五", "六"]; //先定义一个数组
        return "星期" . $weekarray[$week];
    }

    // 计算相差天数
    public static function diffDate($date1, $date2)
    {
        try {
            $datetime1 = new \DateTime($date1);
        } catch (\Exception $e) {
        }
        try {
            $datetime2 = new \DateTime($date2);
        } catch (\Exception $e) {
        }
        $interval = $datetime1->diff($datetime2);
        $time['y'] = $interval->format('%Y');
        $time['m'] = $interval->format('%m');
        $time['d'] = $interval->format('%d');
        $time['h'] = $interval->format('%H');
        $time['i'] = $interval->format('%i');
        $time['s'] = $interval->format('%s');
        $time['a'] = $interval->format('%a');    // 两个时间相差总天数
        return $time;
    }

    /**
     * 计算两个时（datetime）间隔了几个月
     */
    public static function getDiffMonth($date1, $date2)
    {
        try {
            $datetime1 = new \DateTime($date1);
        } catch (\Exception $e) {
        }
        try {
            $datetime2 = new \DateTime($date2);
        } catch (\Exception $e) {
        }
        $interval = $datetime1->diff($datetime2);
        $year = intval($interval->format('%Y'));
        $month = intval($interval->format('%m'));
        $diff_month = ($year * 12) + $month;
        return $diff_month;
    }

    // 计算两个时间间隔天数
    public static function getDiffDay($date1, $date2)
    {
        $date1_stamp = strtotime($date1);
        $date2_stamp = strtotime($date2);
        return abs(($date1_stamp - $date2_stamp) / (24 * 3600));
    }

    // 获取本周开始时间
    public static function getCurWeekStart($isData = false)
    {
        $timeStamp = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        return $isData ? date("Y-m-d H:i:s", $timeStamp) : $timeStamp;
    }

    // 获取本月开始时间
    public static function getCurMonthStart($isData = false)
    {
        $timeStamp = mktime(0, 0, 0, date('m'), 1, date('Y'));
        return $isData ? date("Y-m-d H:i:s", $timeStamp) : $timeStamp;
    }

    // 获取本月结束时间
    public static function getMonthLastDay($year, $month)
    {
        return date('t', strtotime($year . '-' . $month . ' -1'));
    }

    /**
     * 获取指定日期所在月的开始日期与结束日期
     * @param string $date
     * @param boolean 为true返回开始日期，否则返回结束日期
     * @return array
     * @access private
     */
    public static function getMonthRange($date, $returnFirstDay = true)
    {
        $timestamp = strtotime($date);
        if ($returnFirstDay) {
            $monthFirstDay = date('Y-m-1 00:00:00', $timestamp);
            return strtotime($monthFirstDay);
        } else {
            $mdays = date('t', $timestamp);
            $monthLastDay = date('Y-m-' . $mdays . ' 23:59:59', $timestamp);
            return strtotime($monthLastDay);
        }
    }

    // 获取N天之前的时间
    public static function getDayBefore($day = 1, $timeStamp = null, $isData = false)
    {
        $time = $timeStamp ?? time();
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        return $isData ? date("Y-m-d H:i:s", strtotime("-" . $day . " day", $time)) : strtotime("-" . $day . " day", $time);
    }

    // 获取N天之后的时间
    public static function getDayAfter($day = 1, $timeStamp = null, $isData = false)
    {
        $time = $timeStamp ?? time();
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        return $isData ? date("Y-m-d H:i:s", strtotime($day . " day", $time)) : strtotime("-" . $day . " day", $time);
    }

    // 获取N月之前的时间
    public static function getMonthBefore($month = 1, $timeStamp = null, $isData = false)
    {
        $time = $timeStamp ?? time();
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        return $isData ? date("Y-m-d H:i:s", strtotime("-" . $month . " month", $time)) : strtotime("-" . $month . " month", $time);
    }

    // 获取N月之后的时间
    public static function getMonthAfter($month = 1, $timeStamp = null, $isData = false)
    {
        $time = $timeStamp ?? time();
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        return $isData ? date("Y-m-d H:i:s", strtotime($month . " month", $time)) : strtotime($month . " month", $time);
    }

    // 时间转换
    public static function now($format = 'Y-m-d H:i:s', $time = "")
    {
        if (empty($time)) {
            return date($format);
        } else {
            return date($format, $time);
        }
    }

    // 时间格式化
    public static function timeFormat($time = NULL, $format = 'Y-m-d H:i:s', $str = "无")
    {
        $time = intval($time);
        if (empty($time)) {
            return $str;
        } else {
            return date($format, $time);
        }
    }

    //获取两个日期间隔天数
    public static function diffDay($startD, $endD)
    {
        try {
            $startDobj = new \DateTime($startD);
        } catch (\Exception $e) {
        }
        try {
            $endDobj = new \DateTime($endD);
        } catch (\Exception $e) {
        }
        return $startDobj->diff($endDobj)->days;
    }

    // 秒转时分秒信息
    public static function getSecondToTimeInfo($second)
    {
        $hour = floor($second / 3600);
        if ($hour < 10) {
            $hour = '0' . $hour;
        }

        $t = $second % 3600;
        $minute = floor($t / 60);
        if ($minute < 10) {
            $minute = '0' . $minute;
        }

        $secondDif = $second;
        if (!empty($hour)) {
            $secondDif = $secondDif - (intval($hour) * 60 * 60);
        }
        if (!empty($minute)) {
            $secondDif = $secondDif - (intval($minute) * 60);
        }

        if ($secondDif < 10) {
            $secondDif = '0' . $secondDif;
        }

        $result = [
            'hour'   => $hour,
            'minute' => $minute,
            'second' => intval($secondDif),
        ];
        return $result;
    }
}