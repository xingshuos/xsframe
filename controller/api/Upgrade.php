<?php

namespace app\xs_cloud\controller\api;

use app\xs_cloud\facade\service\FrameVersionServiceFacade;
use xsframe\base\ApiBaseController;

class Upgrade extends ApiBaseController
{
    public function getUpgradeList(): \think\response\Json
    {
        $upgradeList = FrameVersionServiceFacade::getList(['status' => 1, 'deleted' => 0], "version,title,updatetime", "id desc");

        $result = [
            'list' => $upgradeList,
        ];

        return $this->success($result);
    }

    public function getUpgradeFiles(): \think\response\Json
    {
        $upgradeInfo = FrameVersionServiceFacade::getInfo(['status' => 1, 'deleted' => 0], "version,title,updatetime", "id desc");

        $upgradeLogFile = IA_ROOT . "/storage/releases/frames/" . $upgradeInfo['version'] . "/upgrade.log";

        $upgradeFiles = [];
        if (is_file($upgradeLogFile)) {
            $file = fopen($upgradeLogFile, "r");
            $upgradeFiles = fgets($file);
        }

        $result = [
            'version'      => $upgradeInfo['version'],
            'upgradeFiles' => $upgradeFiles,
        ];

        return $this->success($result);
    }

    public function getUpgradeFileData(): \think\response\Json
    {
        $filePath = $this->params['file_path'] ?? '';
        $fileType = substr(strrchr($filePath, '.'), 1);

        $upgradeInfo = FrameVersionServiceFacade::getInfo(['status' => 1, 'deleted' => 0], "version,title,updatetime", "id desc");
        $upgradeFile = IA_ROOT . "/storage/releases/frames/" . $upgradeInfo['version'] . "/{$filePath}";

        $fileData = "";
        if (is_file($upgradeFile)) {
            $fileData = file_get_contents($upgradeFile);
            if ($fileData !== false) { // 假设是图片数据
                if (in_array($fileType, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'ico', 'heic'])) {
                    $fileData = base64_encode($fileData);
                }
            }
        }

        $result = [
            'version'  => $upgradeInfo['version'],
            'fileData' => $fileData,
        ];

        return $this->success($result);
    }


}
