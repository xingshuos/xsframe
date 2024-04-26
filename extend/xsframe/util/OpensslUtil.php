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

class OpensslUtil
{
    /**
     * 私钥、公钥加密
     * @param $data
     * @param $type
     * @return string
     */
    public static function sslEncode($data, $type = 'pu')
    {
        $encrypted = "";
        if ($type == 'pi') {
            // 私钥加密
            openssl_private_encrypt($data, $encrypted, config('ssl.rsa.private'), OPENSSL_PKCS1_PADDING);
        } else if ($type == 'pu') {
            // 公钥加密
            foreach (str_split($data, 117) as $chunk) {

                openssl_public_encrypt($chunk, $encryptData, config('ssl.rsa.public'), OPENSSL_PKCS1_PADDING);

                $encrypted .= $encryptData;
            }
        }

        //    $encrypt_data = bin2hex($encrypted);

        $encrypt_data = base64_encode($encrypted);

        return $encrypt_data;
    }

    /**
     * 私钥、公钥解密
     * @param $data
     * @param $type
     * @return string
     */
    public static function sslDecode($data, $type = 'pi')
    {
        $hex_encrypt_data = trim($data); // 十六进制数据
        //    $encrypt_data = pack("H*", $hex_encrypt_data);//对十六进制数据进行转换

        $encrypt_data = base64_decode($hex_encrypt_data);// 对十六进制数据进行转换

        $decrypted = "";
        if ($type == 'pu') {
            // 公钥解密
            openssl_public_decrypt($encrypt_data, $decrypted, config('ssl.rsa.public'), OPENSSL_PKCS1_PADDING);
        } else if
        ($type == 'pi') {
            // 私钥解密  ---- 分段解密
            $arrThrunk = str_split($encrypt_data, 256);
            foreach ($arrThrunk as $trunk) {
                $temp = '';
                if (openssl_private_decrypt($trunk, $temp, config('ssl.rsa.private'), OPENSSL_PKCS1_PADDING)) {
                    $decrypted .= $temp;
                } else {
                    return '';
                }
            }
        }
        return $decrypted;
    }

    // 加密
    public static function encrypt($string, $key, $iv = null, $expirationTime = null, $type = 'aes-128-cbc')
    {
        if (!$iv) {
            $ivLength = openssl_cipher_iv_length($type);
            $iv = openssl_random_pseudo_bytes($ivLength);
            $data = openssl_encrypt(strval($string) . ($expirationTime ? ":{$expirationTime}" : ''), $type, $key, OPENSSL_RAW_DATA, $iv);
            $retStr = bin2hex($iv . $data);
        } else {
            $data = openssl_encrypt(strval($string) . ($expirationTime ? ":{$expirationTime}" : ''), $type, $key, OPENSSL_RAW_DATA, $iv);
            $retStr = bin2hex($data);
        }
        return $retStr;
    }

    // 解密
    public static function decrypt($string, $key, $iv = null, $type = 'aes-128-cbc')
    {
        try {
            if (!$iv) {
                $ivLength = openssl_cipher_iv_length($type);
                $iv = substr(hex2bin($string), 0, $ivLength);
                $string = substr(hex2bin($string), $ivLength);
                $retStr = openssl_decrypt($string, $type, $key, OPENSSL_RAW_DATA, $iv);
            } else {
                $retStr = openssl_decrypt(hex2bin($string), $type, $key, OPENSSL_RAW_DATA, $iv);
            }

            [$data, $expirationTime] = explode(':', $retStr);
            if ($expirationTime && time() > $expirationTime) {
                $data = "";
            }
        } catch (\Exception $e) {
            $data = "";
        }

        return $data;
    }

}