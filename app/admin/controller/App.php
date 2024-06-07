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

use think\facade\Cache;
use think\facade\Db;
use xsframe\enum\CacheKeyEnum;
use xsframe\util\FileUtil;
use xsframe\util\PinYinUtil;
use xsframe\wrapper\ModulesWrapper;

class App extends Base
{
    public function index()
    {
        return redirect('/admin/app/list');
    }

    public function list()
    {
        $do = $this->params['do'] ?? '';
        $type = $this->params['type'] ?? '';
        $nameInitial = $this->params['letter'] ?? '';

        $condition1 = ['is_deleted' => 0, 'is_install' => 1, 'status' => 1];
        $condition2 = ['is_deleted' => 0, 'is_install' => 0];
        $condition3 = ['is_deleted' => 0, 'is_install' => 1, 'status' => 0];
        $condition4 = ['is_deleted' => 1];

        $condition = [];

        $modulesController = new ModulesWrapper();

        $this->updateSystemModuleList();

        if (empty($do) || $do == 'installed') {
            $condition = $condition1;
        } else {
            if ($do == 'not_installed') {
                $condition = $condition2;

                $key = $this->websiteSets['key'] ?? '';
                $token = $this->websiteSets['token'] ?? '';

                $modulesController->buildLocalUnInstalledModule();
                $modulesController->buildCloudUnInstalledModule($key, $token);
            } else {
                if ($do == 'recycle') {
                    $condition = $condition3;
                } else {
                    if ($do == 'delete') {
                        $condition = $condition4;
                    }
                }
            }
        }

        if (!empty($type)) {
            $condition[$type . '_support'] = 1;
        }

        if (!empty($this->params['keyword'])) {
            $condition['name'] = Db::raw("like '%" . trim($this->params['keyword']) . "%'");
        }
        if (!empty($nameInitial)) {
            $condition['name_initial'] = strtoupper($nameInitial);
        }

        $list = Db::name('sys_modules')->where($condition)->order('create_time desc')->page($this->pIndex, $this->pSize)->select();
        $total = Db::name("sys_modules")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $list = $list->toArray();

        foreach ($list as &$item) {
            $item['logo'] = tomedia($item['logo']);

            $manifest = $modulesController->extModuleManifest($item['identifie']);
            if (!empty($manifest) && version_compare($manifest['application']['version'], $item['version'], '>')) {
                $item['new_version'] = 1;
            }
        }

        $total1 = Db::name("sys_modules")->where($condition1)->count();
        $total2 = Db::name("sys_modules")->where($condition2)->count();
        $total3 = Db::name("sys_modules")->where($condition3)->count();
        $total4 = Db::name("sys_modules")->where($condition4)->count();
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'W', 'T', 'D', 'T', 'U', 'V', 'E', 'C', 'Y', 'X'];

