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

namespace app\admin\controller;

use xsframe\base\AdminBaseController;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\facade\wrapper\SystemWrapperFacade;
use xsframe\util\ArrayUtil;
use xsframe\util\FileUtil;
use xsframe\util\RandomUtil;
use xsframe\util\StringUtil;
use xsframe\wrapper\AttachmentWrapper;
use think\facade\Db;
use xsframe\wrapper\UserWrapper;

class System extends AdminBaseController
{
    protected $uniacid;

    // 应用列表
    public function index()
    {
        $this->pSize = 20;
        $condition = [
            'am.uniacid' => $this->uniacid,
            'am.deleted' => 0
        ];

        $field = "am.id,am.module,am.settings," . "m.name,m.identifie,m.logo,m.ability";
        $list = Db::name('sys_account_modules')->alias('am')->field($field)->leftJoin("sys_modules m", "m.identifie = am.module")->where($condition)->order("am.displayorder asc")->page($this->pIndex, $this->pSize)->select()->toArray();
        $total = Db::name('sys_account_modules')->alias('am')->field($field)->leftJoin("sys_modules m", "m.identifie = am.module")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item) {
            $settings = unserialize($item['settings']);
            if (!empty($settings)) {
                if (!empty($settings['basic'])) {
                    if (!empty($settings['basic']['logo'])) {
                        $basicLogo = tomedia($settings['basic']['logo']);
                        $item['logo'] = $basicLogo;
                        $settings['basic']['logo'] = $basicLogo;
                    }
                    if (!empty($settings['basic']['name'])) {
                        $item['name'] = $settings['basic']['name'];
                    }
                }
                $item['settings'] = $settings;
            }
            $item['logo'] = tomedia($item['logo']);

            // 获取后台访问地址
            $item['url'] = $this->getRealModuleUrl($item['module']);
        }
        $list = set_medias($list, ['logo']);

        $uid = $this->userId;
        $field = " u.uid,u.status as userstatus,r.status as rolestatus,u.perms as userperms,r.perms as roleperms,u.roleid ";
        $user = Db::name("sys_account_perm_user")->alias('u')->field($field)->leftJoin("sys_account_perm_role r", "r.id = u.roleid")->where(['u.uid' => $uid])->find();
        $role_perms = explode(',', $user['roleperms']);
        $user_perms = explode(',', $user['userperms']);
        $perms = array_merge($role_perms, $user_perms);

