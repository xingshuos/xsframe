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

namespace xsframe\wrapper;

use think\facade\Config;
use xsframe\util\StringUtil;
use think\facade\Db;

class PermWrapper
{
    public static $allPerms = [];
    public static $formatPerms = [];

    // 验证权限 $permType 1主菜单 2子菜单 3操作
    public function checkPerm($permUrls = '', $permType = 3)
    {
        $check = true;

        if (empty($permUrls)) {
            return false;
        }

        if (!strexists($permUrls, '&') && !strexists($permUrls, '|')) {
            $check = $this->check($permUrls, $permType);
        } else if (strexists($permUrls, '&')) {
            $pts = explode('&', $permUrls);

            foreach ($pts as $pt) {
                $check = $this->check($pt, $permType);

                if (!$check) {
                    break;
                }
            }
        } else {
            if (strexists($permUrls, '|')) {
                $pts = explode('|', $permUrls);

                foreach ($pts as $pt) {
                    $check = $this->check($pt, $permType);

                    if ($check) {
                        break;
                    }
                }
            }
        }

        return $check;
    }

    private function check($permUrl = '', $permType = 3)
    {
        $loginResult = UserWrapper::checkUser();
        $userInfo = $loginResult['adminSession'];
        $role = $userInfo['role'];

        if (in_array($role, ['founder', 'manager', 'owner'])) {
            return true;
        }

        $uid = $userInfo['uid'];

        if (empty($permUrl)) {
            return false;
        }

        $field = " u.uid,u.status as userstatus,r.status as rolestatus,u.perms as userperms,r.perms as roleperms,u.roleid ";

        $where = ['u.uid' => $uid];
        if( !empty($userInfo['uniacid']) ){
            $where['u.uniacid'] = $userInfo['uniacid'];
        }
        $user = Db::name("sys_account_perm_user")->alias('u')->field($field)->leftJoin("sys_account_perm_role r", "r.id = u.roleid")->where($where)->find();

        if (empty($user) || empty($user['userstatus'])) {
            return false;
        }
        if (!empty($user['roleid']) && empty($user['rolestatus'])) {
            return false;
        }

        $role_perms = explode(',', $user['roleperms']);
        $user_perms = explode(',', $user['userperms']);

        if (empty($role_perms) && empty($user_perms)) {
            return false;
        }

        $permUrlArr = explode("/", $permUrl);
        $module = $permUrlArr[0];
        $appMaps = Config::get('app.app_map') ?? [];
        $appModuleName = $appMaps[$module] ?? $module; // 获取应用真实文件夹名称匹配权限

        if ($permType == 1) {
            $permUrlArr = array_splice($permUrlArr, 0, 2);

            $controllerUrl = $permUrlArr[1];
            $controllerUrlArr = explode(".", $controllerUrl);
            $controllerUrlArr = array_splice($controllerUrlArr, 0, 2);
            $controllerUrl = implode("/", $controllerUrlArr);

            $permUrl = $appModuleName . "/" . $controllerUrl;
        } else {
            $permUrlArr[0] = $appModuleName;
            $permUrl = implode("/", $permUrlArr);
        }

        $permUrl = str_replace("/", '.', $permUrl);

        $perms = array_merge($role_perms, $user_perms);
        if (!in_array($permUrl, $perms)) {
            return false;
        }

        return true;
    }

    // 获取全部权限
    public function allPerms($uniacid)
    {
        if (empty(self::$allPerms)) {
            // 1.获取当前商户所有应用 TODO 需要缓存插件列表
            $modules = $this->getModules($uniacid);
            // dump($modules);die;

            // 2.获取所有插件菜单列表
            $perms = [];
            foreach ($modules as &$module) {
                $perms[$module['module']] = array_merge([
                    'text' => $module['name']
                ], $this->getModuleMenus($module['module']));
            }
            self::$allPerms = $perms;
        }
        return self::$allPerms;
    }

