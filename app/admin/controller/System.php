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

use app\admin\service\AccessibleMenuService;
use xsframe\base\AdminBaseController;
use xsframe\enum\AppCategoryKeyEnum;
use xsframe\enum\AppTypesKeyEnum;
use xsframe\facade\service\DbServiceFacade;
use xsframe\facade\wrapper\SystemWrapperFacade;
use xsframe\service\ZiShuAiService;
use xsframe\util\ArrayUtil;
use xsframe\util\FileUtil;
use xsframe\util\RandomUtil;
use xsframe\wrapper\AttachmentWrapper;
use think\facade\Db;
use xsframe\wrapper\UserWrapper;

class System extends AdminBaseController
{
    protected $uniacid;

    // 应用列表
    public function index()
    {
        $result = $this->getAppList();


        return $this->template('index', $result);
    }

    // 切换应用
    public function changeApp()
    {
        $result = $this->getAppList();
        return $this->template('changeApp', $result);
    }

    private function getAppList()
    {
        $this->pSize = 72;
        $condition = [
            'am.uniacid' => $this->uniacid,
            'am.deleted' => 0,
        ];

        $field = "am.settings," . "m.*";
        $is_limit = 0;
        $moduleFilter = null;
        if ($this->adminSession['role'] == 'operator') {
            $permUserInfo = DbServiceFacade::name('sys_account_perm_user')->getInfo(['uniacid' => $this->uniacid, 'uid' => $this->userId]);
            $is_limit = (int)($permUserInfo['is_limit'] ?? 1);
            if ($is_limit == 1) {
                // 多角色合并：主角色 + 关联表所有角色 + 用户额外权限（统一走 UserPermService）
                $merged = \app\admin\service\UserPermService::getMergedPerms($this->userId, $this->uniacid);
                $all_perms = $merged['perms'];
                $all_app_perms = $merged['app_perms'];

                // 同时提取应用级权限和菜单/操作权限中的应用前缀，确保拥有具体菜单权限的应用也能显示。
                $appKeys = [];
                foreach ($all_perms as $permission) {
                    $permission = (string)$permission;
                    $webPosition = strpos($permission, '.web.');
                    if ($webPosition !== false) {
                        $appKeys[] = substr($permission, 0, $webPosition);
                    } elseif (strpos($permission, '.') === false && $permission !== '') {
                        $appKeys[] = $permission;
                    }
                }
                $appKeys = array_unique(array_merge($appKeys, array_map('strval', $all_app_perms)));

                // 没有应用级权限时使用不可能命中的模块值，避免无权限账号看到全部应用。
                $moduleFilter = $appKeys ?: ['__no_permission__'];
            }
        }

        $category = AppCategoryKeyEnum::getEnumsText();

        $listQuery = Db::name('sys_account_modules')->fetchSql(false)->alias('am')->field($field)->leftJoin("sys_modules m", "m.identifie = am.module")->where($condition);
        if ($moduleFilter !== null) {
            $listQuery->whereIn('am.module', $moduleFilter);
        }
        $list = $listQuery
            ->orderRaw("FIELD(m.type," . "'" . implode("','", array_keys($category)) . "'" . ")")->order("am.displayorder asc")->page($this->pIndex, $this->pSize)->select();
        $list = $list->toArray();
        $totalQuery = Db::name('sys_account_modules')->alias('am')->field($field)->leftJoin("sys_modules m", "m.identifie = am.module")->where($condition);
        if ($moduleFilter !== null) {
            $totalQuery->whereIn('am.module', $moduleFilter);
        }
        $total = $totalQuery->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item) {
            $appTypes = $this->getAppTypes($item);
            $item['app_types'] = AppTypesKeyEnum::getTextArray(implode(",", $appTypes));
            $settings = unserialize($item['settings']);

            // Logo处理：优先读取自定义设置的logo
            $customLogo = '';
            if (!empty($settings['basic']['logo'])) {
                $customLogo = tomedia($settings['basic']['logo']);
                $item['logo'] = $customLogo;
                $settings['basic']['logo'] = $customLogo;
            } elseif (!empty($item['logo'])) {
                // 其次读取数据表中的logo字段
                $item['logo'] = tomedia($item['logo']);
            }

            if (!empty($settings['basic']['name'])) {
                $item['name'] = $settings['basic']['name'];
            }

            $item['settings'] = $settings;

            // 受限账号必须进入当前应用实际拥有查看权限的第一个菜单页面。
            $accessibleUrl = (new AccessibleMenuService())->firstUrl(
                (string)$item['identifie'],
                (int)$this->uniacid,
                (int)$this->userId,
                (string)($this->adminSession['role'] ?? '')
            );
            if ($accessibleUrl !== null) {
                $item['url'] = $accessibleUrl;
            } elseif ($this->adminSession['role'] === 'operator' && $is_limit === 1) {
                // 受限账号没有可访问入口时从应用列表移除，避免点击后进入无权限页面。
                $item['url'] = '';
            } else {
                // 管理员或未启用限制的账号保留无菜单应用的兼容入口。
                $item['url'] = $this->getRealModuleUrl($item['identifie']);
            }
        }
        unset($item);
        if ($this->adminSession['role'] === 'operator' && $is_limit === 1) {
            $list = array_values(array_filter($list, static function (array $item): bool {
                return $item['url'] !== '';
            }));
            // 应用过滤后同步总数和分页信息，避免页面显示已移除的无权限应用数量。
            $total = count($list);
            $pager = pagination2($total, $this->pIndex, $this->pSize);
        }
        $list = set_medias($list, ['logo']);

