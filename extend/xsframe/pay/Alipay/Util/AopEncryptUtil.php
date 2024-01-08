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

/**
 * Created by Date: 2019/4/12
 */

namespace xsframe\pay\Alipay\Util;


class AopEncryptUtil
{
    /**
     *   加密工具类
     * info: jiehua
     * Date: 16/3/30
     * Time: 下午3:25
     */

    /**
     * 加密方法
     *
     * @param string $str
     *
     * @return string
     */
    public static function encrypt($str, $screct_key)
    {
        //AES, 128 模式加密数据 CBC
        $screct_key  = base64_decode($screct_key);
        $str         = trim($str);
        $str         = self::addPKCS7Padding($str);
        $iv          = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
        return base64_encode($encrypt_str);
    }

    /**
     * 解密方法
     *
     * @param string $str
     *
     * @return string
     */
    public static function decrypt($str, $screct_key)
    {
        //AES, 128 模式加密数据 CBC
        $str         = base64_decode($str);
        $screct_key  = base64_decode($screct_key);
        $iv          = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
        $encrypt_str = trim($encrypt_str);

        $encrypt_str = self::stripPKSC7Padding($encrypt_str);
        return $encrypt_str;

    }


    /**
     * AES 加密
     *
     * @param $plainText
     * @param $screctKey
     *
     * @return string
     */
    public static function encodeSslAESBase64($plainText, $screctKey)
    {
        $screctKey     = base64_decode($screctKey);
        $plainText     = trim($plainText);
        $cipher        = "AES-128-CBC";
        $ivLen         = openssl_cipher_iv_length($cipher);
        $iv            = openssl_random_pseudo_bytes($ivLen);
        $iv            = substr(md5($iv), 0, 16);
        $cipherTextRaw = openssl_encrypt($plainText, $cipher, $screctKey, $options = OPENSSL_RAW_DATA, $iv);
        $hmac          = md5($cipherTextRaw);
        return base64_encode($iv . $hmac . $cipherTextRaw);
    }

    /**
     * AES解密
     *
     * @param $cipherText
     * @param $secretKey
     *
     * @return null|string
     */
    public static function decodeSslAESBase64($cipherText, $secretKey)
    {

        $cipherText = base64_decode($cipherText);
        $cipher     = "AES-128-CBC";
        $ivLen      = openssl_cipher_iv_length($cipher);
        $iv         = substr($cipherText, 0, $ivLen);
        $hmac       = substr($cipherText, $ivLen, $sha2len = 32);

        $cipherTextRaw      = substr($cipherText, $ivLen + $sha2len);
        $original_plaintext = openssl_decrypt($cipherTextRaw, $cipher, $secretKey, $options = OPENSSL_RAW_DATA, $iv);
        $calcMac            = md5($cipherTextRaw);
        if (hash_equals($hmac, $calcMac)) {
            return $original_plaintext;
        }
        return null;
    }

    /**
     * 填充算法
     *
     * @param string $source
     *
     * @return string
     */
    public static function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block  = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char   = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }

    /**
     * 移去填充算法
     *
     * @param string $source
     *
     * @return string
     */
    public static function stripPKSC7Padding($source)
    {
        $source = trim($source);
        $char   = substr($source, -1);
        $num    = ord($char);
        if ($num == 62)
            return $source;
        $source = substr($source, 0, -$num);
        return $source;
    }


}