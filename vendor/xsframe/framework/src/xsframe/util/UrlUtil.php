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

    /**
     * 处理URL端口，确保非标准端口被正确包含
     * 主要用于处理完整URL时，确保端口号一致性
     *
     * @param string $url 要处理的URL
     * @param bool $full 是否进行完整处理（包括端口检查）
     * @return string 处理后的URL
     */
    public static function processUrlWithPort($url, $full = true)
    {
        if (!$full) {
            return $url;
        }

        // 1. 获取当前请求的主机名（可能包含端口）
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $port = $_SERVER['SERVER_PORT'] ?? '';

        // 如果 host 中没有端口号，但使用了非标准端口，则手动拼接端口
        if (!StringUtil::strexists($host, ':') && $port && !in_array($port, ['80', '443'])) {
            $host .= ':' . $port;
        }

        if (!empty($host)) {
            $parsedUrl = parse_url($url);
            if ($parsedUrl && isset($parsedUrl['host'])) {
                // 检查生成的 URL 是否缺少端口，且当前请求确实使用了非标准端口
                if (!isset($parsedUrl['port']) && isset($port) && !in_array($port, ['80', '443'])) {
                    // 重新构建 URL，使用带端口的 host 替换原有主机
                    $newUrl = (isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '//') . $host;
                    if (isset($parsedUrl['path'])) $newUrl .= $parsedUrl['path'];
                    if (isset($parsedUrl['query'])) $newUrl .= '?' . $parsedUrl['query'];
                    if (isset($parsedUrl['fragment'])) $newUrl .= '#' . $parsedUrl['fragment'];
                    $url = $newUrl;
                }
            }
        }

        return $url;
    }

}