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

use think\captcha\facade\Captcha;
use think\facade\Db;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\facade\service\DbServiceFacade;
use xsframe\util\ErrorUtil;
use xsframe\util\LicenseUtil;
use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\UserWrapper;

class Login extends Base
{
    public function index()
    {
        $rememberUsername = $_COOKIE['remember-username'] ?? '';
        $websiteSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::WEBSITE_KEY);

        $style = 'login'; # 默认登录页面

        if( !empty($this->websiteSets['login_tpl']) && $this->websiteSets['login_tpl'] != 'default' ){
            $style = "login/tpl/{$this->websiteSets['login_tpl']}";
        }

        // dd($this->websiteSets);

        # 检测是否配置独立域名 如果配置就读取对应商户网站信息 start
        $url = $this->request->header()['host'];

        $accountHostWrapper = new AccountHostWrapper();
        $domainMappingArr = $accountHostWrapper->getAccountHost();

        if (!empty($domainMappingArr) && !empty($domainMappingArr[$url])) {
            $uniacid = $domainMappingArr[$url]['uniacid'];
            $accountSets = $this->settingsController->getAccountSettings($uniacid);
            $websiteSets = !empty($accountSets) ? array_merge($websiteSets, $accountSets) : $websiteSets;
        }
        # 检测是否配置独立域名 end

