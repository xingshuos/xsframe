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

class AesEncoderUtil
{

    // 微信退款回调解密
    public static function decrypt(string $encryptData, string $Key = '')
    {
        $md5LowerKey = strtolower(md5($Key));
        return ArrayUtil::xml2array(openssl_decrypt($encryptData, "AES-256-ECB", $md5LowerKey));
    }

}