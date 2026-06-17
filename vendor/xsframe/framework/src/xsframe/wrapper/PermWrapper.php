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
        $oldPermUrl = $permUrl;

        // /* 测试 start */
        // if( $permType == 1 ){
        //     dump($permUrl);
        // }
        // /* 测试 end */

        // 操作名映射转换（支持动态操作名）
        $lastDot = strrpos($permUrl, '.');
        $lastSlash = strrpos($permUrl, '/');
        $lastSep = max($lastDot, $lastSlash);
        if ($lastSep !== false) {
            $preFixUrl = substr($permUrl, 0, $lastSep + 1);
            $operation = substr($permUrl, $lastSep + 1);

            if (in_array($operation, ['main', 'detail', 'add', 'edit', 'delete'])) {

            } else if (in_array($operation, ['status', 'change', 'del', 'save'])) {
                $operation = 'edit';
                if ($operation == 'del') $operation = 'delete';
            } else {
                // 默认映射为 edit
                $operation = 'edit';

                // 从 permUrl 中提取模块名和路径部分
                $slashPos = strpos($permUrl, '/');
                if ($slashPos === false) {
                    // 格式不符合预期，保留原 operation
                    return false;
                }
                $module = substr($permUrl, 0, $slashPos);
                $path = substr($permUrl, $slashPos + 1);  // 如 "form.basic.log"

                // 加载模块菜单配置
                $menuPath = APP_PATH . "/{$module}/config/menu.php";
                if (!is_file($menuPath)) {
                    // 没有菜单文件，无法映射，使用默认 edit
                    $permUrl = $preFixUrl . $operation;
                } else {
                    $moduleMenus = require $menuPath;
                    // 按点分割路径，获取主菜单键、子菜单键、操作名
                    $parts = explode('.', $path);
                    $partsCount = count($parts);
                    if ($partsCount >= 2) {
                        $mainKey = $parts[1];      // 如 'form'
                        $subKey = $parts[2];      // 如 'basic'
                        $opName = $parts[3] ?? ''; // 如 'log'

                        // 查找主菜单
                        if (isset($moduleMenus[$mainKey]) && !empty($moduleMenus[$mainKey]['items'])) {
                            foreach ($moduleMenus[$mainKey]['items'] as $item) {
                                // 子菜单的 route 通常是 "basic/main" 或 "module/main"
                                $routeParts = explode('/', $item['route']);
                                if ($routeParts[0] === $subKey) {
                                    // 找到对应子菜单，获取其 perm 定义
                                    $permDef = $item['perm'] ?? [];
                                    if (isset($permDef['xxx']) && is_array($permDef['xxx']) && $opName !== '') {
                                        // 如果操作名在映射表中，则替换
                                        if (array_key_exists($opName, $permDef['xxx'])) {
                                            $operation = $permDef['xxx'][$opName];
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    // 重新构造完整的 permUrl
                    $permUrl = $preFixUrl . $operation;
                }
            }
        }

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

        $field = " u.uid,u.status as userstatus,u.is_limit,u.app_perms,r.status as rolestatus,u.perms as userperms,r.perms as roleperms,u.roleid ";

        $where = ['u.uid' => $uid];
        if (!empty($userInfo['uniacid'])) {
            $where['u.uniacid'] = $userInfo['uniacid'];
        }
        $user = Db::name("sys_account_perm_user")->alias('u')->field($field)->leftJoin("sys_account_perm_role r", "r.id = u.roleid")->where($where)->find();
        if ($user['is_limit'] == 0) {
            return true;
        }

        if (empty($user) || empty($user['userstatus'])) {
            return false;
        }
        if (!empty($user['roleid']) && empty($user['rolestatus'])) {
            return false;
        }

        $role_perms = explode(',', $user['roleperms']);
        $user_perms = explode(',', $user['userperms']);
        $app_perms = explode(',', $user['app_perms']);

        if (empty($role_perms) && empty($user_perms) && empty($app_perms)) {
            return false;
        }

        $permUrlArr = explode("/", $permUrl);
        $module = $permUrlArr[0];
        $appMaps = Config::get('app.app_map') ?? [];
        $appModuleName = $appMaps[$module] ?? $module; // 获取应用真实文件夹名称匹配权限

        # 是否有应用的所有权限
        if (in_array($module, $app_perms)) {
            return true;
        }

        if ($permType == 1) {
            // 尝试从菜单配置中查找父级菜单键（用于主菜单权限校验）
            $module = $permUrlArr[0] ?? '';
            if ($module) {
                $menuPath = APP_PATH . "/{$module}/config/menu.php";
                if (is_file($menuPath)) {
                    $moduleMenus = require $menuPath;
                    // 获取路径部分，如 "web.receive_plan/index"
                    $pathPart = isset($permUrlArr[1]) ? $permUrlArr[1] : '';
                    // 提取子菜单键（去掉 web. 前缀，并取第一个部分）
                    $subKey = '';
                    if (strpos($pathPart, 'web.') === 0) {
                        $pathPart = substr($pathPart, 4);
                    }
                    if (strpos($pathPart, '/') !== false) {
                        $parts = explode('/', $pathPart);
                        $subKey = $parts[0];
                    } else if (strpos($pathPart, '.') !== false) {
                        $parts = explode('.', $pathPart);
                        $subKey = $parts[0];
                    } else {
                        $subKey = $pathPart;
                    }

                    // 在菜单配置中查找包含该子菜单的父级菜单键
                    if ($subKey) {
                        foreach ($moduleMenus as $parentKey => $menu) {
                            if (!empty($menu['items'])) {
                                foreach ($menu['items'] as $item) {
                                    $itemRoute = $item['route'] ?? $item['url'] ?? '';
                                    // 匹配子菜单的 route 或 url 是否以 subKey 开头（支持 subKey/main 或 subKey/index）
                                    if ($itemRoute && strpos($itemRoute, $subKey) === 0) {
                                        // 找到父级键，构造父级权限点（点分隔格式）
                                        $permUrl = $module . '.web.' . $parentKey;
                                        break 2; // 跳出两层循环
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // 如果未找到父级菜单，则保持原 $permUrl 不变（后续会按常规转换）
        } else {
            $permUrlArr[0] = $appModuleName;
            $permUrl = implode("/", $permUrlArr);
        }

        $permUrl = str_replace("/", '.', $permUrl);

        $search = '.index';
        $replace = '.main';
        $pos = strrpos($permUrl, $search);
        if ($pos !== false) {
            $permUrl = substr_replace($permUrl, $replace, $pos, strlen($search));
        }

        $perms = array_merge($role_perms, $user_perms);
        $perms = array_unique($perms);

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
                'detail' => '查看详情',
                'add'    => '添加-log',
                'edit'   => '修改-log',
                'delete' => '删除-log',
                'xxx'    => [
                    'status' => 'edit',  // 继承edit权限，默认继承
                    'change' => 'edit',  // 继承edit权限，默认继承
                    'del'    => 'delete',// 继承delete权限，默认继承
                    'save'   => 'edit',  // 继承edit权限，默认继承
                ],
            ];

            // $permDefault = [];

            // if( $module == 'sz_propt' ){
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
                            } else {
                                $perm = $permDefault;
                            }

                            if ($item['route']) {
                                $routers = explode("/", $item['route']);
                                $c = count($routers) > 1 ? "." . $routers[0] : '';
                            } else {
                                $routers = explode("/", $item['url']);
                                $c = count($routers) > 1 ? "." . $routers[0] : '';
                            }

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
                } else {
                    $perm = [];

                    if (is_array($menu['perm']) && !empty($menu['perm'])) {
                        $perm = $menu['perm'];
                    } else {
                        $perm = $permDefault;
                    }

                    $routers = explode("/", $menu['route']);
                    $c = count($routers) > 1 ? "." . $routers[0] : '';

                    $newModuleMenusItemText = ['text' => !empty($menu['subtitle']) ? $menu['subtitle'] : $item['title']];
                    $newModuleMenusItem = array_merge($newModuleMenusItemText, $perm);

                    if (empty($c)) {
                        $itemModuleMenus[$menu['route']] = $newModuleMenusItem;
                    } else {
                        $itemRoute = preg_replace('/^\./u', '', $c); // 去掉第一个字符是.的
                        $itemRoute = preg_replace('/\bweb\..*?\b/u', '', $itemRoute);// 去掉第一个字符是web.的
                        $itemModuleMenus[$itemRoute] = $newModuleMenusItem;
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
    public function formatPerms($uniacid, $moduleName = null)
    {
        if (empty(self::$formatPerms)) {

            $perms = $this->allPerms($uniacid, $moduleName);
            if (!empty($moduleName)) {
                $perms = [$moduleName => $perms[$moduleName]];
            }

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