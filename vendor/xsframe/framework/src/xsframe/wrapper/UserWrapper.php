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

use think\facade\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\enum\UserRoleKeyEnum;
use xsframe\facade\service\DbServiceFacade;
use xsframe\util\ErrorUtil;
use xsframe\util\StringUtil;
use xsframe\util\LicenseUtil;

class UserWrapper
{
    private static $session_key = SysSettingsKeyEnum::ADMIN_USER_KEY;

    // 校验登录操作（设定一个操作时长，如果长时间不操作就登出）
    public static function checkLogin()
    {
        if (!empty($noOpTime)) {

        }
    }

    // 校验登录
    public static function checkUser($url = null)
    {
        $isLogin = true;
        $adminSession = $_COOKIE[self::$session_key] ?? '';
        if (!empty($adminSession)) {
            $adminSession = json_decode(authcode($adminSession), true);

            $usersInfoKey = self::$session_key . "_" . $adminSession['username'];
            $usersInfo = Cache::get($usersInfoKey);

            if (empty($usersInfo) || ($usersInfo && $adminSession['hash'] != md5($usersInfo['password'] . $usersInfo['salt']))) {
                $usersInfo = Db::name('sys_users')->field("id,username,password,role,salt")->where(['id' => $adminSession['uid']])->find();
                Cache::set($usersInfoKey, $usersInfo);
            }

            if (!(is_array($usersInfo)) || ($adminSession['hash'] != md5($usersInfo['password'] . $usersInfo['salt']))) {
                $isLogin = false;
            }
        } else {
            $isLogin = false;
        }

        if (!$isLogin) {
            isetcookie(self::$session_key, false, -100);
        }

        return [
            'isLogin'      => $isLogin,
            'adminSession' => $adminSession,
        ];
    }

    // 用户登录
    public static function login($username, $password, $hostUrl = '')
    {
        $userInfo = Db::name('sys_users')->field("id,password,salt,role,status,end_time")->where(['username' => $username])->find();
        if (empty($userInfo)) {
            return ErrorUtil::error(-1, "该登录用户未找到");
        }

        if ($userInfo['status'] == 0) {
            return ErrorUtil::error(-1, "该用户已被禁用，请联系管理员处理");
        }

        if ($userInfo['end_time'] > 0 && $userInfo['end_time'] <= TIMESTAMP) {
            return ErrorUtil::error(-2, "该用户登录权限已过期，请联系管理员处理");
        }

        $password = md5($password . $userInfo['salt']);
        if ($userInfo['password'] != $password) {
            return ErrorUtil::error(-1, "该用户登录密码错误");
        }

        $url = self::getLoginReturnUrl($userInfo['role'], $userInfo['id'], $hostUrl);

        if (ErrorUtil::isError($url)) {
            show_json(-1, $url['msg']);
        }

        $cookie = [];
        $cookie['uid'] = $userInfo['id'];
        $cookie['username'] = $username;
        $cookie['role'] = $userInfo['role'];
        $cookie['hash'] = md5($userInfo['password'] . $userInfo['salt']);
        $cookie['uniacid'] = self::getUserUniacid($userInfo['id']);

        $cookie['perm_user'] = Db::name("sys_account_perm_user")->field("id,uid,realname,mobile,roleid,mid,status")->where(['uid' => $userInfo['id']])->find() ?? [];
        $cookie['perm_role'] = Db::name("sys_account_perm_role")->field("id,pid,rolename,role_key,status")->where(['id' => $cookie['perm_user']['roleid']])->find() ?? [];

        $session = authcode(json_encode($cookie), 'encode');
        isetcookie(self::$session_key, $session, 7 * 86400, true);

        return [
            'userInfo' => $userInfo,
            'url'      => $url,
        ];
    }

