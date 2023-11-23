<?php


// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\base;

use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\SettingsWrapper;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\util\FileUtil;
use think\App;
use think\Request;
use think\route\dispatch\Controller;

abstract class BaseController extends Controller
{
    protected $uniacid;
    protected $request;
    protected $header;
    protected $params;
    protected $pIndex;
    protected $pSize;
    protected $app;
    protected $siteRoot;
    protected $view;

    protected $module;
    protected $controller;
    protected $action;

    protected $url;

    protected $userId;
    protected $iaRoot;
    protected $moduleSiteRoot;
    protected $moduleAttachUrl;
    protected $moduleIaRoot;
    protected $authkey;
    protected $expire;
    protected $settingsController;
    protected $attachment;

    protected $websiteSets = [];
    protected $account = [];
    protected $accountSetting = [];
    protected $moduleInfo = [];
    protected $moduleSetting = [];

    public function __construct(Request $request, App $app)
    {
        $this->request = $request;
        $this->header = $request->header();
        $this->app = $app;
        $this->params = $this->request->param();

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
        }

        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    protected function _initialize()
    {
        $this->authkey = "xsframe_";
        $this->expire = 3600 * 24 * 10; // 10天有效期

        $this->view = $this->app['view'];

        $this->pIndex = $this->request->param('page') ?? 1;
        $this->pSize = $this->request->param('size') ?? 10;

        $this->siteRoot = request()->domain();
        $this->iaRoot = str_replace("\\", '/', dirname(dirname(dirname(dirname(__FILE__)))));
        $this->module = app('http')->getName();

        $this->moduleSiteRoot = $this->siteRoot . "/" . $this->module;
        $this->moduleAttachUrl = $this->siteRoot . "/app/" . $this->module;
        $this->moduleIaRoot = $this->iaRoot . "/app/" . $this->module;

        $this->controller = strtolower($this->request->controller());
        $this->action = strtolower($this->request->action());
        $this->url = $this->request->url();

        $this->checkCors();
        $this->autoLoad();

        # 验证是否独立数据库应用 start
        if (!is_file($this->moduleIaRoot . "/config/database.php")) {
            $this->getDefaultSets();
        } else {
            $this->getUniacid();
        }
        # 验证是否独立数据库应用 end
    }

    // 解决重复提交与跨域问题
    private function checkCors()
    {
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With , X-Access-Token");
        header('Access-Control-Allow-Methods: PUT,POST,GET,DELETE,OPTIONS');

        if ($this->request->method() === "OPTIONS") {
            exit();
        }
    }

    protected function autoLoad()
    {
        $path = $this->iaRoot . '/extend/xsframe/function';
        $files = FileUtil::getDir($path);

        if (!empty($files)) {
            foreach ($files as $fileInfo) {
                if (is_file($fileInfo['path'])) {
                    if (!class_exists($fileInfo['path'])) {
                        include_once $fileInfo['path'];
                    }
                }
            }
        }
    }

    // 加载默认配置信息
    protected function getDefaultSets()
    {
        # 系统网站设置
        $this->websiteSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::WEBSITE_KEY);

        # 设置模块
        $uniacid = $this->getUniacid();

        # 附件设置
        $attachmentSets = $this->settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);

        # 项目信息
        $this->account = $this->settingsController->getAccountSettings($uniacid);
        $this->accountSetting = $this->settingsController->getAccountSettings($uniacid, SysSettingsKeyEnum::SETTING_KEY);

        # 模块信息
        $this->moduleInfo = $this->settingsController->getModuleInfo($this->module);
        $this->moduleSetting = $this->getModuleSettings($uniacid);

        if ($uniacid > 0) {
            $accountSets = $this->account['settings'] ?? [];
            if (!empty($accountSets) && !empty($accountSets['attachment'])) {
                $attachmentSets['remote'] = $accountSets['attachment']['remote'];
            }
        }

        $this->attachment = $attachmentSets;
    }

    // 获取模块配置信息
    protected function getModuleSettings($uniacid)
    {
        $moduleSetting = $this->settingsController->getModuleSettings(null, $this->module, $uniacid);
        if (!empty($moduleSetting)) {
            if (!empty($moduleSetting['basic']))
                $moduleSetting['basic']['logo'] = tomedia($moduleSetting['basic']['logo']);
            if (!empty($moduleSetting['share']))
                $moduleSetting['share']['imageUrl'] = tomedia($moduleSetting['share']['imageUrl']);
        }
        return $moduleSetting;
    }

    // 获取项目uniacid
    protected function getUniacid()
    {
        $uniacid = isset($this->params['uniacid']) ? $this->params['uniacid'] : ($_GET['i'] ?? ($_COOKIE['uniacid'] ?? 0));

        # 校验域名路由 start
        if (empty($uniacid)) {
            if ($this->module != 'admin') {
                $accountHost = new AccountHostWrapper();
                $uniacid = $accountHost->getAccountHostUniacid($_SERVER['HTTP_HOST']);
            }
        }
        # 校验域名路由 end

        # 这里是后台首页默认的uniacid 不计入cookie
        if (empty($uniacid)) {
            $uniacid = $this->websiteSets['uniacid'] ?? 0;
        } else {
            # 缓存当前所选商户uniacid
            if (!empty($uniacid)) {
                isetcookie('uniacid', $uniacid);
            }
        }

        // 如果是平台端 商户uniacid设置为空 TODO
        if ($this->module == 'admin') {
            $uniacid = 0;
        }

        $this->uniacid = $uniacid;
        return $uniacid;
    }

    // 校验插件路由
    protected function checkAppRouter()
    {
        $url = $this->url;

        $appControllerPath = $this->iaRoot . "/app/{$this->module}/controller";
        // $appControllerDirs = FileUtil::dirsOnes($appControllerPath);

        $urlPath = $appControllerPath . "/{$this->controller}/" . ucfirst($this->action) . ".php";

        # 校验控制器是否存在
        if (!is_file($urlPath)) {
            if (is_mobile()) {
                $this->controller = 'mobile';
            } else {
                $this->controller = 'web';
            }
        }

        if (count(explode("/", $url)) > 2) {
            $url = "{$this->controller}.{$this->action}";
        } else {
            $url = "{$this->module}/{$this->controller}.{$this->action}";
        }

        return $url;
    }
}