    // 获取插件菜单信息
    public function getModuleMenus($module)
    {
        $menuPath = APP_PATH . "/{$module}/config/menu.php";

        if (!is_file($menuPath)) {
            return [];
        }

        $moduleMenus = require $menuPath;

        $parentModuleMenus = [];
        if (!empty($moduleMenus)) {

            $permDefault = [
                'main'   => '查看列表',
                'view'   => '查看详情',
                'add'    => '添加-log',
                'edit'   => '修改-log',
                'delete' => '删除-log',
                'xxx'    => [
                    'status' => 'edit'
                ],
            ];

            // $permDefault = [];

            // if( $module == 'jrp_anjia' ){
            //     dd($moduleMenus);
            // }

            foreach ($moduleMenus as $key => $menu) {

                // 子菜单权限
                $itemModuleMenus = [];

                if (!empty($menu['items'])) {
                    foreach ((array)$menu['items'] as $item) {
                        if (!empty($item['items'])) {
                            foreach ((array)$item['items'] as $item2) {

                                $perm = $permDefault;
                                if (is_array($item2['perm']) && !empty($item2['perm'])) {
                                    $perm = $item2['perm'];
                                }

                                $routers = explode("/", $item2['route']);
                                $c = count($routers) > 1 ? "." . $routers[0] : '';

                                $newModuleMenusItemText = ['text' => !empty($item2['subtitle']) ? $item2['subtitle'] : $item2['title']];
                                $newModuleMenusItem = array_merge($newModuleMenusItemText, $perm);

                                if (empty($c)) {
                                    $itemModuleMenus[$item2['route']] = $newModuleMenusItem;
                                } else {
                                    $itemRoute = preg_replace('/^\./u', '', $c); // 去掉第一个字符是.的
                                    $itemRoute = preg_replace('/\bweb\..*?\b/u', '', $itemRoute);// 去掉第一个字符是web.的
                                    $itemModuleMenus[$itemRoute] = $newModuleMenusItem;
                                }
                            }
                        } else {
                            $perm = [];

                            if (is_array($item['perm']) && !empty($item['perm'])) {
                                $perm = $item['perm'];
                            }else{
                                $perm = $permDefault;
                            }

                            $routers = explode("/", $item['route']);
                            $c = count($routers) > 1 ? "." . $routers[0] : '';

                            $newModuleMenusItemText = ['text' => !empty($item['subtitle']) ? $item['subtitle'] : $item['title']];
                            $newModuleMenusItem = array_merge($newModuleMenusItemText, $perm);

                            if (empty($c)) {
                                $itemModuleMenus[$item['route']] = $newModuleMenusItem;
                            } else {
                                $itemRoute = preg_replace('/^\./u', '', $c); // 去掉第一个字符是.的
                                $itemRoute = preg_replace('/\bweb\..*?\b/u', '', $itemRoute);// 去掉第一个字符是web.的
                                $itemModuleMenus[$itemRoute] = $newModuleMenusItem;
                            }
                        }
                    }
                }

                // 主菜单权限
                $parentMenus = array_merge(['text' => $menu['subtitle']], $itemModuleMenus);

                if (!strexists($key, "web.")) {
                    $key = "web." . $key;
                }

                $parentModuleMenus[$key] = $parentMenus;
            }
        }

        return $parentModuleMenus;
    }

    // 获取全部插件标识
    public function getModules($uniacid)
    {
        $modules = Db::name('sys_account_modules')->alias("am")->field("am.module,m.name,m.identifie")->leftJoin("sys_modules m", "m.identifie = am.module")->where(['am.uniacid' => $uniacid, 'am.deleted' => 0])->select()->toArray();
        return $modules;
    }

    // 格式化权限
    public function formatPerms($uniacid)
    {
        if (empty(self::$formatPerms)) {

            $perms = $this->allPerms($uniacid);
            // dump($perms['jrp_anjia']);die;

            $array = [];

            foreach ($perms as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $ke => $val) {
                        if (!is_array($val)) {
                            $array['parent'][$key][$ke] = $val;
                        }

                        if (is_array($val) && $ke != 'xxx') {
                            foreach ($val as $k => $v) {
                                if (!is_array($v)) {
                                    $array['son'][$key][$ke][$k] = $v;
                                }

                                if (is_array($v) && $k != 'xxx') {
                                    foreach ($v as $kk => $vv) {
                                        if (!is_array($vv)) {
                                            $array['grandson'][$key][$ke][$k][$kk] = $vv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            self::$formatPerms = $array;
        }

        return self::$formatPerms;
    }


}