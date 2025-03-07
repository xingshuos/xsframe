<?php

namespace app\admin\controller;

use think\Exception;
use think\facade\Cache;
use think\facade\Db;
use xsframe\enum\CacheKeyEnum;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\exception\ApiException;
use xsframe\util\FileUtil;
use xsframe\util\RequestUtil;
use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\AttachmentWrapper;
use xsframe\wrapper\CloudWrapper;

class Sysset extends Base
{
    public function index()
    {
        return redirect('/admin/sysset/site');
    }

    // 附件设置
    public function attachment()
    {
        $post_max_size = ini_get('post_max_size');
        $post_max_size = $post_max_size > 0 ? byteCount($post_max_size) / 1024 : 0;
        $upload_max_filesize = ini_get('upload_max_filesize');

        $attachmentPath = IA_ROOT . "/public/attachment/";

        if ($this->request->isPost()) {
            $type = $this->params['type'];
            $uniacid = $this->params['uniacid'] ?? 0;

            $attachmentController = new AttachmentWrapper();

            switch ($type) {
                case 'alioss':
                    $attachmentController->aliOss($this->params['key'], $this->params['secret'], $this->params['url'], $this->params['bucket']);
                    show_json(1);
                    break;
                case 'qiniu':
                    $attachmentController->qiNiu($this->params['accesskey'], $this->params['secretkey'], $this->params['bucket']);
                    show_json(1);
                    break;
                case 'cos':
                    $attachmentController->cos($this->params['appid'], $this->params['secretid'], $this->params['secretkey'], $this->params['bucket'], $this->params['local']);
                    show_json(1);
                    break;
                case 'buckets':
                    $ret = $attachmentController->buckets($this->params['key'], $this->params['secret']);
                    show_json(1, ['data' => $ret]);
                    break;
                case 'upload_remote':
                    if (empty($uniacid)) {
                        $setting = $this->settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);
                    } else {
                        $setting = $this->settingsController->getAccountSettings($uniacid, 'settings');
                    }
                    $attachmentController->fileDirRemoteUpload($setting, $attachmentPath, $attachmentPath . 'images' . ($uniacid > 0 ? '/' . $uniacid : ''));
                    show_json(1, "上传成功");
                    break;
            }

            $data = $this->params['data'];
            $this->settingsController->setSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY, $data);
            show_json(1, ["url" => url("sysset/attachment", ['tab' => str_replace("#tab_", "", $this->params['tab'])])]);
        }

        $localAttachment = FileUtil::fileDirExistImage($attachmentPath . 'images');

        $accountSettings = $this->settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);

        $result = [
            'post_max_size'       => $post_max_size,
            'upload_max_filesize' => $upload_max_filesize,
            'accountSettings'     => $accountSettings,
            'local_attachment'    => $localAttachment,
            'uniacid'             => 0,
            'postUrl'             => strval(url('sysset/attachment')),
        ];
        return $this->template('attachment', $result);
    }

    // 网站设置
    public function site()
    {
        $websiteSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::WEBSITE_KEY);

        if ($this->request->isPost()) {
            $data = $this->params['data'];
            $data['logo'] = tomedia($data['logo']);
            $data['copyright'] = htmlspecialchars_decode($this->params['data_copyright']);

            $websiteSetsData = array_merge($websiteSets, $data);

            $this->settingsController->setSysSettings(SysSettingsKeyEnum::WEBSITE_KEY, $websiteSetsData);
            show_json(1, ['url' => url('sysset/site')]);
        }

        $list = Db::name('sys_account')->where(['deleted' => 0])->order('uniacid desc')->select();

        $result = [
            'data'        => $websiteSets,
            'list'        => $list,
            'ip'          => $this->ip,
            'version'     => IMS_VERSION,
            'versionTime' => IMS_VERSION_TIME,
        ];
        return $this->template('site', $result);
    }

    // 通信设置
    public function communication(): \think\response\View
    {
        $websiteSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::WEBSITE_KEY);

        if ($this->request->isPost()) {
            $data = $this->params['data'];

            // 监测通信状态是否成功 communication_status
            $key = $data['key'] ?? '';
            $token = $data['token'] ?? '';
            $ret = RequestUtil::cloudHttpPost("frame/checkVersion", ['key' => $key, 'token' => $token]);
            if ($ret['code'] == 404) {
                $this->error($ret['msg']);
            }
            $data['communication_status'] = 1;
            $websiteSetsData = array_merge($websiteSets, $data);

            $this->settingsController->setSysSettings(SysSettingsKeyEnum::WEBSITE_KEY, $websiteSetsData);
            show_json(1, ['url' => url('sysset/communication')]);
        }

        $websiteSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::WEBSITE_KEY);

        $result = [
            'data' => $websiteSets,
        ];
        return $this->template('communication', $result);
    }

    // 表单
    public function form()
    {
        return $this->template('form');
    }

    // 模态框
    public function model()
    {
        return $this->template('model');
    }

    // 创建静态资源
    public function static()
    {
        $name = '静态资源';
        $this->buildHtml('static', $this->iaRoot . '/app/admin/view/sysset/', $this->iaRoot . '/app/admin/view/tpl/static_tpl.html', ['name' => $name]);
        return $this->template('static');
    }


    // 监测更新
    public function checkVersion()
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $result = RequestUtil::cloudHttpPost("frame/checkVersion", ['key' => $key, 'token' => $token]);
        $isUpgrade = $result['data']['isUpgrade'];

        $result = [
            'isUpgrade' => (bool)$isUpgrade
        ];
        $this->success($result);
    }

    /**
     * 系统升级
     * @throws ApiException
     */
    public function upgrade(): \think\response\View
    {
        $isCheckUpdate = $this->params['is_update'] ?? 0;
        $upgradeList = $this->getUpgradeList($isCheckUpdate);
        $updateFiles = $this->getUpdateFiles($upgradeList[0], $isCheckUpdate);
        $updateType = $this->params['update_type'] ?? 0;

        if ($this->request->isPost()) {
            if (empty($isCheckUpdate) && !empty($updateFiles)) {
                if ($updateType == 1) {
                    $this->doZipUpgrade();
                } else {
                    $this->doUpgradeFiles($updateFiles);
                }
            }
            show_json(1, ["url" => url("sysset/upgrade", ['tab' => str_replace("#tab_", "", $this->params['tab'])])]);
        }

        $result = [
            'upgradeList' => $upgradeList,
            'updateFiles' => $updateFiles,
            'version'     => IMS_VERSION,
            'versionTime' => date('Y-m-d H:i:s', IMS_VERSION_TIME),
        ];
        return $this->template('upgrade', $result);
    }

    // 显示升级内容
    public function versionContent()
    {
        $version = $this->params['version'] ?? '';
        $content = $this->params['content'] ?? '';

        $result = [
            'version' => $version,
            'content' => $content,
        ];
        return $this->template('version', $result);
    }

    // 更新完毕
    private function upgradeSuccess($version, $updateTime = null): void
    {
        isetcookie('isUpgradeSystemNotice', 0);
    }

    // 执行zip包升级
    private function doZipUpgrade()
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';
        $version = $this->params['version'] ?? '';
        $type = $this->params['type'] ?? 0;

        $cloudWrapper = new CloudWrapper();
        try {
            $cloudWrapper->downloadCloudFrame($version, $type, $key, $token);
            return true;
        } catch (ApiException $e) {
            throw new ApiException("升级失败，请检查文件夹权限！");
        }
    }

    // 执行文件升级
    private function doUpgradeFiles($updateFiles)
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $version = IMS_VERSION;
        $versionTime = IMS_VERSION_TIME;

        $isSuccess = true;
        $unSuccessFiles = [];
        if (!empty($updateFiles)) {
            foreach ($updateFiles as $filePath) {
                $file_dir = dirname(IA_ROOT . $filePath);
                if (!is_dir($file_dir)) {
                    @mkdir($file_dir, 0777, true);
                }

                $result = RequestUtil::cloudHttpPost("frame/upgradeFileData", ['key' => $key, 'token' => $token, 'file_path' => $filePath]);

                if (empty($result) || $result['code'] != 200) {
                    continue;
                } else {
                    $version = $result['data']['version'];
                    $versionTime = $result['data']['updatetime'];
                    $fileData = $result['data']['fileData'];
                    $fileType = substr(strrchr($filePath, '.'), 1);

                    if (in_array($fileType, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'ico', 'heic'])) {
                        $fileData = base64_decode($fileData);
                    }

                    try {
                        file_put_contents(IA_ROOT . $filePath, $fileData, FILE_BINARY);
                    } catch (Exception $exception) {
                        $unSuccessFiles[] = $filePath;
                        $isSuccess = false;
                    }

                }
            }
        }

        if (!$isSuccess) {
            $msg = "";
            foreach ($unSuccessFiles as $filePath) {
                $msg .= $filePath . "<br>";
            }

            Cache::set(CacheKeyEnum::CLOUD_FRAME_UPGRADE_FILES_KEY, $unSuccessFiles, 7200);

            show_json(1, "部分文件更新失败<br>{$msg}请设置根目录权限为777");
        } else {
            Cache::delete(CacheKeyEnum::CLOUD_FRAME_UPGRADE_FILES_KEY);
        }

        $this->upgradeSuccess($version, $versionTime);
    }

    // 获取升级日志列表
    private function getUpgradeList($isCheckUpdate = null)
    {
        // 获取更新日志 start
        $upgradeList = Cache::get(CacheKeyEnum::CLOUD_FRAME_UPGRADE_LIST_KEY);

        if (empty($upgradeList) || !empty($isCheckUpdate)) {
            $key = $this->websiteSets['key'] ?? '';
            $token = $this->websiteSets['token'] ?? '';
            $result = RequestUtil::cloudHttpPost("frame/upgrade", ['key' => $key, 'token' => $token]);
            if (empty($result) || $result['code'] != 200) {
                if ($this->request->isPost()) {
                    $this->error($result['msg']);
                }
                $upgradeList = [];
            } else {
                $upgradeList = $result['data']['list'];
            }
            Cache::set(CacheKeyEnum::CLOUD_FRAME_UPGRADE_LIST_KEY, $upgradeList, 7200);
        }
        // 获取更新日志 end
        return $upgradeList;
    }

    // 获取待更新文件列表
    private function getUpdateFiles($upgradeInfo = null, $isCheckUpdate = null)
    {
        # 版本对比是否存在最新版本 start
        $updateFiles = Cache::get(CacheKeyEnum::CLOUD_FRAME_UPGRADE_FILES_KEY);

        if ((empty($updateFiles) && (!empty($upgradeInfo) && version_compare($upgradeInfo['version'], IMS_VERSION, '>'))) || !empty($isCheckUpdate)) {

            $key = $this->websiteSets['key'] ?? '';
            $token = $this->websiteSets['token'] ?? '';
            $result = RequestUtil::cloudHttpPost("frame/upgradeFiles", ['key' => $key, 'token' => $token]);

            if (empty($result) || $result['code'] != 200) {
                $this->error($result['msg']);
            } else {
                $files = json_decode($result['data']['upgradeFiles'], true);

                if (!empty($files)) {
                    $updateFiles = [];
                    foreach ($files as $file) {
                        $entry = IA_ROOT . $file['path'];

                        if (!is_file($entry) || md5_file($entry) != $file['checksum']) {
                            $updateFiles[] = $file['path'];
                        }
                    }
                    unset($file);
                }
            }

            Cache::set(CacheKeyEnum::CLOUD_FRAME_UPGRADE_FILES_KEY, $updateFiles, 7200);
        }
        # 版本对比是否存在最新版本 end

        return $updateFiles;
    }

}