        $vars = [
            'list'  => $list,
            'total' => $total,
            'pager' => $pager,

            'nameInitial' => $nameInitial,
            'do'          => $do,
            'type'        => $type,
            'total1'      => $total1,
            'total2'      => $total2,
            'total3'      => $total3,
            'total4'      => $total4,
            'letters'     => $letters,
        ];
        return $this->template('list', $vars);
    }

    public function edit()
    {
        return $this->post();
    }

    public function post()
    {
        $id = $this->params['id'];
        if ($this->request->isPost()) {
            $data = [
                "name"        => trim($this->params["name"]),
                "logo"        => tomedia(trim($this->params["logo"])),
                "author"      => trim($this->params["author"]),
                "description" => trim($this->params["description"]),
                "status"      => intval($this->params["status"]),

                "wechat_support" => intval($this->params["wechat_support"]),
                "wxapp_support"  => intval($this->params["wxapp_support"]),
                "pc_support"     => intval($this->params["pc_support"]),
                "app_support"    => intval($this->params["app_support"]),
                "h5_support"     => intval($this->params["h5_support"]),
                "aliapp_support" => intval($this->params["aliapp_support"]),
                "bdapp_support"  => intval($this->params["bdapp_support"]),
                "uniapp_support" => intval($this->params["uniapp_support"]),
            ];
            Db::name('sys_modules')->where(['id' => $id])->update($data);
            $this->success(["url" => webUrl("app/edit", ['id' => $id, 'tab' => str_replace("#tab_", "", $this->params['tab'])])]);
        }

        $item = Db::name('sys_modules')->where(['id' => $id])->find();
        // $item['logo'] = !empty($item['logo']) ? tomedia($item['logo']) : $this->siteRoot . "/app/{$item['identifie']}/icon.png";
        $item['logo'] = !empty($item['logo']) ? $item['logo'] : $this->siteRoot . "/app/{$item['identifie']}/icon.png";

        $result = [
            'item' => $item,
        ];
        return $this->template('post', $result);
    }

    // 通用更新数据
    public function change()
    {
        $id = intval($this->params["id"]);
        if (empty($id)) {
            $id = $this->params["ids"];
        }
        if (empty($id)) {
            $this->error(["message" => "参数错误"]);
        }
        $type = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $items = Db::name('sys_modules')->field("id")->where(['id' => $id])->select()->toArray();
        foreach ($items as $item) {
            Db::name('sys_modules')->where(['id' => $item['id']])->update([$type => $value]);
        }

        $this->updateSystemModuleList();
        $this->success();
    }

    // 应用安装
    public function install()
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $id = intval($this->params["id"]);
        $identifie = trim($this->params["identifie"]);

        Db::name('sys_modules')->where(['id' => $id])->update(['is_install' => 1, 'status' => 1]);
        Db::name('sys_account_modules')->where(['module' => $identifie])->update(['deleted' => 0]);

        $modulesController = new ModulesWrapper();
        $isTrue = $modulesController->runInstalledModule($identifie, $key, $token);

        if (!$isTrue) {
            $this->success('安装失败');
        }

        $modulesController->moveDirToPublic($identifie);
        $this->removePackages($identifie);

        $this->updateSystemModuleList();
        $this->success('安装成功');
    }

    // 应用卸载
    public function uninstall()
    {
        $id = intval($this->params["id"]);
        $identifie = trim($this->params["identifie"]);

        Db::name('sys_modules')->where(['id' => $id])->update(['is_install' => 0]);
        Db::name('sys_account_modules')->where(['module' => $identifie])->update(['deleted' => 1]);

        $modulesController = new ModulesWrapper();
        $modulesController->runUninstalledModule($identifie);
        $this->updateSystemModuleList();
        $this->success('卸载成功');
    }

    // 应用升级
    public function upgrade()
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $id = intval($this->params["id"]);
        $identifie = trim($this->params["identifie"]);
        $isCloud = $this->params['is_cloud'] ?? 0;

        $modulesController = new ModulesWrapper();
        $manifest = $modulesController->runUpgradeModule($identifie, $key, $token, $isCloud);

        if ($manifest) {
            $modulesController->moveDirToPublic($identifie);

            $updateData = [
                'type'         => $manifest['application']['type'],
                'name'         => $manifest['application']['name'],
                'version'      => $manifest['application']['version'],
                'author'       => $manifest['application']['author'],
                'ability'      => $manifest['application']['ability'],
                'description'  => $manifest['application']['description'],
                'update_time'  => time(),
                'name_initial' => PinYinUtil::getFirstPinyin($manifest['application']['name']),
            ];
            Db::name('sys_modules')->where(['id' => $id])->update($updateData);

            $this->removePackages($identifie);
            $this->updateSystemModuleList();
            $this->success('升级成功');
        } else {
            $this->success('升级失败');
        }
    }

    // 删除多余资源包
    private function removePackages($identifie)
    {
        $packagesPath = IA_ROOT . "/app/{$identifie}/packages";
        $unFiles = [
            $packagesPath . "/source",
            $packagesPath . "/install.php",
            $packagesPath . "/manifest.xml",
            $packagesPath . "/uninstall.php",
            $packagesPath . "/upgrade.php",
        ];
        FileUtil::rmDirs($packagesPath, $unFiles);
        return true;
    }

    // 更新系统的应用列表
    private function updateSystemModuleList(): bool
    {
        return Cache::set(CacheKeyEnum::SYSTEM_MODULE_LIST_KEY, Db::name('sys_modules')->where(['status' => 1, 'is_install' => 1, 'is_deleted' => 0])->column('identifie'));
    }
}