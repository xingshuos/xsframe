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

class MenuWrapper
{
    // 获取菜单
    public static function getMenusList($role, $module, $controller, $action, $full = true, $configKey = 'menu'): ?array
    {
        $allMenus = Config::get($configKey);
        return self::buildMenu($role, $allMenus, $module, strtolower($controller), $action, $full);
    }

    private static function getChangeParentInfo($allMenus, $module, $controller, $action): array
    {
        $url = $controller . "/" . $action;

        $parentMenuRoute = $controller;
        $currentRouteIsChange = false;

        foreach ($allMenus as $key => &$menuInfo) {
            if (!empty($menuInfo['items'])) {
                $route = $key;
                foreach ($menuInfo['items'] as &$itemInfo) {
                    if (!empty($itemInfo['url'])) {

                        $urlArr = explode("/", $url);
                        $itemUrlArr = explode("/", $itemInfo['url']);

                        if (strexists($urlArr[0], $itemUrlArr[0])) {
                            $menuInfo['active'] = 1;
                            $currentRouteIsChange = true;
                            if (!strexists($route, 'web.') && $module != 'admin') {
                                $route = "web." . $route;
                            }
                            $parentMenuRoute = $route;
                            $itemInfo['active'] = 1;
                        }
                    }
                }
            }
        }

        return [
            'allMenus'    => $allMenus,
            'parentRoute' => $parentMenuRoute,
            'isChange'    => $currentRouteIsChange,
        ];
    }

