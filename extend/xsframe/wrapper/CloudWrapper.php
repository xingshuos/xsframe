<?php


namespace xsframe\wrapper;

use xsframe\util\FileUtil;
use xsframe\util\RequestUtil;

class CloudWrapper
{
    // 下载云应用
    public function downloadCloudApp($moduleName, $key = null, $token = null): bool
    {
        $response = RequestUtil::httpPost("https://www.xsframe.cn/cloud/api/app/download", array('key' => $key, 'token' => $token, 'identifier' => $moduleName));

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
                if ($zip->open($tmpFile) === TRUE) {
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