        $result = [
            'list'  => $list,
            'pager' => $pager,
            'total' => $total,
            'perms' => $perms,
        ];
        return $this->template('index', $result);
    }

    // 我的账号
    public function profile()
    {
        if ($this->request->isPost()) {
            $username = $this->params['username'];
            $password = $this->params['password'];
            $newPassword = $this->params['newPassword'];

            $adminSession = $this->adminSession;
            $userInfo = Db::name('sys_users')->field("id,username,password,salt")->where(['id' => $adminSession['uid']])->find();
            $password = md5($password . $userInfo['salt']);
            if (md5($password . $userInfo['salt']) != $adminSession['hash']) {
                show_json(0, "原始密码错误，请重新输入");
            }
            if (strlen($newPassword) < 6) {
                show_json(0, "请输入不小于6位数的密码");
            }
            if (empty($username)) {
                show_json(0, "登录账号不能为空");
            }
            if ($userInfo['username'] != $username) {
                show_json(0, "账号暂不允许修改");
            }
            if ($userInfo['password'] == md5($newPassword . $userInfo['salt'])) {
                show_json(0, "新密码与原密码相同无需修改");
            }

            $salt = RandomUtil::random(6);
            Db::name('sys_users')->where(['id' => $userInfo['id']])->update(['username' => $username, 'password' => md5($newPassword . $salt), 'salt' => $salt]);
            UserWrapper::logout();
            show_json(1, ['message' => "密码已修改请重新登录", 'url' => referer()]);
        }

        return $this->template('profile');
    }

    // 获取后端访问入口
    private function getRealModuleUrl($moduleName)
    {
        $realModuleName = realModuleName($moduleName);

        $moduleMenuConfigFile = IA_ROOT . "/app/" . $moduleName . "/config/menu.php";
        if (is_file($moduleMenuConfigFile)) {
            $menuConfig = include($moduleMenuConfigFile);
            $oneMenus = array_slice($menuConfig, 0, 1);
            $oneMenusKeys = array_keys($oneMenus);
            $actionUrl = $oneMenus[$oneMenusKeys[0]]['items'][0]['route'];

            if (StringUtil::strexists($actionUrl, "/")) {
                $actionUrl = "." . $actionUrl;
            } else {
                $actionUrl = "/" . $actionUrl;
            }

            if (!strexists($oneMenusKeys[0], "web.")) {
                $oneMenusKeys[0] = "web." . $oneMenusKeys[0];
            }

            $url = webUrl('/' . $realModuleName . "/{$oneMenusKeys[0]}{$actionUrl}", ['i' => $this->uniacid]);
        } else {
            $url = webUrl('/' . $realModuleName . "/web.index", ['i' => $this->uniacid]);
        }

        return $url;
    }

    // 基本信息
    public function account()
    {
        $uniacid = $this->uniacid;
        $accountSettings = $this->settingsController->getAccountSettings($this->uniacid, 'settings');

        if ($this->request->isPost()) {
            $settingsData = $this->params['data'] ?? [];

            if (!empty($_FILES)) {
                if ($_FILES['wxpay_cert_file']['name']) {
                    $settingsData['wxpay']['cert_file'] = $this->upload_cert('wxpay_cert_file');
                }

                if ($_FILES['wxpay_key_file']['name']) {
                    $settingsData['wxpay']['key_file'] = $this->upload_cert('wxpay_key_file');
                }
            }

            $settingsData = ArrayUtil::customMergeArrays($accountSettings, $settingsData);

            if (empty($uniacid)) {
                show_json(0, "更新失败");
            }

            $data = [
                "name"         => trim($this->params["name"]),
                "logo"         => trim($this->params["logo"]),
                "keywords"     => trim($this->params["keywords"]),
                "description"  => trim($this->params["description"]),
                "copyright"    => trim($this->params["copyright"]),
                "displayorder" => intval($this->params["displayorder"]),
                "status"       => intval($this->params["status"]),
            ];
            $data['settings'] = serialize($settingsData);

            Db::name('sys_account')->where(['uniacid' => $uniacid])->update($data);

            if (!empty($settingsData)) {
                $data['settings'] = serialize($settingsData);
                Db::name('sys_account')->where(["uniacid" => $uniacid])->update($data);
                # 更新缓存
                $this->settingsController->reloadAccountSettings($uniacid);
            }

            # 分配应用
            $this->setAccountModules($uniacid);

            $this->success(["url" => webUrl("account", ['module' => $this->params['module'], 'tab' => str_replace("#tab_", "", $this->params['tab'])])]);
        }

        $attachmentPath = IA_ROOT . "/public/attachment/images/{$uniacid}";
        $localAttachment = FileUtil::fileDirExistImage($attachmentPath);

        $identifies = Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'deleted' => 0])->order('displayorder asc,id asc')->column('module');
        $hostList = Db::name('sys_account_host')->where(['uniacid' => $uniacid])->order('id asc')->select();
        $item = Db::name('sys_account')->where(['uniacid' => $uniacid])->find();

        $modules = Db::name('sys_modules')->where(['identifie' => $identifies])->orderRaw("FIELD(identifie," . "'" . implode("','", $identifies) . "'" . ")")->select()->toArray();
        foreach ($modules as &$module) {
            $module['logo'] = !empty($module['logo']) ? tomedia($module['logo']) : $this->siteRoot . "/app/{$module['identifie']}/icon.png";
        }

        $result = [
            'item'             => $item,
            'uniacid'          => $uniacid,
            'hostList'         => $hostList,
            'accountSettings'  => $accountSettings,
            'modules'          => $modules,
            'postUrl'          => strval(url('system/attachment')),
            'local_attachment' => $localAttachment,
        ];

        return $this->template('account', $result);
    }

    // 附件设置
    public function attachment()
    {
        $attachmentPath = IA_ROOT . "/public/attachment/";

        $type = $this->params['type'];

        # 测试配置
        $attachmentController = new AttachmentWrapper();

        switch ($type) {
            case 'alioss':
                $attachmentController->aliOss($this->params['key'], $this->params['secret'], $this->params['url'], $this->params['bucket']);
                show_json(1);
                break;
            case 'qiniu':
                // $attachmentController->qiNiu();
                break;
            case 'cos':
                // $attachmentController->cos();
                break;
            case 'buckets':
                $ret = $attachmentController->buckets($this->params['key'], $this->params['secret']);
                show_json(1, ['data' => $ret]);
                break;
            case 'upload_remote':
                $setting = $this->settingsController->getAccountSettings($this->uniacid, 'settings');
                $attachmentController->fileDirRemoteUpload($setting, $attachmentPath, $attachmentPath . "images/{$this->uniacid}");
                show_json(1, "上传成功");
        }

        $this->success(["url" => webUrl("account", ['tab' => str_replace("#tab_", "", $this->params['tab'])])]);
    }

    // 设置默认应用
    public function setAccountDefaultModule()
    {
        $uniacid = $this->params['uniacid'] ?? '';
        $module = $this->params['module'] ?? '';

        if (!empty($uniacid) && !empty($module)) {
            Db::name('sys_account_modules')->where(['uniacid' => $uniacid])->update(['is_default' => 0]);
            Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'module' => $module])->update(['is_default' => 1]);
        }

        show_json(1);
    }

    // 解析证书
    private function upload_cert($fileinput)
    {
        $filename = $_FILES[$fileinput]['name'];
        $tmp_name = $_FILES[$fileinput]['tmp_name'];
        if (!empty($filename) && !empty($tmp_name)) {
            $ext = strtolower(substr($filename, strrpos($filename, '.')));

            if ($ext != '.pem') {
                $errinput = '';

                if ($fileinput == 'cert_file') {
                    $errinput = 'CERT文件格式错误';
                } else if ($fileinput == 'key_file') {
                    $errinput = 'KEY文件格式错误';
                }

                show_json(0, $errinput . ',请重新上传!');
            }

            return file_get_contents($tmp_name);
        }

        return '';
    }

    // 分配应用
    private function setAccountModules($uniacid): bool
    {
        $modulesIds = $this->params['modulesids'] ?? [];
        if (!empty($modulesIds)) {
            Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'module' => Db::raw("not in ('" . implode("','", $modulesIds) . "')")])->update(['deleted' => 1]);
            foreach ($modulesIds as $key => $identifie) {
                $moduleInfo = Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'module' => $identifie])->find();
                $updateData = [
                    'uniacid'      => $uniacid,
                    'displayorder' => $key + 1,
                    'module'       => $identifie,
                    'deleted'      => 0,
                ];
                if ($moduleInfo) {
                    Db::name('sys_account_modules')->where(['id' => $moduleInfo['id']])->update($updateData);
                } else {
                    Db::name('sys_account_modules')->insert($updateData);
                }
            }
            $isDefaultModule = Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'is_default' => 1])->count();
            if (empty($isDefaultModule)) {
                Db::name('sys_account_modules')->where(['uniacid' => $uniacid])->update(['is_default' => 0]);
                Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'module' => $modulesIds[0]])->update(['is_default' => 1]);
            }
        } else {
            Db::name('sys_account_modules')->where(['uniacid' => $uniacid])->update(['deleted' => 1]);
        }

        // 更新uniacid的应用列表
        SystemWrapperFacade::reloadAccountModuleList($uniacid);

        return true;
    }

}