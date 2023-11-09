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

class PhoneUtil
{

    /**
     * 手机号脱敏
     *
     * @param $phone
     * @return string
     */
    public static function desensitize($phone)
    {
        if (empty($phone) || StringUtil::emptyStr($phone)) {
            return '';
        }

        return substr($phone, 0, 3) . '****' . substr($phone, -4, 4);
    }
}