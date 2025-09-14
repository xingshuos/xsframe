<?php

namespace xsframe\util;

use xsframe\exception\ApiException;

class LicenseUtil
{
    const PLATFORM_ID = 'xsframe_license';

    /**
     * 生成加密License
     * @param int $expireTimestamp 过期时间戳
     * @param string $clientId 客户端ID
     * @param string $masterKey 主密钥
     * @param string $keySalt 密钥盐值
     * @return string Base64编码的License
     */
    public static function generateLicense(
        int    $expireTimestamp,
        string $clientId,
        string $masterKey,
        string $keySalt
    ): string
    {
        // 构建验证数据结构
        $data = [
            'expire'    => $expireTimestamp,
            'client_id' => $clientId,
            'platform'  => self::PLATFORM_ID,
            'salt'      => bin2hex(random_bytes(16)), // 32字符随机盐值
            'date'      => date('Ymd') // 生成日期
        ];

        // 添加数据签名
        $data['signature'] = self::generateDataSignature($data, $masterKey, $keySalt);

        $json = json_encode($data);
        $iv = self::generateIv($masterKey, $keySalt);

        // AES-256-CBC加密
        $encrypted = openssl_encrypt(
            $json,
            'AES-256-CBC',
            self::getEncryptionKey($masterKey, $keySalt),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new ApiException('License加密失败');
        }

        // 组合IV和加密数据
        return base64_encode($iv . $encrypted);
    }

    /**
     * 验证License有效性
     * @param string $license
     * @param string $masterKey
     * @param string $keySalt
     * @param bool $returnTime
     * @return bool|mixed
     */
    public static function validateLicense(
        string $license,
        string $masterKey,
        string $keySalt = '',
        bool   $returnTime = false
    )
    {
        if (empty($keySalt)) {
            $keySalt = $masterKey;
        }
        $raw = base64_decode($license, true);
        if ($raw === false) {
            return false;
        }

        $iv = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);

        // 解密数据
        $json = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            self::getEncryptionKey($masterKey, $keySalt),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($json === false) {
            return false;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return false;
        }

        // 验证数据完整性
        if (!self::validateDataSignature($data, $masterKey, $keySalt)) {
            return false;
        }

        // 验证平台标识
        if (!isset($data['platform']) || $data['platform'] !== self::PLATFORM_ID) {
            return false;
        }

        // 检查过期时间
        if ($returnTime) {
            return $data['expire'];
        } else {
            return time() < (int)$data['expire'];
        }
    }

    // 获取过期时间
    public static function getExpireTime(
        string $license,
        string $masterKey,
        string $keySalt = ''
    ): int
    {
        if (empty($keySalt)) {
            $keySalt = $masterKey;
        }
        return self::validateLicense($license, $masterKey, $keySalt, true);
    }

    /**
     * 生成数据签名
     * @param array $data
     * @param string $masterKey
     * @param string $keySalt
     * @return string
     */
    private static function generateDataSignature(
        array  $data,
        string $masterKey,
        string $keySalt
    ): string
    {
        $signingData = [
            $data['expire'],
            $data['client_id'],
            $data['platform'],
            $data['salt'],
            $data['date']
        ];

        return hash_hmac('sha256', implode('|', $signingData),
            self::getSignatureKey($masterKey, $keySalt));
    }

    /**
     * 验证数据签名
     * @param array $data
     * @param string $masterKey
     * @param string $keySalt
     * @return bool
     */
    private static function validateDataSignature(
        array  $data,
        string $masterKey,
        string $keySalt
    ): bool
    {
        if (!isset($data['signature'])) {
            return false;
        }

        $expected = self::generateDataSignature($data, $masterKey, $keySalt);
        return hash_equals($expected, $data['signature']);
    }

    /**
     * 生成初始化向量
     * @param string $masterKey
     * @param string $keySalt
     * @return string
     */
    private static function generateIv(string $masterKey, string $keySalt): string
    {
        return substr(hash('sha256', self::getIvKey($masterKey, $keySalt)), 0, 16);
    }

    /**
     * 获取加密密钥
     * @param string $masterKey
     * @param string $keySalt
     * @return string
     */
    private static function getEncryptionKey(string $masterKey, string $keySalt): string
    {
        return hash('sha256', $masterKey . $keySalt . 'encryption');
    }

    /**
     * 获取签名密钥
     * @param string $masterKey
     * @param string $keySalt
     * @return string
     */
    private static function getSignatureKey(string $masterKey, string $keySalt): string
    {
        return hash('sha256', $masterKey . $keySalt . 'signature');
    }

    /**
     * 获取IV密钥
     * @param string $masterKey
     * @param string $keySalt
     * @return string
     */
    private static function getIvKey(string $masterKey, string $keySalt): string
    {
        return hash('sha256', $masterKey . $keySalt . 'iv');
    }
}
