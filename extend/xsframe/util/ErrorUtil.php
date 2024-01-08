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

class ErrorUtil
{
    /**
     * 构造错误数组
     * @param $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function error($code, $msg = '', $data = array())
    {
        return array(
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        );
    }

    /**
     * 检测返回值是否产生错误
     *
     * 产生错误则返回true，否则返回false
     *
     * @param mixed $data 待检测的数据
     * @return boolean
     */
    public static function isError($data)
    {
        if ((is_numeric($data) && $data < 1) || empty($data) || (is_array($data) && array_key_exists('code', $data) && $data['code'] <= 0)) {
            return true;
        } else {
            return false;
        }
    }
}