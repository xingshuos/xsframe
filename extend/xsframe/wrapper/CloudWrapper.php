<?php


namespace xsframe\wrapper;

use xsframe\util\FileUtil;
use xsframe\util\RequestUtil;

class CloudWrapper
{
    // 下载云应用
    public function downloadCloudApp($moduleName, $key = null, $token = null): bool
    {
        $postData = ['key' => $key, 'token' => $token, 'identifier' => $moduleName];

        $postData['host_ip'] = $_SERVER['REMOTE_ADDR'];
        $postData['host_url'] = $_SERVER['HTTP_HOST'];
        $postData['version'] = IMS_VERSION;
        $postData['php_version'] = PHP_VERSION;

        $response = RequestUtil::httpPost("https://www.xsframe.cn/cloud/api/app/download", $postData);

        if (!empty($response)) {
            $result = @json_decode($response, true);
            if (!empty($result) && $result['code'] != 200) {
                return false;
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