    // 定义菜单结构
    private static function buildMenu($role, $allMenus, $module, $controller, $action, $full)
    {
        $return_menu = [];
        $return_submenu = [];
        $submenu = [];
        $pageTitle = "";
        $module = realModuleName($module);

        # 验证当前路由是否调换（子路由调换到其他父级路由中）
        $getChangeParentInfo = self::getChangeParentInfo($allMenus, $module, $controller, $action);

        $parentMenuRoute = $getChangeParentInfo['parentRoute'] ?? $controller; // 当前路由是否调换
        $parentMenuIsChange = $getChangeParentInfo['isChange'] ?? false; // 是否已经有选中的菜单
        $allMenus = $getChangeParentInfo['allMenus'] ?? $allMenus;
        $parentMenuActive = false;// 是否已经有选中的菜单

        if ($controller != 'login') {
            foreach ($allMenus as $key => $val) {
                $menu_item = [
                    'route'    => empty($val['route']) ? $key : $val['route'],
                    'text'     => $val['title'],
                    'subtitle' => $val['subtitle'],
                    'active'   => $val['active'] ?? 0,
                ];

                if (!strexists($menu_item['route'], 'web.') && $module != 'admin') {
                    $menu_item['route'] = "web." . $menu_item['route'];
                }

                // dump($menu_item);

                if (!empty($val['icon'])) {
                    $menu_item['icon'] = $val['icon'];
                }

                if (!$parentMenuActive) {
                    $runIn = false;
                    if ($parentMenuIsChange) {
                        if ($menu_item['route'] == $parentMenuRoute) {
                            $runIn = true;
                        }
                    } else {
                        if (strexists($controller, $menu_item['route'])) {
                            $runIn = true;
                        }
                    }

                    if ($runIn) {
                        $parentMenuActive = true;
                        $menu_item['active'] = 1;

                        $submenu = $val;
                        $return_submenu['subtitle'] = $submenu['subtitle'];

                        $pageTitle = $submenu['subtitle'];
                        if (empty($pageTitle)) {
                            $pageTitle = $submenu['title'];
                        }
                    }
                }

                # 设置一级目录路由 start
                if (!empty($val['items'])) {
                    $itemsOneRoute = $val['items'][0]['route'] ?? null;

                    if (empty($itemsOneRoute) && !empty($val['items'][0]['items'])) {
                        $itemsOneRoute = $val['items'][0]['items'][0]['route'] ?? null;
                        if ($itemsOneRoute) {
                            $val['items'][0]['route'] = $itemsOneRoute;
                        } else {
                            $itemsOneRoute = $val['items'][0]['items'][0]['url'] ?? null;
                            if ($itemsOneRoute) {
                                $val['items'][0]['url'] = $itemsOneRoute;
                            }
                        }
                    }

                    $itemsOneRouteIsChange = false; // 解决一级菜单默认访问url
                    if (empty($val['items'][0]['route'])) {
                        $itemsOneRoute = $val['items'][0]['url'] ?? null;
                        if ($itemsOneRoute) {
                            $itemsOneRouteIsChange = true;
                        }
                    }

                    // 操作员权限验证 start
                    if (!in_array($role, ['founder', 'manager', 'owner'])) {
                        // if ($val['items'][0]['perm']) { // 旧逻辑需要再代码中设置开启权限
                        foreach ($val['items'] as $itemsKey => $itemInfo) {
                            // if ($itemInfo['perm']) {
                            $permUrl = $module . "/" . $menu_item['route'] . "." . $itemInfo['route'];
                            $isAuthPerm = cs($permUrl);

                            if ($isAuthPerm) {
                                $itemsOneRoute = $val['items'][$itemsKey]['route'];
                            }
                            // dump($permUrl, 123, $role);
                            // }
                        }
                        // }
                    }
                    // 操作员权限验证 end

                    if ($itemsOneRouteIsChange) {
                        if (!strexists($itemsOneRoute, 'web.') && $module != 'admin') {
                            $itemsOneRoute = "web." . $itemsOneRoute;
                        }
                        $menu_item['route'] = $module . "/" . $itemsOneRoute;
                    } else {
                        if (strexists($itemsOneRoute, '/')) {
                            $menu_item['route'] = $module . "/" . $menu_item['route'] . "." . $itemsOneRoute;
                        } else {
                            $menu_item['route'] = $module . "/" . $menu_item['route'] . "/" . $itemsOneRoute;
                        }
                    }
                } else {
                    $menu_item['route'] = $module . "/" . $menu_item['route'] . "/index";
                }
                # 设置一级目录路由 end

                if ($full) {
                    $menu_item['url'] = getSiteRoot() . $menu_item['route'];
                }

                // 主菜单权限验证 登录会报错
                if (cm($menu_item['route'])) {
                    $return_menu[] = $menu_item;
                }
            }
            unset($val);

            if (!empty($submenu)) {
                $menuRoute = $controller;

                if ($parentMenuIsChange) {
                    $menuRoute = $parentMenuRoute;
                }

                # 是否存在多级目录
                $isMoreDir = false;
                if (count(explode(".", $controller)) == 3 && !$parentMenuIsChange) {
                    $index = strripos($menuRoute, ".", 0);
                    $menuRoute = substr($menuRoute, 0, $index);
                    $isMoreDir = true;
                }

                if (!empty($submenu['items'])) {
                    $submenuIsActive = false;
                    foreach ($submenu['items'] as $i => $child) {
                        if (!empty($child['active'])) {
                            $submenuIsActive = true;
                        }

                        // 操作员权限验证 start
                        if (!in_array($role, ['founder', 'manager', 'owner'])) {
                            if ($child['perm']) {
                                $controllerArr = explode(".", $controller);
                                $permUrl = $module . "/" . $controllerArr[0] . "." . $controllerArr[1] . "." . $child['route'];
                                $currentUrl = $module . "/" . $controller . "/" . $action;
                                $isAuthPerm = cs($permUrl);

                                // 权限不足提示 start
                                if ($permUrl == $currentUrl && !$isAuthPerm) {
                                    exit('Your permission is insufficient !');
                                }
                                // 权限不足提示 end

                                if (!$isAuthPerm) {
                                    unset($submenu['items'][$i]);
                                    continue;
                                }
                            }
                        }
                        // 操作员权限验证 end

                        $actionTmp = $action;

                        # 如果是二级目录补全路径 start
                        if ($isMoreDir) {
                            $controllerName = explode('.', $controller)[2];
                            $actionTmp = $controllerName . "/" . $actionTmp;
                        }
                        # 如果是二级目录补全路径 end

                        $actionTmpArr = explode("/", $actionTmp);
                        $isUpdate = strexists($actionTmp, '/add') || strexists($actionTmp, '/edit') || strexists($actionTmp, '/post');

                        # 二级目录
                        if (empty($child['items'])) {
                            $return_menu_child = [
                                'title'  => $child['title'],
                                'route'  => $child['route'],
                                'active' => $child['active'] ?? 0,
                                'url'    => $child['url'] ?? null,
                            ];

                            if (!$submenuIsActive && strexists(strtolower(str_replace("_", "", $return_menu_child['route'])), $actionTmpArr[0]) || ((strexists($return_menu_child['route'], 'main') || strpos($return_menu_child['route'], '/') === false) && in_array($actionTmp, ['add', 'edit', 'post']))) {
                                if (strtolower(str_replace("_", "", $return_menu_child['route'])) != $actionTmp && !in_array($actionTmp, ['add', 'edit', 'post']) && !strexists($actionTmp, '/add') && !strexists($actionTmp, '/edit') && !strexists($actionTmp, '/post')) {
                                    $return_menu_child['active'] = 0;
                                    if ($actionTmp == $return_menu_child['route']) {
                                        $return_menu_child['active'] = 1;
                                    }
                                } else {
                                    $menuRouteTmp = str_replace("web.", "", $return_menu_child['route']);
                                    $menuRouteTmpArray = explode("/", $menuRouteTmp);
                                    $controllerNameTmp = StringUtil::camelize($menuRouteTmpArray[0]);

                                    $pageTitle = $return_menu_child['title'];

                                    if ($isUpdate) {
                                        if (strexists($actionTmp, '/add')) {
                                            $pageTitle = str_replace("列表", "", $pageTitle) . "添加";
                                        } else {
                                            if (strexists($actionTmp, '/edit')) {
                                                $pageTitle = str_replace("列表", "", $pageTitle) . "编辑";
                                            } else {
                                                $pageTitle = str_replace("列表", "", $pageTitle) . "更新";
                                            }
                                        }
                                    }

                                    if (strtolower($controllerNameTmp) == $actionTmpArr[0]) {
                                        $return_menu_child['active'] = $parentMenuIsChange ? 0 : 1;
                                    } else {
                                        if (in_array($controllerNameTmp, ['list', 'main', 'index']) && in_array($actionTmpArr[0], ['add', 'post', 'edit'])) {
                                            $return_menu_child['active'] = 1;
                                        }
                                    }
                                }
                            } else {
                                if ($return_menu_child['route'] == $actionTmp) {
                                    $return_menu_child['active'] = 1;
                                }
                            }

                            if ($isMoreDir || $parentMenuIsChange) {
                                $routeLen = 0;
                                if (!empty($return_menu_child['route'])) {
                                    $routeLen = count(explode("/", $return_menu_child['route']));
                                }

                                if ($routeLen == 1) {
                                    $return_menu_child['route'] = $module . "/" . $menuRoute . "/" . $return_menu_child['route'];
                                } else {
                                    $return_menu_child['route'] = $module . "/" . $menuRoute . "." . $return_menu_child['route'];
                                }
                            } else {
                                $return_menu_child['route'] = $module . "/" . $menuRoute . "/" . $return_menu_child['route'];
                            }

                            if ($full) {
                                if ($return_menu_child['url']) {
                                    if (!strexists($return_menu_child['url'], 'web.') && $module != 'admin') {
                                        $return_menu_child['url'] = "web." . $return_menu_child['url'];
                                    }
                                    $return_menu_child['url'] = getSiteRoot() . $module . "/" . $return_menu_child['url'];
                                } else {
                                    $return_menu_child['url'] = getSiteRoot() . $return_menu_child['route'];
                                }
                            }

                            // 子菜单权限验证
                            if (cs($return_menu_child['route'])) {
                                $return_submenu['items'][] = $return_menu_child;
                            }
                        } else {
                            # 三级目录 向下展开
                            $return_menu_child = [
                                'title'  => $child['title'],
                                'items'  => [],
                                'active' => 1, // 默认展开所有子菜单
                            ];

                            foreach ($child['items'] as $ii => $three) {
                                $return_submenu_three = [
                                    'title'  => $three['title'],
                                    'url'    => $three['url'] ?? null,
                                    'active' => 0,
                                ];

                                if (!empty($three['route'])) {
                                    $return_submenu_three['route'] = $three['route'];
                                } else {
                                    $return_submenu_three['route'] = $child['route'];
                                }

                                if (strlen($return_submenu_three['route']) > 0 && $return_submenu_three['route'][0] === '/') {
                                    $return_submenu_three['route'] = $module . "/" . $menuRoute . $return_submenu_three['route'];
                                } else {
                                    $return_submenu_three['route'] = $module . "/" . $menuRoute . "." . $return_submenu_three['route'];
                                }

                                if (strexists($return_submenu_three['route'], $action)) {
                                    $currentRoute = strtolower(str_replace("_", "", $return_submenu_three['route']));
                                    $currentRouteArray = explode("/", $currentRoute) ?? [];
                                    if (in_array($controller, $currentRouteArray)) {
                                        $return_submenu_three['active'] = 1; // 是否选中
                                    }
                                }

                                if ($return_submenu_three['active'] == 0 && !empty($actionTmpArr) && in_array($actionTmpArr[1], ['add', 'edit', 'post'])) {
                                    if ($actionTmpArr[0] . "/main" == $three['route'] || (strtolower($actionTmpArr[0]) . "/main" == strtolower($three['route']))) {
                                        $return_submenu_three['active'] = 1; // 是否选中
                                    }
                                }

                                if ($full) {
                                    if ($return_submenu_three['url']) {
                                        if (!strexists($return_submenu_three['url'], 'web.') && $module != 'admin') {
                                            $return_submenu_three['url'] = "web." . $return_submenu_three['url'];
                                        }
                                        $return_submenu_three['url'] = getSiteRoot() . $module . "/" . $return_submenu_three['url'];
                                    } else {
                                        $return_submenu_three['url'] = getSiteRoot() . $return_submenu_three['route'];
                                    }
                                }

                                if (cs($return_submenu_three['route'])) {
                                    $return_menu_child['items'][] = $return_submenu_three;
                                }
                            }

                            $return_submenu['items'][] = $return_menu_child;
                            unset($ii, $three);
                        }

                    }

                }
            }
        }

        return [
            'menu'      => $return_menu,
            'submenu'   => $return_submenu,
            'pageTitle' => $pageTitle,
        ];
    }
}