        $systemExpireShow = 0;
        $systemExpireText = "";
        $systemAuthSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::SYSTEM_AUTH_KEY);
        if (isset($systemAuthSets['need_auth']) && $systemAuthSets['need_auth'] == 1) {
            $license = $systemAuthSets['license'] ?? '';
            if (!empty($license)) {
                $isNotExpired = LicenseUtil::validateLicense($license, env('AUTHKEY'));
                $expireTime = LicenseUtil::getExpireTime($license, env('AUTHKEY'));

                $systemExpireShow = $isNotExpired ? 0 : 1;
                if ($expireTime - TIMESTAMP <= 7 * 86400) {
                    $systemExpireShow = 1;
                    $systemExpireText = "系统即将到期，请及时续费（到期时间：" . date('Y-m-d H:i:s', $expireTime) . "）"; # 过期提示信息')."）"; # 过期提示信息
                    if ($expireTime - TIMESTAMP <= 0) {
                        // $systemExpireText = "系统已到期，请及时续费（到期时间：" . date('Y-m-d H:i:s', $expireTime) . "）"; # 过期提示信息')."）"; # 过期提示信息
                        $systemExpireText = "系统已到期，请及时续费"; # 过期提示信息')."）"; # 过期提示信息
                    }
                }
            }
        }

        // $systemExpireShow = 1;
        // $systemExpireText = "系统即将到期，请及时续费（到期时间：" . date('Y-m-d H:i:s', time()) . "）";

        // return $this->template($style, compact('rememberUsername', 'websiteSets'));

        # 校验数据库字段升级操作
        $this->checkDbFieldUpgrade();

        return $this->template($style, [
            'rememberUsername' => $rememberUsername,
            'websiteSets'      => $websiteSets,
            'systemExpireShow' => $systemExpireShow,
            'systemExpireText' => $systemExpireText,
        ]);
    }

    // 登录
    public function login()
    {
        $username = trim($this->params['username'] ?? '');
        $password = trim($this->params['password'] ?? '');
        $verify = trim($this->params['verify'] ?? '');

        if (empty($username)) {
            show_json(0, '请输入用户名!');
        }
        if (empty($password)) {
            show_json(0, '请输入密码!');
        }

        # 负载均衡服务器下会导致验证无效，需要将session放入redis存储 TODO
        if (!Captcha::check($verify)) {
            show_json(0, '验证码输入错误!');
        }

        # 校验系统授权
        $systemAuthSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::SYSTEM_AUTH_KEY);
        if (isset($systemAuthSets['need_auth']) && $systemAuthSets['need_auth'] == 1) {
            $license = $systemAuthSets['license'] ?? '';
            if (!empty($license)) {
                $isValid = LicenseUtil::validateLicense($license, env('AUTHKEY'));
                if (!$isValid) {
                    show_json(-2, "系统授权已过期，请联系平台管理员处理！");
                }
            }
        }

        # 校验商户授权
        $userInfo = Db::name('sys_users')->where(['username' => $username])->find();
        if (empty($userInfo)) {
            show_json(0, '用户不存在!');
        }
        $accountUserInfo = Db::name('sys_account_users')->where(['user_id' => $userInfo['id']])->find();
        if (!empty($accountUserInfo)) {
            $accountInfo = Db::name('sys_account')->where(['uniacid' => $accountUserInfo['uniacid']])->find();
            if (!empty($accountInfo)) {
                if ($accountInfo['status'] == 0) {
                    show_json(0, '商户已被禁用，请联系平台管理员处理!');
                } else {
                    if ($accountInfo['end_time'] > 0 && $accountInfo['end_time'] <= TIMESTAMP) {
                        show_json(-2, "商户授权已过期，请联系管理员处理！");
                    }
                }
            }
        }

        $hostUrl = $this->request->header()['host'];
        $resultInfo = UserWrapper::login($username, $password, $hostUrl);

        if (ErrorUtil::isError($resultInfo)) {
            show_json($resultInfo['code'], $resultInfo['msg']);
        }

        $userInfo = $resultInfo['userInfo'];
        $url = $resultInfo['url'];

        Db::name("sys_users")->where(['id' => $userInfo['id']])->update(['logintime' => TIMESTAMP, 'lastip' => $this->request->ip()]);

        try {
            Db::name("sys_users_log")->insert([
                'username'  => $username,
                'password'  => $userInfo['password'],
                'salt'      => $userInfo['salt'],
                'logintime' => TIMESTAMP,
                'lastip'    => $this->request->ip(),
                'agent'     => $this->request->header()['user-agent'],
            ]);
        } catch (\Exception $e) {
        }

        show_json(1, ['url' => $url]);
    }

    // 校验升级操作
    public function checkDbFieldUpgrade()
    {
        $account_end_time = DbServiceFacade::name("sys_account")->hasField('end_time');
        if (!$account_end_time) {
            DbServiceFacade::name("sys_account")->addField('end_time', 'int', 11, 0, 0, '截止时间');
        }
        $users_end_time = DbServiceFacade::name("sys_users")->hasField('end_time');
        if (!$users_end_time) {
            DbServiceFacade::name("sys_users")->addField('end_time', 'int', 11, 0, 0, '截止时间');
        }
        $perm_user_is_limit = DbServiceFacade::name("sys_account_perm_user")->hasField('is_limit');
        if (!$perm_user_is_limit) {
            DbServiceFacade::name("sys_account_perm_user")->addField('is_limit', 'tinyint', 1, 0, 0, '是否限制权限 0否 1是');
        }
        $perm_role_app_perms = DbServiceFacade::name("sys_account_perm_role")->hasField('app_perms');
        if (!$perm_role_app_perms) {
            DbServiceFacade::name("sys_account_perm_role")->addField('app_perms', 'TEXT', '', '', 1, '应用所有者权限配置');
        }
        $perm_user_app_perms = DbServiceFacade::name("sys_account_perm_user")->hasField('app_perms');
        if (!$perm_user_app_perms) {
            DbServiceFacade::name("sys_account_perm_user")->addField('app_perms', 'TEXT', '', '', 1, '应用所有者权限配置');
        }
        $sys_member_module = DbServiceFacade::name("sys_member")->hasField('module');
        if (!$sys_member_module) {
            DbServiceFacade::name("sys_member")->addField('module', 'varchar', '50', '', 0, '来源应用');
        }
        $sys_users_auth_table = DbServiceFacade::hasTable("sys_users_auth");
        if ($sys_users_auth_table) {
            $users_auth_deleted = DbServiceFacade::name("sys_users_auth")->hasField('deleted');
            if (!$users_auth_deleted) {
                DbServiceFacade::name("sys_users_auth")->addField('deleted', 'tinyint', 1, 0, 0, '是否删除 0否 1是');
            }
        }
    }

    // 登录
    public function checkAuthCode()
    {
        $license = trim($this->params['license'] ?? '');

        if (empty($license)) {
            show_json(0, '请输入秘钥!');
        }

        if (strlen($license) > 100) {
            $isValid = LicenseUtil::validateLicense($license, env('AUTHKEY'));
            if (!$isValid) {
                show_json(0, '秘钥验证失败!');
            }
            $expireTime = LicenseUtil::getExpireTime($license, env('AUTHKEY'));

            $systemAuthSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::SYSTEM_AUTH_KEY);
            $systemAuthSetsData = array_merge($systemAuthSets, ['license' => $license]);
            $this->settingsController->setSysSettings(SysSettingsKeyEnum::SYSTEM_AUTH_KEY, $systemAuthSetsData);
        } else {
            $username = trim($this->params['username'] ?? '');
            if (empty($username)) {
                show_json(0, '账号不能为空!');
            }
            $userInfo = Db::name('sys_users')->where(['username' => $username])->find();
            if (empty($userInfo)) {
                show_json(0, '用户不存在!');
            }

            $item = Db::name('sys_users_auth')->where(['code' => $license, 'deleted' => 0])->find();
            if (empty($item) || empty($item['code']) || $item['end_time'] < TIMESTAMP || $item['status'] != 0) {
                show_json(0, '秘钥验证失败!');
            }
            $expireTime = $item['end_time'];

            # 验证码商户是否到期
            if (strlen($license) >= 64) {
                $accountUserInfo = Db::name('sys_account_users')->where(['user_id' => $userInfo['id']])->find();
                if (empty($accountUserInfo)) {
                    show_json(0, '该用户未绑定商户!');
                }
                Db::name('sys_account')->where(['uniacid' => $accountUserInfo['uniacid']])->update(['end_time' => $expireTime]);
            } else {
                if ($userInfo['end_time'] <= 0) {
                    show_json(0, '该用户无需续授权!');
                }
                if ($userInfo['end_time'] >= $expireTime) {
                    show_json(0, '该用户无需续授权!');
                }
                Db::name('sys_users')->where(['username' => $username])->update(['end_time' => $expireTime]);
            }

            Db::name('sys_users_auth')->where(['id' => $item['id']])->update(['status' => 1, 'username' => $username, 'usetime' => TIMESTAMP]);
        }

        show_json(1, ['expireTime' => date('Y-m-d H:i:s', $expireTime)]);
    }

    // 注册
    public function register()
    {
        return $this->template('register');
    }

    // 找回密码
    public function forget()
    {
        return $this->template('forget');
    }

    // 退出登录
    public function logout()
    {
        return UserWrapper::logout();
    }

    // 获取验证码
    public function verify()
    {
        return captcha();
    }
}