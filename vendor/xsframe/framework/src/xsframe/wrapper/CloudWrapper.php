<?php


namespace xsframe\wrapper;

use xsframe\exception\ApiException;
use xsframe\util\FileUtil;
use xsframe\util\RequestUtil;

class CloudWrapper
{

    /**
     * 下载云应用（改为分段式下载）
     * @throws ApiException
     */
    public function downloadCloudApp($moduleName, $key = null, $token = null): bool
    {
        $appPath = IA_ROOT . "/app/" . $moduleName;
        FileUtil::mkDirs($appPath);

        $outputFile = IA_ROOT . "/app/{$moduleName}/$moduleName.zip"; // 替换为你想要保存文件的路径
        $totalSize = 0; // 总文件大小，可以从服务器响应的Content-Length或Content-Range头部获取（如果需要的话）
        $downloadedSize = 0; // 已下载的文件大小
        $chunkSize = 1024 * 1024; // 每次下载的块大小（1MB）

        // 分段下载文件
        $postData = ['key' => $key, 'token' => $token, 'identifier' => $moduleName];
        while ($downloadedSize < $totalSize || $totalSize === 0) {
            $start = $downloadedSize;
            $end = min($start + $chunkSize - 1, $totalSize - 1);
            $range = "bytes=$start-$end";

            $extra = [
                'CURLOPT_HEADER' => false,
                'CURLOPT_RANGE'  => $range,
            ];
            $response = RequestUtil::cloudHttpPost("app/download", $postData, $extra);

            if (is_array($response)) {
                throw new ApiException($response['msg'] ?? "通信失败");
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

        @unlink($outputFile);
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