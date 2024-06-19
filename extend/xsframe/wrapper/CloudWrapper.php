<?php


namespace xsframe\wrapper;

use xsframe\exception\ApiException;
use xsframe\util\FileUtil;
use xsframe\util\RequestUtil;

class CloudWrapper
{
    // 下载云应用
    public function downloadCloudApp($moduleName, $key = null, $token = null): bool
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
}