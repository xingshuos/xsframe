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


class PasswordUtil
{
    /**
     * 密码处理
     * @param string $password 密码原码
     * @param string $salt 盐
     * @return array
     */
    public static function processCipher($password,$salt=''){
        if(empty($salt)){
            $intermediateSalt = md5(uniqid(rand(), true));
            $mix = rand(1, 26);
            $salt = substr($intermediateSalt, $mix, 6);
            $pw = hash("sha256", $password . $salt);
            $re = [
                'salt' => $salt,
                'password' => $pw,
            ];
        }else{
            $pw = hash("sha256", $password . $salt);
            $re = [
                'salt' => $salt,
                'password' => $pw,
            ];
        }
        return $re;
    }
}