        $uid = $this->userId;
        // 多角色合并：主角色 + 关联表所有角色 + 用户额外权限（统一走 UserPermService）
        $merged = \app\admin\service\UserPermService::getMergedPerms($uid, $this->uniacid);
        $perms = $merged['perms'];

        $newList = [];
        foreach ($list as $item) {
            $newList[$item['type']][] = $item;
        }
        unset($item);

        $result = [
            'list'     => $newList,
            'pager'    => $pager,
            'total'    => $total,
            'perms'    => $perms,
            'category' => $category,
            'is_limit' => $is_limit,
        ];
        return $result;
    }

    // 会员列表
    public function member()
    {
        $this->pSize = 20;

        $searchTime = trim($this->params["searchtime"]);
        $startTime = strtotime("-1 month");
        $endTime = time();

        $condition = [
            'uniacid'    => $this->uniacid,
            'is_deleted' => 0,
        ];

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, ["create"])) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime = strtotime($this->params["time"]["end"]);

            $condition[$searchTime . "_time"] = Db::raw("between {$startTime} and {$endTime} ");
        }

        if (!empty($this->params['module'])) {
            $condition['module'] = trim($this->params['module']);
        }

        $list = DbServiceFacade::name('sys_member')->getList($condition, "*", "id desc");
        $total = DbServiceFacade::name('sys_member')->getTotal($condition);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $list = set_medias($list, ['avatar']);
        foreach ($list as &$item) {
            $item['module_name'] = DbServiceFacade::name('sys_modules')->getValue(['identifie' => $item['module']], "name");
        }
        unset($item);

        // 获取所有模块列表用于下拉选择
        $appList = Db::name("sys_account_modules")->alias('am')
            ->leftJoin('sys_modules m', 'am.module = m.identifie')
            ->where(['am.uniacid' => $this->uniacid])
            ->where(['m.status' => 1, 'm.is_install' => 1, 'm.is_deleted' => 0])
            ->select()->toArray();

        $result = [
            'appList'   => $appList,
            'list'      => $list,
            'pager'     => $pager,
            'total'     => $total,
            'starttime' => $startTime,
            'endtime'   => $endTime,
        ];
        return $this->template('member', $result);
    }

    // 用户详情
    public function memberDetail()
    {
        $id = $this->params['id'];

        if ($this->request->isPost()) {
            $data = [
                'uniacid'  => $this->uniacid,
                'nickname' => trim($this->params['nickname']),
                'avatar'   => trim($this->params['avatar']),
                'realname' => trim($this->params['realname']),
                'mobile'   => trim($this->params['mobile']),
                'gender'   => trim($this->params['gender']),
            ];

            if (!empty($this->params['birthday_str'])) {
                $birthdayDate = strtotime($this->params['birthday_str']);
                $data['birthyear'] = date('Y', $birthdayDate);
                $data['birthmonth'] = date('m', $birthdayDate);
                $data['birthday'] = date('d', $birthdayDate);
            }

            if (!empty($id)) {
                DbServiceFacade::name('sys_member')->updateInfo($data, ['id' => $id]);
            } else {
                DbServiceFacade::name('sys_member')->insertInfo($data);
            }

            $this->success();
        }

        $condition = [
            'id' => $id
        ];
        $item = DbServiceFacade::name('sys_member')->getInfo($condition, "*");
        $result = [
            'item' => $item,
        ];
        return $this->template('memberDetail', $result);
    }

    // 获取应用类型
    private function getAppTypes($item): array
    {
        $appTypes = [];
        if (isset($item['wechat_support']) && $item['wechat_support'] == 1) {
            $appTypes[] = "wechat";
        }
        if (isset($item['wxapp_support']) && $item['wxapp_support'] == 1) {
            $appTypes[] = "wxapp";
        }
        if (isset($item['pc_support']) && $item['pc_support'] == 1) {
            $appTypes[] = "pc";
        }
        if (isset($item['app_support']) && $item['app_support'] == 1) {
            $appTypes[] = "app";
        }
        if (isset($item['h5_support']) && $item['h5_support'] == 1) {
            $appTypes[] = "h5";
        }
        if (isset($item['aliapp_support']) && $item['aliapp_support'] == 1) {
            $appTypes[] = "aliapp";
        }
        if (isset($item['bdapp_support']) && $item['bdapp_support'] == 1) {
            $appTypes[] = "bdapp";
        }
        if (isset($item['uniapp_support']) && $item['uniapp_support'] == 1) {
            $appTypes[] = "uniapp";
        }
        if (isset($item['harmonyos_support']) && $item['harmonyos_support'] == 1) {
            $appTypes[] = "harmonyos";
        }
        if (isset($item['dyapp_support']) && $item['dyapp_support'] == 1) {
            $appTypes[] = "dyapp";
        }
        if (isset($item['aiapp_support']) && $item['aiapp_support'] == 1) {
            $appTypes[] = "aiapp";
        }
        return $appTypes;
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

    // 编辑我的账号
    public function profileEdit()
    {
        $uniacid = intval($this->params['i'] ?? 0);
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

        return $this->template('profileEdit', ['uniacid' => $uniacid]);
    }

    // 获取后端访问入口
    private function getRealModuleUrl($moduleName)
    {
        $moduleName = trim((string)$moduleName);
        $configuredUrl = (new AccessibleMenuService())->firstConfiguredUrl(
            $moduleName,
            (int)$this->uniacid
        );
        if ($configuredUrl !== null) {
            return $configuredUrl;
        }

        // 没有菜单配置时，仅兼容确实存在的 web/Index 控制器，避免生成不存在的 web.index。
        $realModuleName = realModuleName($moduleName);
        $indexController = IA_ROOT . '/app/' . $moduleName . '/controller/web/Index.php';
        if (is_file($indexController)) {
            return webUrl('/' . $realModuleName . '/web.index', ['i' => $this->uniacid]);
        }

        // 应用没有可确认的入口时转到统一 404 页面。
        return webUrl('/admin/error/index', ['type' => 404, 'i' => $this->uniacid]);
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

            if (!empty($settingsData['remote']) && $settingsData['remote']['type'] == 2 && !empty($settingsData['remote']['alioss']) && empty($settingsData['remote']['alioss']['url'])) {
                $attachmentController = new AttachmentWrapper();
                [$bucket, $url] = explode('@@', $settingsData['remote']['alioss']['bucket']);
                $buckets = $attachmentController->attachmentAliossBuctkets($settingsData['remote']['alioss']['key'], $settingsData['remote']['alioss']['secret']);
                $host_name = $settingsData['remote']['alioss']['internal'] ? '-internal.aliyuncs.com' : '.aliyuncs.com';
                $endpoint = 'http://' . $buckets[$bucket]['location'] . $host_name;
                $settingsData['remote']['alioss']['url'] = $endpoint;
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

            // 账号验证
            $username = trim($this->params['username'] ?? '');
            if (!empty($username)) {
                $userInfo = DbServiceFacade::name('sys_users')->getInfo(['username' => $username]);
                if (!empty($userInfo)) {
                    if (empty($uniacid)) {
                        $this->error("该账号已被占用");
                    } else {
                        $accountUserInfo = DbServiceFacade::name('sys_account_users')->getInfo(['uniacid' => $uniacid, 'user_id' => $userInfo['id']]);
                        if (empty($accountUserInfo)) {
                            $this->error("该账号已被占用");
                        }
                    }
                } else {
                    if (empty($this->params['password'])) {
                        $this->error("请输入管理员密码");
                    }
                }
            }


            Db::name('sys_account')->where(['uniacid' => $uniacid])->update($data);

            if (!empty($settingsData)) {
                $data['settings'] = serialize($settingsData);
                Db::name('sys_account')->where(["uniacid" => $uniacid])->update($data);
                # 更新缓存
                $this->settingsController->reloadAccountSettings($uniacid);
            }

            # 分配应用
            $this->setAccountModules($uniacid);

            # 重新加载商户配置信息
            $this->settingsController->reloadAccountSettings($uniacid);

            # 管理员账号
            $this->accountUser($uniacid);

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

        $accountUserInfo = DbServiceFacade::name('sys_account_users')->getInfo(['uniacid' => $uniacid]);
        if (!empty($accountUserInfo)) {
            $userInfo = DbServiceFacade::name('sys_users')->getInfo(['id' => $accountUserInfo['user_id']]);
            $item['username'] = $userInfo['username'];
        }


        # 查询紫薯AI用户信息
        $aiDriveStatus = "";
        $aiDriveBalance = "";
        $errorMessage = "";
        $modelAndPlatformList = [];
        if (!empty($accountSettings) && !empty($accountSettings['aidrive']) && !empty($accountSettings['aidrive']['accessKeyId'])) {
            try {
                $zishuUserInfo = (new ZiShuAiService($uniacid))->getUser();
                $aiDriveStatus = $zishuUserInfo['status'];
                $aiDriveBalance = $zishuUserInfo['balance'];
                $modelAndPlatformList = (new ZiShuAiService($uniacid))->getModelAndPlatform();
            } catch (\Exception $e) {
                $aiDriveStatus = "UNKNOWN";
                $aiDriveBalance = "0";
                $errorMessage = $e->getMessage();
                if (empty($errorMessage)) {
                    $errorMessage = "紫薯AI接口通信失败";
                }
            }
        }

        $result = [
            'item'                 => $item,
            'uniacid'              => $uniacid,
            'hostList'             => $hostList,
            'accountSettings'      => $accountSettings,
            'modules'              => $modules,
            'postUrl'              => strval(url('system/attachment')),
            'local_attachment'     => $localAttachment,
            'aiDriveStatus'        => $aiDriveStatus,
            'aiDriveBalance'       => $aiDriveBalance,
            'errorMessage'         => $errorMessage,
            'modelAndPlatformList' => $modelAndPlatformList,
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
        $uniacid = (int)($this->params['uniacid'] ?? 0);
        $module = trim((string)($this->params['module'] ?? ''));

        if ($uniacid > 0 && $module !== '') {
            $moduleExists = Db::name('sys_account_modules')
                ->where(['uniacid' => $uniacid, 'module' => $module, 'deleted' => 0])
                ->count();
            if (!$moduleExists) {
                show_json(0, '应用不存在或未安装');
            }

            // 切换应用前复用与登录一致的菜单权限解析，阻止受限账号切入无可访问页面的应用。
            if (($this->adminSession['role'] ?? '') === 'operator') {
                $permUserInfo = Db::name('sys_account_perm_user')
                    ->where(['uniacid' => $uniacid, 'uid' => (int)$this->userId, 'status' => 1])
                    ->find();
                if (!$permUserInfo || (int)$permUserInfo['is_limit'] === 1) {
                    $accessibleUrl = (new AccessibleMenuService())->firstUrl(
                        $module,
                        $uniacid,
                        (int)$this->userId,
                        'operator'
                    );
                    if ($accessibleUrl === null) {
                        show_json(0, '当前账号没有该应用的可访问页面');
                    }
                }
            }

            # 设置默认应用
            Db::name('sys_account_modules')->where(['uniacid' => $uniacid])->update(['is_default' => 0]);
            Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'module' => $module])->update(['is_default' => 1]);

            # 切换用户的默认应用
            Db::name('sys_account_users')->where(['uniacid' => $uniacid, 'user_id' => $this->userId])->update(['module' => $module]);
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
            if ($this->adminSession['role'] == 'owner') {
                Db::name('sys_account_modules')->where(['uniacid' => $uniacid])->update(['deleted' => 1]);
            }
        }

        // 更新uniacid的应用列表
        SystemWrapperFacade::reloadAccountModuleList($uniacid);

        return true;
    }

    // 管理员管理
    private function accountUser($uniacid)
    {
        $username = $this->params['username'] ?? '';
        $password = $this->params['password'] ?? '';

        if (!empty($username) && !empty($password)) {
            $salt = RandomUtil::random(6);
            $newPassword = md5($password . $salt);

            $userInfo = DbServiceFacade::name('sys_users')->getInfo(['username' => $username]);

            if (!empty($userInfo)) {
                $accountUserInfo = DbServiceFacade::name('sys_account_users')->getInfo(['user_id' => $userInfo['id']]);
                if (!empty($accountUserInfo)) {
                    if ($accountUserInfo['uniacid'] == $uniacid) {
                        DbServiceFacade::name('sys_users')->updateInfo(['salt' => $salt, 'password' => $newPassword], ['id' => $userInfo['id']]);
                    }
                }
            } else {
                $userData = [
                    "username"   => $username,
                    "password"   => $newPassword,
                    "salt"       => $salt,
                    "role"       => 'manager',
                    "createtime" => TIMESTAMP,
                    "status"     => 1,
                ];
                $userId = DbServiceFacade::name('sys_users')->insertInfo($userData);
                $accountUserData = [
                    "uniacid" => $uniacid,
                    "user_id" => $userId,
                    "module"  => '',
                ];
                DbServiceFacade::name('sys_account_users')->insertInfo($accountUserData);
            }
        }
    }

}
