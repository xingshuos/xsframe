<?php

namespace app\xs_cloud\controller\web;

use xsframe\base\AdminBaseController;
use xsframe\util\RandomUtil;
use think\facade\Db;

class Sets extends AdminBaseController
{
    public function index()
    {
        // 默认我的账号
        return redirect("/{$this->module}/web.sets/cover");
    }

    // 我的账号
    public function cover()
    {
        // $coverUrl = $this->siteRoot . "/{$this->module}/mobile.index?i=" . $this->uniacid;
        $coverUrl = $this->siteRoot;
        return $this->template('cover', ['coverUrl' => $coverUrl]);
    }

    // 我的账号
    public function profile()
    {
        if ($this->request->isPost()) {
            $username    = $this->params['username'];
            $password    = $this->params['password'];
            $newPassword = $this->params['newPassword'];

            $adminSession = $this->adminSession;
            $userInfo     = Db::name('sys_users')->field("id,username,password,salt")->where(['id' => $adminSession['uid']])->find();
            $password     = md5($password . $userInfo['salt']);
            if (md5($password . $userInfo['salt']) != $adminSession['hash']) {
                show_json(0, "原始密码错误，请重新输入");
            }
            if (strlen($newPassword) < 6) {
                show_json(0, "请输入不小于6位数的密码");
            }
            if (empty($username)) {
                show_json(0, "登录账号不能为空");
            }

            $salt = RandomUtil::random(6);
            Db::name('sys_users')->where(['id' => $userInfo['id']])->update(['username' => $username, 'password' => md5($newPassword . $salt), 'salt' => $salt]);
            show_json(1, ['message' => "密码已修改请重新登录", 'url' => url('/yq_shoot/web.login')]);
        }

        return $this->template('profile');
    }

    // 当前项目应用配置信息
    public function module()
    {
        $moduleSettings = $this->settingsController->getModuleSettings(null, $this->module, $this->uniacid);
        if ($this->request->isPost()) {
            $settingsData = $this->params['data'] ?? [];

            if (!empty($settingsData['contact'])) {
                $settingsData['contact']['about'] = htmlspecialchars_decode($settingsData['contact']['about']);
            }
            if (!empty($settingsData['user'])) {
                $settingsData['user']['agreement'] = htmlspecialchars_decode($settingsData['user']['agreement']);
            }

            $settingsData = array_merge($moduleSettings, $settingsData);

            if (!empty($settingsData)) {
                $data['settings'] = serialize($settingsData);
                Db::name('sys_account_modules')->where(["uniacid" => $this->uniacid, 'module' => $this->module])->update($data);
                # 更新缓存
                $this->settingsController->reloadModuleSettings($this->module, $this->uniacid);
            }
            $this->success(array("url" => webUrl("module", ['tab' => str_replace("#tab_", "", $this->params['tab'])])));
        }

        $var = [
            'moduleSettings' => $moduleSettings
        ];
        return $this->template('module', $var);
    }

    // 当前项目公共配置信息
    public function account()
    {
        $accountSettings = $this->settingsController->getAccountSettings($this->uniacid, 'settings');

        if ($this->request->isPost()) {
            $settingsData = $this->params['data'] ?? [];
            $settingsData = array_merge($accountSettings, $settingsData);

            if (!empty($settingsData)) {
                $data['settings'] = serialize($settingsData);
                Db::name('sys_account')->where(["uniacid" => $this->uniacid])->update($data);
                # 更新缓存
                $this->settingsController->reloadAccountSettings($this->uniacid);
            }
            $this->success(array("url" => webUrl("account", ['tab' => str_replace("#tab_", "", $this->params['tab'])])));
        }

        $var = [
            'accountSettings' => $accountSettings
        ];
        return $this->template('account', $var);
    }
}
