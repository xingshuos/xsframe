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
                    $attachmentController->buckets($this->params['key'], $this->params['secret']);
                    show_json(1);
                    break;
                case 'upload_remote':
                    $setting = $this->settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);
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
        ];
        return $this->template('attachment', $result);
    }

    // 网站设置
    public function site()
    {
        if ($this->request->isPost()) {
            $data = $this->params['data'];
            $data['logo'] = tomedia($data['logo']);
            $data['copyright'] = htmlspecialchars_decode($this->params['data_copyright']);
            $this->settingsController->setSysSettings(SysSettingsKeyEnum::WEBSITE_KEY, $data);
            show_json(1, ['url' => url('sysset/site')]);
        }

        $websiteSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::WEBSITE_KEY);
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

    // 域名设置
    public function host()
    {
        $keyword = $this->params['keyword'] ?? '';
        $uniacid = $this->params['uniacid'] ?? 0;

        $condition = [];

        if (!empty($uniacid)) {
            $condition['uniacid'] = $uniacid;
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" `host_url` like '%" . trim($keyword) . "%' ");
        }

        $list = Db::name("sys_account_host")->where($condition)->order('displayorder desc,id asc')->page($this->pIndex, $this->pSize)->select()->toArray();
        $total = Db::name("sys_account_host")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $accountList = Db::name('sys_account')->where(['deleted' => 0])->order("displayorder desc")->select()->toArray();

        foreach ($list as &$item) {
            $item['account'] = Db::name('sys_account')->where(['uniacid' => $item['uniacid']])->find();
            $item['module'] = Db::name('sys_modules')->where(['identifie' => $item['default_module']])->find();
        }
        unset($item);

        $result = [
            'list'        => $list,
            'accountList' => $accountList,
            'pager'       => $pager,
            'total'       => $total,
        ];

        return $this->template('host', $result);
    }

    // 编辑
    public function hostEdit()
    {
        $id = $this->params['id'];

        if ($this->request->isPost()) {

            $data = [
                "uniacid"        => trim($this->params["uniacid"]),
                "host_url"       => trim($this->params["host_url"]),
                "default_module" => trim($this->params["default_module"] ?? ''),
                "default_url"    => trim($this->params["default_url"]),
                "displayorder"   => trim($this->params["displayorder"]),
            ];

            if (empty($data['default_module'])) {
                $this->error("请选择默认应用");
            }

            if (!empty($id)) {
                Db::name("sys_account_host")->where(["id" => $id])->update($data);
            } else {
                Db::name("sys_account_host")->insert($data);
            }

            $accountHost = new AccountHostWrapper();
            $accountHost->reloadAccountHost();

            $this->success(["url" => webUrl("admin/sysset/host")]);
        }

        $item = Db::name("sys_account_host")->where(['id' => $id])->find();

        $accountList = Db::name('sys_account')->where(['deleted' => 0])->order("displayorder desc")->select()->toArray();

        $modules = Db::name('sys_modules')->where(['identifie' => $item['default_module']])->select()->toArray();

        foreach ($modules as &$module) {
            $module['logo'] = !empty($module['logo']) ? tomedia($module['logo']) : $this->siteRoot . "/app/{$module['identifie']}/icon.png";
        }

        return $this->template('host', ['item' => $item, 'accountList' => $accountList, 'modules' => $modules]);
    }

    public function hostDelete()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            $this->error("参数错误");
        }

        $items = Db::name('sys_account_host')->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name('sys_account_host')->where(["id" => $item['id']])->delete();
        }

        $accountHost = new AccountHostWrapper();
        $accountHost->reloadAccountHost();

        $this->success(["url" => referer()]);
    }

    // 更新域名
    public function hostChange()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            $this->error("参数错误");
        }

        $type = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $items = Db::name("sys_account_host")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_account_host")->where("id", '=', $item['id'])->update([$type => $value]);
        }

        $accountHost = new AccountHostWrapper();
        $accountHost->reloadAccountHost();

        $this->success();
    }

    // 图标
    public function icon()
    {
        return $this->template('icon');
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

    // 检测bom
    public function bom()
    {
        $bomTree = Cache::get('bomTree');

        if ($this->request->isPost()) {
            $path = $this->iaRoot;
            $trees = FileUtil::fileTree($path);
            $bomTree = [];
            foreach ($trees as $tree) {
                $tree = str_replace($path, '', $tree);
                $tree = str_replace('\\', '/', $tree);
                if (strexists($tree, '.php')) {
                    $fname = $path . $tree;
                    $fp = fopen($fname, 'r');
                    if (!empty($fp)) {
                        $bom = fread($fp, 3);
                        fclose($fp);
                        if ($bom == "\xEF\xBB\xBF") {
                            $bomTree[] = $tree;
                        }
                    }
                }
            }
            Cache::set('bomTree', $bomTree);
            show_json(1, ['url' => url('sysset/bom')]);
        }

        $result = [
            'bomTree' => $bomTree
        ];
        return $this->template('bom', $result);
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

        if ($this->request->isPost() && !empty($updateFiles)) {
            if (empty($isCheckUpdate)) {
                // $this->doUpgradeFiles($updateFiles);
                $this->doZipUpgrade();
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
