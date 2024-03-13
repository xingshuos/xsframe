<?php

namespace app\xs_cloud\controller\api;

use app\xs_cloud\facade\service\FrameLogServiceFacade;
use app\xs_cloud\facade\service\FrameVersionServiceFacade;
use app\xs_cloud\facade\service\MemberServiceFacade;
use app\xs_cloud\facade\service\MemberSiteServiceFacade;
use xsframe\base\ApiBaseController;

class Upgrade extends ApiBaseController
{
    private $memberInfo = null;

    public function _api_initialize()
    {
        parent::_api_initialize();

        $this->checkToken();
    }

    // 监测站点信息
    private function checkToken()
    {
        $key = $this->params['key']; // 站点ID
        $token = $this->params['token']; // 通信秘钥

        if (empty($key) || empty($token)) {
            return $this->error("请查看站点设置中“站点ID”和“通信秘钥”是否配置");
        }

        $memberInfo = MemberServiceFacade::getInfo(['token' => $token]);
        if (empty($memberInfo)) {
            return $this->error("当前站点不存在或已暂停服务,请联系官方客服处理");
        }
        if ($memberInfo['status'] == 0) {
            return $this->error("您的站点已暂停服务,请联系官方客服处理");
        }
        if ($memberInfo['is_black'] == 1) {
            return $this->error("您的站点已被系统列入黑名单,请联系官方客服处理");
        }
        $memberSiteInfo = MemberSiteServiceFacade::getInfo(['key' => $key]);
        if (empty($memberSiteInfo) || $memberSiteInfo['mid'] != $memberInfo['id']) {
            return $this->error("请查看站点设置中“站点ID”和“通信秘钥”是否配置正确");
        }
        $this->memberInfo = $memberInfo;
    }

    // 是否存在新版本
    public function checkUpgradeVersion(): \think\response\Json
    {
        $version = $this->params['version'] ?? '';

        $result = [
            'isUpgrade' => version_compare(IMS_VERSION, $version, ">"),
        ];

        return $this->success($result);
    }

    // 获取更新日志
    public function getUpgradeList(): \think\response\Json
    {
        $upgradeList = FrameVersionServiceFacade::getList(['status' => 1, 'deleted' => 0], "version,title,updatetime,content", "id desc");

        $result = [
            'list' => $upgradeList,
        ];

        return $this->success($result);
    }

    // 获取更新文件
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

    // 获取更新数据流
    public function getUpgradeFileData(): \think\response\Json
    {
        $hostIp = $this->params['host_ip'] ?? '';
        $hostUrl = $this->params['host_url'] ?? '';
        $filePath = $this->params['file_path'] ?? '';
        $phpVersion = $this->params['php_version'] ?? '';
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

        FrameLogServiceFacade::insertInfo(['mid' => $this->memberInfo['mid'], 'host_url' => $hostUrl, 'host_ip' => $hostIp, 'createtime' => time(), 'version' => $upgradeInfo['version'], 'php_version' => $phpVersion]);

        $result = [
            'version'  => $upgradeInfo['version'],
            'fileData' => $fileData,
        ];

        return $this->success($result);
    }

}
