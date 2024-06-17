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

use xsframe\exception\ApiException;
use think\facade\Log;

class LoggerUtil
{
    public static function alert($message)
    {
        self::write('alert', $message);
    }

    public static function critical($message)
    {
        self::write('critical', $message);
    }

    public static function error($message)
    {
        self::write('error', $message);
    }

    public static function warning($message)
    {
        self::write('warning', $message);
    }

    public static function notice($message)
    {
        self::write('notice', $message);
    }

    public static function info($message)
    {
        self::write('info', $message);
    }

    public static function debug($message)
    {
        self::write('debug', $message);
    }

    public static function emergency($message)
    {
        self::write('emergency', $message);
    }

    public static function sql($message)
    {
        self::write('sql', $message);
    }

    /**
     * 写入日志信息
     * @param $type
     * @param $message
     */
    public static function write($type, $message)
    {
        if (!in_array($type, ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])) {
            $type = "info";
        }
        Log::write($message, $type);
    }

    /**
     * 日志级别不存在
     * @param $name
     * @param $arguments
     * @throws ApiException
     */
    public static function __callStatic($name, $arguments)
    {
        throw new ApiException("{$name}:class not exist");
    }
}