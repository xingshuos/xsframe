<?php

namespace app\admin\controller;

use app\admin\enum\CacheKeyEnum;
use think\Exception;
use xsframe\util\RequestUtil;
use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\AttachmentWrapper;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\util\FileUtil;
use think\facade\Cache;
use think\facade\Db;

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
            # 测试配置
            $attachmentController = new AttachmentWrapper();

            $ret = [];
            switch ($type) {
                case 'alioss':
                    $attachmentController->aliOss($this->params['key'], $this->params['secret'], $this->params['url'], $this->params['bucket']);
                    show_json(1);
                    break;
                case 'qiniu':
                    $attachmentController->qiNiu();
                    break;
                case 'cos':
                    $attachmentController->cos();
                    break;
                case 'buckets':
                    $ret = $attachmentController->buckets($this->params['key'], $this->params['secret']);
                    show_json(1, ['data' => $ret]);
                    break;
                case 'upload_remote':
                    $setting = $this->settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);
                    $attachmentController->fileDirRemoteUpload($setting, $attachmentPath, $attachmentPath . 'images');
                    show_json(1, "上传成功");
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
        ];
        return $this->template('attachment', $result);
    }

    // 网站设置
    public function site()
    {
        if ($this->request->isPost()) {
            $data = $this->params['data'];
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

            $data = array(
                "uniacid"        => trim($this->params["uniacid"]),
                "host_url"       => trim($this->params["host_url"]),
                "default_module" => trim($this->params["default_module"] ?? ''),
                "default_url"    => trim($this->params["default_url"]),
                "displayorder"   => trim($this->params["displayorder"]),
            );

            if (empty($data['default_module'])) {
                $this->error("请选择默认应用");
            }

            if (!empty($id)) {
                Db::name("sys_account_host")->where(["id" => $id])->update($data);
            } else {
                Db::name("sys_account_host")->insert($data);
            }

            $accountHostWrapper = new AccountHostWrapper();
            $accountHostWrapper->setAccountHost();

            $this->success(array("url" => webUrl("admin/sysset/host")));
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
        $this->success(array("url" => referer()));
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
            $bomTree = array();
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
        $response = RequestUtil::httpPost("https://www.xsframe.cn/cloud/api/frame/checkVersion", array('key' => $key, 'token' => $token));
        $result = json_decode($response, true);
        $isUpgrade = $result['data']['isUpgrade'];

        $result = [
            'isUpgrade' => $isUpgrade
        ];
        return $this->success($result);
    }

    // 系统升级
    public function upgrade(): \think\response\View
    {
        $isCheckUpdate = $this->params['is_update'] ?? 0;
        $upgradeList = $this->getUpgradeList($isCheckUpdate);
        $updateFiles = $this->getUpdateFiles($upgradeList[0], $isCheckUpdate);

        if ($this->request->isPost()) {
            if (empty($isCheckUpdate)) {
                $this->doUpgradeFiles($updateFiles);
            }
            show_json(1, ["url" => url("sysset/upgrade", ['tab' => str_replace("#tab_", "", $this->params['tab'])])]);
        }

        $result = [
            'upgradeList' => $upgradeList,
            'updateFiles' => $updateFiles,
            'version'     => IMS_VERSION,
            'versionTime' => IMS_VERSION_TIME,
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
    private function upgradeSuccess($version, $updateTime = null): bool
    {
        $tpl = <<<EOF
<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

define('IMS_VERSION', '{$version}');
define('IMS_VERSION_TIME', '{$updateTime}');

EOF;
        isetcookie('isUpgradeSystemNotice', 0);
        file_put_contents(IA_ROOT . "/public/version.php", $tpl);
        return true;
    }

    // 执行文件升级
    private function doUpgradeFiles($updateFiles)
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $version = IMS_VERSION;
        $versionTime = IMS_VERSION_TIME;

        $isSuccess = true;
        foreach ($updateFiles as $filePath) {
            $file_dir = dirname(IA_ROOT . $filePath);
            if (!is_dir($file_dir)) {
                mkdir($file_dir, 0777, true);
            }

            $response = RequestUtil::httpPost("https://www.xsframe.cn/cloud/api/frame/upgradeFileData", array('key' => $key, 'token' => $token, 'file_path' => $filePath));
            $result = json_decode($response, true);

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
                    file_put_contents(IA_ROOT . $filePath, $fileData);
                } catch (Exception $exception) {
                    $isSuccess = false;
                }

            }

            Cache::delete(CacheKeyEnum::CLOUD_FRAME_UPGRADE_FILES_KEY);
        }

        if (!$isSuccess) {
            show_json(0, "有部分文件更新失败，请设置根目录权限为777");
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
            $response = RequestUtil::httpPost("https://www.xsframe.cn/cloud/api/frame/upgrade", ['key' => $key, 'token' => $token]);

            $result = json_decode($response, true);

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
            $response = RequestUtil::httpPost("https://www.xsframe.cn/cloud/api/frame/upgradeFiles", ['key' => $key, 'token' => $token]);
            $result = json_decode($response, true);

            if (empty($result) || $result['code'] != 200) {
                $this->error($result['msg']);
            } else {
                $files = json_decode($result['data']['upgradeFiles'], true);

                if (!empty($files)) {
                    $updateFiles = array();
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
