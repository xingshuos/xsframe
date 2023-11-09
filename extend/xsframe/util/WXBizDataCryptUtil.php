<?php

namespace xsframe\util;

class WXBizDataCryptUtil
{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;

    public static $block_size = 16;

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $appid
     * @param $sessionKey
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public static function decryptData($appid, $sessionKey, $encryptedData, $iv, &$data)
    {
        if (strlen($sessionKey) != 24) {
            return self::$IllegalAesKey;
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return self::$IllegalIv;
        }
        $aesIV     = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result    = self::decrypt($aesKey, $aesCipher, $aesIV);
        if ($result[0] != 0) {
            return $result[0];
        }
        $dataObj = json_decode($result[1]);
        if ($dataObj == NULL) {
            return self::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $appid) {
            return self::$IllegalBuffer;
        }
        $data = $result[1];
        return self::$OK;
    }

    /**
     * 对密文进行解密
     * @param $aesKey
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return array 解密得到的明文
     */
    public static function decrypt($aesKey, $aesCipher, $aesIV)
    {
        $decrypted = '';
        try {
            // php7.3以后解密
            $decrypted = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $aesIV);
        } catch (Exception $e) {
            return array(self::$IllegalBuffer, NULL);
        }

        try {
            $result = self::decode($decrypted);
        } catch (Exception $e) {
            return array(self::$IllegalBuffer, NULL);
        }
        return array(0, $result);
    }

    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return string
     */
    public static function encode($text = '')
    {
        $text_length   = strlen($text);
        $amount_to_pad = self::$block_size - $text_length % self::$block_size;
        if ($amount_to_pad == 0) {
            $amount_to_pad = self::$block_size;
        }
        $pad_chr = chr($amount_to_pad);
        $tmp     = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    public static function decode($text = '')
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || 32 < $pad) {
            $pad = 0;
        }
        return substr($text, 0, strlen($text) - $pad);
    }
}