    // 获取当前应用第一个子菜单当做首页
    public static function getModuleOneUrl($moduleName, $isAdmin = false, $role = null, $userId = null)
    {
        $rootPath = App::getRootPath();
        $modulePath = $rootPath . "app/" . $moduleName;

        $appMaps = Config::get('app.app_map') ?? [];
        $appKey = array_search($moduleName, $appMaps);
        $moduleName = !empty($appKey) ? $appKey : $moduleName;

        $url = $moduleName;
        if (!is_file($modulePath . "/controller/Index.php") || $isAdmin) {
            $moduleMenuConfigFile = $modulePath . "/config/menu.php";

            if (is_file($moduleMenuConfigFile)) {
                $menuConfig = include($moduleMenuConfigFile);

                // 根据操作员访问权限获取菜单 start
                $oneMenus = [];
                if ($role && $userId && $role == UserRoleKeyEnum::OPERATOR_KEY) {
                    $permUserInfo = DbServiceFacade::name('sys_account_perm_user')->getInfo(['uid' => $userId]);
                    if ($permUserInfo['is_limit'] == 1) {
                        // ★ 合并角色权限 + 用户额外权限（与 PermWrapper.check 逻辑一致）
                        $role_perms = [];
                        $role_app_perms = [];
                        if (!empty($permUserInfo['roleid'])) {
                            $roleInfo = DbServiceFacade::name('sys_account_perm_role')->getInfo(['id' => $permUserInfo['roleid']]);
                            $role_perms = array_filter(explode(',', $roleInfo['perms'] ?? ''));
                            $role_app_perms = array_filter(explode(',', $roleInfo['app_perms'] ?? ''));
                        }
                        $user_perms = array_filter(explode(',', $permUserInfo['perms'] ?? ''));
                        $user_app_perms = array_filter(explode(',', $permUserInfo['app_perms'] ?? ''));
                        $all_perms = array_unique(array_merge($role_perms, $user_perms));
                        $all_app_perms = array_unique(array_merge($role_app_perms, $user_app_perms));

                        if (in_array($moduleName, $all_app_perms)) {
                            // 有该应用级权限，返回第一个菜单
                            $oneMenus = array_slice($menuConfig, 0, 1);
                        } else {
                            // 在合并后的操作权限中查找属于当前应用的菜单项
                            $prefixToRemove = "{$moduleName}.web.";
                            foreach ($all_perms as $part) {
                                if (strpos($part, $prefixToRemove) !== false) {
                                    $key = substr($part, strlen($prefixToRemove));
                                    $menuInfo = $menuConfig[$key] ?? null;
                                    if (!empty($menuInfo)) {
                                        if (!empty($menuInfo['items']) && !empty($menuInfo['items'][0]) && !empty($menuInfo['items'][0]['items'])) {
                                            // 三级目录的情况
                                            $oneMenus[$key] = $menuInfo['items'][0];
                                        } else {
                                            $oneMenus[$key] = $menuInfo;
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        $oneMenus = array_slice($menuConfig, 0, 1);
                    }
                } else {
                    $oneMenus = array_slice($menuConfig, 0, 1);
                }
                // 根据操作员访问权限获取菜单 end

                $oneMenusKeys = array_keys($oneMenus);

                $actionUrl = $oneMenus[$oneMenusKeys[0]]['items'][0]['route'];
                if (empty($actionUrl)) {
                    if (!empty($oneMenus[$oneMenusKeys[0]]['items'][0]['url'])) {
                        $actionUrl = $oneMenus[$oneMenusKeys[0]]['items'][0]['url'];
                        if (!StringUtil::strexists($actionUrl, "web.")) {
                            $actionUrl = "web." . $actionUrl;
                        }
                        $url = url("/" . $moduleName . "/" . $actionUrl);
                    } else {
                        $actionUrl = $oneMenus[$oneMenusKeys[0]]['route'];
                        if (empty($actionUrl)) {
                            $actionUrl = "/main";
                        } else {
                            $isAction = count(explode("/", $actionUrl)) == 1;
                            $actionUrl = ($isAction ? "/" : ".") . $actionUrl;
                        }

                        $controllerUrl = $oneMenusKeys[0];
                        if (!StringUtil::strexists($controllerUrl, "web.")) {
                            $controllerUrl = "web." . $controllerUrl;
                        }
                        $url = url("/" . $moduleName . "/" . $controllerUrl . $actionUrl);
                    }
                } else {
                    if (StringUtil::strexists($actionUrl, "/")) {
                        $actionUrl = "." . $actionUrl;
                    } else {
                        $actionUrl = "/" . $actionUrl;
                    }
                    $controllerUrl = $oneMenusKeys[0];
                    if (!StringUtil::strexists($controllerUrl, "web.")) {
                        $controllerUrl = "web." . $controllerUrl;
                    }
                    $url = url("/" . $moduleName . "/" . $controllerUrl . $actionUrl);
                }
            } else {
                $url = url('/' . $moduleName);
            }
        }

        return $url;
    }

    // 通过用户id获取默认插件
    public static function getModuleInfoByUserId($userId): array
    {
        $moduleName = null;
        $uniacid = 0;

        // 先从 sys_account_perm_user 获取 uniacid（兜底，如果 sys_account_users 无记录）
        $permUserInfo = Db::name('sys_account_perm_user')->where(['uid' => $userId])->find();
        if (!empty($permUserInfo)) {
            $uniacid = $permUserInfo['uniacid'];
        }

        // 获取用户关联的账号记录
        $usersAccountInfo = Db::name('sys_account_users')->field("id,uniacid,module")->where(['user_id' => $userId])->find();

        if (!empty($usersAccountInfo)) {
            if (!empty($usersAccountInfo['uniacid'])) {
                $uniacid = $usersAccountInfo['uniacid'];
            }
            $moduleName = $usersAccountInfo['module'];

            // 检查记录的应用是否已安装
            $isInstall = Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'module' => $moduleName, 'deleted' => 0])->count();
            if (empty($isInstall)) {
                $moduleName = null;
            }
        }

        // 如果没有找到可用应用，基于权限合并查找第一个有权限的应用
        if (empty($moduleName) && !empty($uniacid)) {
            // 重新查询当前 uniacid 下的权限记录
            $permUserInfo = Db::name('sys_account_perm_user')->where(['uid' => $userId, 'uniacid' => $uniacid])->find();

            if (!empty($permUserInfo)) {
                // ★ 合并角色权限 + 用户额外权限（与 PermWrapper.check 逻辑一致）
                $role_perms = [];
                $role_app_perms = [];
                if (!empty($permUserInfo['roleid'])) {
                    $roleInfo = Db::name('sys_account_perm_role')->where(['id' => $permUserInfo['roleid']])->find();
                    $role_perms = array_filter(explode(',', $roleInfo['perms'] ?? ''));
                    $role_app_perms = array_filter(explode(',', $roleInfo['app_perms'] ?? ''));
                }
                $user_perms = array_filter(explode(',', $permUserInfo['perms'] ?? ''));
                $user_app_perms = array_filter(explode(',', $permUserInfo['app_perms'] ?? ''));

                // 提取所有有权访问的应用 key
                $all_perms = array_unique(array_merge($role_perms, $user_perms));
                $all_app_perms = array_unique(array_merge($role_app_perms, $user_app_perms));
                $appKeys = array_filter($all_perms, fn($item) => strpos($item, '.') === false);
                $appKeys = array_unique(array_merge(array_values($appKeys), $all_app_perms));

                // 查找该账号下第一个有权限且已安装的应用
                if (!empty($appKeys)) {
                    $defaultModuleInfo = Db::name('sys_account_modules')
                        ->field("id,uniacid,module")
                        ->where(['uniacid' => $uniacid, 'deleted' => 0])
                        ->whereIn('module', $appKeys)
                        ->order("is_default desc")
                        ->find();
                    if (!empty($defaultModuleInfo)) {
                        $moduleName = $defaultModuleInfo['module'];
                        if (!empty($usersAccountInfo)) {
                            Db::name('sys_account_users')->where(['id' => $usersAccountInfo['id']])->update(['module' => $moduleName]);
                        }
                    }
                }
            }
        }

        return [
            'module'  => $moduleName,
            'uniacid' => $uniacid
        ];
    }

    // 退出登录
    public static function logout($url = null)
    {
        isetcookie(self::$session_key, false, -100);
        Cache::delete(self::$session_key);

        if (empty($url)) {
            $url = '/admin/login';
        }
        return redirect($url);
    }

    public static function getUserUniacid($userId)
    {
        $uniacid = Db::name("sys_account_users")->where(['user_id' => $userId])->value('uniacid');
        return $uniacid;
    }

    // 获取登录后跳转地址
    public static function getLoginReturnUrl($role, $userId, $hostUrl = null)
    {
        if ($role == UserRoleKeyEnum::OWNER_KEY) {
            $url = '/admin/home/welcome';
        } else {
            $moduleInfo = self::getModuleInfoByUserId($userId);
            $uniacid = $moduleInfo['uniacid'];
            $moduleName = $moduleInfo['module'];

            if (empty($moduleName)) {
                return ErrorUtil::error(-1, "暂无管理功能权限");
            }

            $realUrl = self::getModuleOneUrl($moduleName, true, $role, $userId);
            $url = webUrl(str_replace('.html', "", $realUrl), ['i' => $uniacid]);
        }
        return $url;
    }
}