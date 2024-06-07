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

namespace xsframe\base;

use xsframe\wrapper\MenuWrapper;
use xsframe\wrapper\UserWrapper;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\traits\AdminTraits;
use xsframe\util\RandomUtil;
use think\App;
use think\Request;
use think\facade\View;

abstract class AdminBaseController extends BaseController
{
    protected $clientBaseType = 'admin';
    protected $isSystem = false;
    protected $adminSession = [];
    protected $_GPC = [];

    public function __construct(Request $request, App $app)
    {
        parent::__construct($request, $app);
        if (method_exists($this, '_admin_initialize')) {

            $this->checkAuth();
            if ($this->module == 'admin') {
                $this->isSystem = true;
            }

            $this->_admin_initialize();
        }
    }

    /**
     * 引入后台通用的traits
     */
    use AdminTraits;

    // 初始化
    public function _admin_initialize()
    {

    }

    // 校验用户登录
    protected function checkAuth()
    {
        if ($this->controller != 'login') {
            $loginResult = UserWrapper::checkUser();

            if (!$loginResult['isLogin']) {
                header('location: ' . url('/admin/login'));
                exit();
            }

            $this->adminSession = $loginResult['adminSession'];
            $this->userId = $this->adminSession['uid'];
            $uniacid = $this->adminSession['uniacid'];

            if (!empty($uniacid)) {
                $this->uniacid = $uniacid;
                $_COOKIE['uniacid'] = $uniacid;
            }
        } else {
            $loginResult = UserWrapper::checkUser();
            if ($loginResult['isLogin'] && (!in_array($this->action, ['logout', 'verify']))) {
                $url = UserWrapper::getLoginReturnUrl($loginResult['adminSession']['role'], $loginResult['adminSession']['uid']);
                header('location: ' . $url);
                exit();
            }
        }
    }

    // 引入后端模板
    protected function template($name, $var = null)
    {
        # 解决 使用门面调用会报 未定义数组索引 的错误警告
        error_reporting(E_ALL ^ E_NOTICE);
        $var = $this->getDefaultVars($var);
        return view($name, $var);
    }

    /**
     * 生成静态文件
     * @throws \Exception
     */
    protected function buildHtml($htmlFile = '', $htmlPath = '', $templateFile = '', $templateVars = []): string
    {
        $templateVars = $this->getDefaultVars($templateVars);

        $content = View::fetch($templateFile, $templateVars);
        $htmlPath = !empty($htmlPath) ? $htmlPath : './appTemplate/';
        $htmlFile = $htmlPath . $htmlFile . '.' . config('view.view_suffix');
        $File = new \think\template\driver\File();
        $File->write($htmlFile, $content);
        return $content;
    }

    protected function success($data = [], $code = 1)
    {
        show_json($code, $data);
    }

    protected function error($data = [], $code = 0)
    {
        show_json($code, $data);
    }

    private function getDefaultVars($params = null): array
    {
        if (!empty($this->moduleSetting['basic']) && !empty($this->moduleSetting['basic']['name'])) {
            $this->moduleInfo = array_merge(!empty($this->moduleInfo) ? $this->moduleInfo : [], $this->moduleSetting['basic']);
        }

        $menusList = MenuWrapper::getMenusList($this->adminSession['role'], $this->module, $this->controller, $this->action);

        $var = [];
        $var['module'] = $this->module;
        $var['controller'] = $this->controller;
        $var['action'] = $this->action;
        $var['uniacid'] = $this->uniacid;
        $var['_GPC'] = $this->params;
        $var['uid'] = $this->userId;
        $var['url'] = $this->url;
        $var['siteRoot'] = $this->siteRoot;
        $var['moduleSiteRoot'] = $this->moduleSiteRoot;
        $var['moduleAttachUrl'] = $this->moduleAttachUrl;
        $var['token'] = RandomUtil::random(8);
        $var['isSystem'] = $this->isSystem;
        $var['menusList'] = $menusList;
        $var['pageTitle'] = empty($menusList['pageTitle']) ? $this->websiteSets['name'] : $menusList['pageTitle'];
        $var['userInfo'] = $this->adminSession;
        $var['websiteSets'] = $this->websiteSets;

        # 收缩菜单
        $var['foldNav'] = intval($_COOKIE["foldnav"] ?? 0);

        # 选中系统菜单
        $var['selSystemNav'] = intval($_COOKIE[$this->module . "_systemnav"]);
        $var['selSystemNavUrl'] = strval(empty($_COOKIE[$this->module . "_systemnavurl"]) ? url('admin/system/index') : $_COOKIE[$this->module . "_systemnavurl"]);

        $var['account'] = $this->account;
        $var['moduleInfo'] = $this->moduleInfo;
        $var['attachUrl'] = getAttachmentUrl() . "/";
        $var['isLogin'] = $this->isLogin;

        if (!empty($params)) {
            $var = array_merge($var, $params);
        }

        return $var;
    }
}