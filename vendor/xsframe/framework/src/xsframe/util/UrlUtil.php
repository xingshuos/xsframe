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

class UrlUtil
{
    public static function getBaseUrl($url = null)
    {
        // 使用 parse_url 解析 URL
        $parsedUrl = parse_url($url);

        // 获取协议和主机名，并将它们组合起来
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';

        return $scheme . $host;
    }

    public static function download($remoteFileUrl, $filename = null)
    {
        $localFilename = $filename;
        if (empty($filename)) {
            $localFilename = basename($remoteFileUrl);
        }

        $baseUrl = self::getBaseUrl($remoteFileUrl);

        // 调试输出URL（正式环境移除）
        // error_log("尝试下载文件: " . $remoteFileUrl);

        // 设置响应头
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . htmlspecialchars($localFilename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $remoteFileUrl,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false, // 禁用SSL验证（仅测试用）
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', // 模拟浏览器
            CURLOPT_HTTPHEADER     => [
                "Referer: {$baseUrl}" // 有些服务器需要Referer
            ]
        ]);

        $success = curl_exec($ch);
        if (!$success) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("下载失败: " . $error);
        }
        curl_close($ch);
        exit;
    }
}