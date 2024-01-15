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

        # 检测是否配置独立域名 start
        $url = $this->request->header()['host'];

        $accountHostWrapper = new AccountHostWrapper();
        $domainMappingArr = $accountHostWrapper->getAccountHost();

        if (!empty($domainMappingArr) && !empty($domainMappingArr[$url])) {
            $uniacid = $domainMappingArr[$url]['uniacid'];
            $module = $domainMappingArr[$url]['default_module'];
            $moduleSetting = $this->settingsController->getModuleSettings(null, $module, $uniacid);

            if( empty($moduleSetting) ){
                // TODO 项目名称也应该可以配置官网信息
                $accountSetting = $this->accountSetting;
            }else{
                $websiteSets = array_merge($websiteSets, (array)$moduleSetting['website'], (array)$moduleSetting['basic']);
            }
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

        if (!Captcha::check($verify)) {
            show_json(0, '验证码输入错误!');
        }

        $resultInfo = UserWrapper::login($username, $password);
        if (ErrorUtil::isError($resultInfo)) {
            show_json(-1, $resultInfo['msg']);
        }

        $userInfo = $resultInfo['userInfo'];
        $url = $resultInfo['url'];

        Db::name("sys_users")->where(['id' => $userInfo['id']])->update(['logintime' => time(), 'lastip' => $this->request->ip()]);
        show_json(1, array('url' => $url));
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
        return Captcha::create();
    }
}