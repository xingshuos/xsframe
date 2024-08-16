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

use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\UserWrapper;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\util\ErrorUtil;
use think\captcha\facade\Captcha;
use think\facade\Db;

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

        // 负载均衡服务器下会导致验证无效，需要将session放入redis存储 TODO
        if (!Captcha::check($verify)) {
            show_json(0, '验证码输入错误!');
        }

        $hostUrl = $this->request->header()['host'];
        $resultInfo = UserWrapper::login($username, $password, $hostUrl);

        if (ErrorUtil::isError($resultInfo)) {
            show_json(-1, $resultInfo['msg']);
        }

        $userInfo = $resultInfo['userInfo'];
        $url = $resultInfo['url'];

        Db::name("sys_users")->where(['id' => $userInfo['id']])->update(['logintime' => time(), 'lastip' => $this->request->ip()]);
        show_json(1, ['url' => $url]);
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