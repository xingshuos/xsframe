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

        $style = 'login';

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

        return $this->template($style, compact('rememberUsername', 'websiteSets'));
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