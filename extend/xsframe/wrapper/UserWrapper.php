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
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\enum\UserRoleKeyEnum;
use xsframe\util\ErrorUtil;
use xsframe\util\StringUtil;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;

class UserWrapper
{
    private static $session_key = SysSettingsKeyEnum::ADMIN_USER_KEY;

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
    public static function login($username, $password)
    {
        $userInfo = Db::name('sys_users')->field("id,password,salt,role,status")->where(['username' => $username])->find();
        if (empty($userInfo)) {
            return ErrorUtil::error(-1, "该登录用户未找到");
        }

        if ($userInfo['status'] == 0) {
            return ErrorUtil::error(-1, "该用户已被禁用，请联系管理员处理");
        }

        $password = md5($password . $userInfo['salt']);
        if ($userInfo['password'] != $password) {
            return ErrorUtil::error(-1, "该用户登录密码错误");
        }

        $url = self::getLoginReturnUrl($userInfo['role'], $userInfo['id']);

        if (ErrorUtil::isError($url)) {
            show_json(-1, $url['msg']);
        }

        $cookie = array();
        $cookie['uid'] = $userInfo['id'];
        $cookie['username'] = $username;
        $cookie['role'] = $userInfo['role'];
        $cookie['hash'] = md5($userInfo['password'] . $userInfo['salt']);
        $cookie['uniacid'] = self::getUserUniacid($userInfo['id']);
        $session = authcode(json_encode($cookie), 'encode');

        isetcookie(self::$session_key, $session, 7 * 86400, true);

        return [
            'userInfo' => $userInfo,
            'url'      => $url,
        ];
    }

    // 获取当前应用第一个子菜单当做首页
    public static function getModuleOneUrl($moduleName)
    {
        $rootPath = App::getRootPath();
        $moduleMenuConfigFile = $rootPath . "app/" . $moduleName . "/config/menu.php";

        $appMaps = Config::get('app.app_map') ?? [];
        $appKey = array_search($moduleName, $appMaps);

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

            $moduleName = !empty($appKey) ? $appKey : $moduleName;
            $url = url("/" . $moduleName . "/" . $oneMenusKeys[0] . $actionUrl);
        } else {
            $moduleName = !empty($appKey) ? $appKey : $moduleName;
            $url = url('/' . $moduleName);
        }
        return $url;
    }

    // 通过用户id获取默认插件
    public static function getModuleNameByUserId($userId)
    {
        $moduleName = null;
        $usersAccountInfo = Db::name('sys_account_users')->field("id,uniacid,module")->where(['user_id' => $userId])->find();
        if (!empty($usersAccountInfo)) {
            $moduleName = $usersAccountInfo['module'];
            $isInstall = Db::name('sys_account_modules')->where(['uniacid' => $usersAccountInfo['uniacid'], 'module' => $moduleName])->count();
            if (empty($moduleName) || empty($isInstall)) {
                $defaultModuleInfo = Db::name("sys_account_modules")->field("id,uniacid,module")->where(['uniacid' => $usersAccountInfo['uniacid']])->order("is_default desc")->find();

                if (!empty($defaultModuleInfo)) {
                    $moduleName = $defaultModuleInfo['module'];
                    Db::name('sys_account_users')->where(['id' => $usersAccountInfo['id']])->update(['module' => $moduleName]);
                }
            }
        }
        return $moduleName;
    }

    // 退出登录
    public static function logout($url = null)
    {
        isetcookie(self::$session_key, false, -100);
        isetcookie('uniacid', false, -100);
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
    public static function getLoginReturnUrl($role, $userId)
    {
        if ($role == UserRoleKeyEnum::OWNER_KEY) {
            $url = url('/admin/home/welcome');
        } else {
            $moduleName = self::getModuleNameByUserId($userId);

            if (empty($moduleName)) {
                return ErrorUtil::error(-1, "暂未开通应用管理");
            }

            $url = self::getModuleOneUrl($moduleName);
        }
        return $url;
    }
}