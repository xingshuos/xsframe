<?php


namespace xsframe\wrapper;

use xsframe\exception\ApiException;
use xsframe\util\FileUtil;
use xsframe\util\RequestUtil;

class CloudWrapper
{

    /**
     * 下载云应用（整包下载）
     * @throws ApiException
     */
    public function downloadCloudApp($moduleName, $key = null, $token = null): bool
    {
        $appPath = IA_ROOT . "/app/" . $moduleName;
        FileUtil::mkDirs($appPath);

        $postData = [
            'key'        => $key,
            'token'      => $token,
            'identifier' => $moduleName
        ];
        $postData['host_ip'] = $_SERVER['REMOTE_ADDR'];
        $postData['host_url'] = $_SERVER['HTTP_HOST'];
        $postData['version'] = $postData['version'] ?? IMS_VERSION;
        $postData['php_version'] = $postData['php_version'] ?? PHP_VERSION;
        $urlParams = http_build_query($postData);
        $url = "https://www.xsframe.cn/cloud/api/app/download?" . $urlParams; // 替换为你的服务器上的下载脚本URL
        $outputFile = IA_ROOT . "/app/{$moduleName}/$moduleName.zip"; // 替换为你想要保存文件的路径

        $totalSize = 0; // 总文件大小，可以从服务器响应的Content-Length或Content-Range头部获取（如果需要的话）
        $downloadedSize = 0; // 已下载的文件大小
        $chunkSize = 1024 * 1024; // 每次下载的块大小（1MB）

        // 获取文件总大小（可选，但有助于显示下载进度）
        $headers = get_headers($url, 1);

        if (isset($headers['Content-Length'])) {
            $totalSize = intval($headers['Content-Length'][0]);
        } else if (isset($headers['Content-Range'])) {
            [, $rangeInfo] = explode(' ', $headers['Content-Range'][0]);
            [, $totalSize] = explode('-', $rangeInfo);
            $totalSize = intval($totalSize);
        }

        // 分段下载文件
        while ($downloadedSize < $totalSize || $totalSize === 0) {
            $start = $downloadedSize;
            $end = min($start + $chunkSize - 1, $totalSize - 1);
            $range = "bytes=$start-$end";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RANGE, $range);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2000);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_URL, $url);

            $data = curl_exec($ch);
            curl_close($ch);

            if ($data === false) {
                throw new ApiException("下载失败：" . curl_error($ch));
            }

            $filePointer = fopen($outputFile, 'ab');
            fwrite($filePointer, $data);
            fclose($filePointer);

            $downloadedSize += strlen($data);

            // 显示下载进度（可选）
            // echo "已下载：$downloadedSize / $totalSize 字节\n";

            // 如果总大小未知，可以在这里尝试从服务器响应中更新它（如果需要的话）
            // ...

            // 暂停一段时间以避免对服务器造成过大压力（可选）
            // usleep(100000); // 100毫秒
        }

        $zip = new \ZipArchive();
        if ($zip->open($outputFile) === true) {
            $zip->extractTo($appPath);
            $zip->close();
        }

        return true;
    }

    /**
     * 下载云应用（整包下载）
     * @throws ApiException
     */
    public function downloadCloudApp2($moduleName, $key = null, $token = null): bool
    {
        $postData = ['key' => $key, 'token' => $token, 'identifier' => $moduleName];
        $response = RequestUtil::cloudHttpPost("app/download", $postData);

        if (!empty($response)) {
            if (is_array($response)) {
                throw new ApiException($response['msg'] ?? "通信失败");
            } else {
                $tmpFile = tempnam(sys_get_temp_dir(), 'zip_');
                @file_put_contents($tmpFile, $response);

                $appPath = IA_ROOT . "/app/" . $moduleName;
                FileUtil::mkDirs($appPath);

                $zip = new \ZipArchive();
                if ($zip->open($tmpFile) === true) {
                    $zip->extractTo($appPath);
                    $zip->close();
                }

                @unlink($tmpFile);
                return true;
            }
        }

        return false;
    }

    /**
     * 下载云框架补丁包
     * @throws ApiException
     */
    public function downloadCloudFrame($version, $type = 0, $key = null, $token = null): bool
    {
        $postData = ['key' => $key, 'token' => $token, 'version' => $version, 'type' => $type];
        $response = RequestUtil::cloudHttpPost("frame/download", $postData);
        if (!empty($response)) {
            if (is_array($response)) {
                throw new ApiException($response['msg'] ?? "通信失败");
            } else {
                $tmpFile = tempnam(sys_get_temp_dir(), 'zip_');
                @file_put_contents($tmpFile, $response);

                $framePath = IA_ROOT;
                FileUtil::mkDirs($framePath);

                $zip = new \ZipArchive();
                if ($zip->open($tmpFile) === true) {
                    $zip->extractTo($framePath);
                    $zip->close();
                }

                @unlink($tmpFile);
                return true;
            }
        }

        return false;
    }
}