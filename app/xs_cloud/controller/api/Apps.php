<?php

namespace app\xs_cloud\controller\api;

use app\xs_cloud\facade\service\AppLogServiceFacade;
use app\xs_cloud\facade\service\AppServiceFacade;
use app\xs_cloud\facade\service\MemberAppServiceFacade;

class Apps extends Base
{

    // 获取app列表
    public function getAppList(): \think\response\Json
    {
        $memberAppList = MemberAppServiceFacade::getAll(['mid' => $this->memberInfo['id'], ['endtime', '>=', TIMESTAMP]], "identifier", "id desc", "identifier");
        foreach ($memberAppList as $identifier => $memberApp) {
            if ($memberApp['endtime'] > 0 && $memberApp['endtime'] < TIMESTAMP) {
                unset($memberAppList[$identifier]);
            }
        }

        $memberAppListKeys = array_keys($memberAppList);
        $appList = AppServiceFacade::getAll(['identifier' => $memberAppListKeys, 'status' => 1, 'deleted' => 0]);

        foreach ($appList as &$item) {
            $item['logo'] = tomedia($item['logo']);
            unset($item['id']);
        }

        $result = [
            'appList' => $appList,
        ];

        return $this->success($result);
    }

    // 下载应用
    public function downloadModule()
    {
        $identifier = $this->params['identifier'] ?? '';

        $version = AppServiceFacade::getValue(['identifier' => $identifier], "version");

        $filePath = IA_ROOT . "/storage/releases/apps/{$identifier}/{$version}/";
        $filename = "{$identifier}.zip";
        $zipFile = $filePath . $filename;

        if (!file_exists($zipFile)) {
            $this->error("应用文件不存在");
        }

        AppLogServiceFacade::insertInfo([
            'mid'         => $this->memberInfo['id'] ?? 0,
            'identifier'  => $identifier,
            'host_url'    => $this->hostUrl,
            'host_ip'     => $this->hostIp,
            'createtime'  => time(),
            'version'     => $version,
            'php_version' => $this->phpVersion
        ]);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
        header('Content-Length: ' . filesize($zipFile));

        @readfile($zipFile);
        exit;
    }
}
