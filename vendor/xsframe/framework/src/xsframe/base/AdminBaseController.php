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

use think\facade\Cache;
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
        $clientName = $this->params['client'] ?? 'web';
        $isFileUpload = strtolower($this->controller) == 'file';

        /*$clientName && $clientName != 'web' TODO zhaoxin 注释图片限定参数 */

        if ($isFileUpload && $this->params['uid'] && $this->params['module'] != 'admin') {
            $this->userId = intval($this->params['uid']);
            // 调用用户是否登录 TODO 这里是个漏洞，没有校验用户是否登录（1.每个应用重做上传 2.统一登录 3.提供调用登录的接口校验 推荐第三种方式）
        } else {

            $loginResult = UserWrapper::checkUser();
            if (!empty($loginResult) && !empty($loginResult['adminSession'])) {
                $this->adminSession = $loginResult['adminSession'];
                $this->userId = $this->adminSession['uid'];
                $uniacid = $this->adminSession['uniacid'];

                if (!empty($uniacid)) {
                    $this->uniacid = $uniacid;
                    $_COOKIE['uniacid'] = $uniacid;
                }
            }

            if (strtolower($this->controller) != 'login') {
                if (!$loginResult['isLogin']) {
                    header('location: ' . url('/admin/login'));
                    exit();
                }
            } else {
                if ($loginResult['isLogin'] && (!in_array($this->action, ['logout', 'verify']))) {
                    $url = UserWrapper::getLoginReturnUrl($loginResult['adminSession']['role'], $loginResult['adminSession']['uid']);
                    header('location: ' . $url);
                    exit();
                }
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
        return show_json($code, $data);
    }

    protected function error($data = [], $code = 0)
    {
        return show_json($code, $data);
    }

    protected function successMsg($message, $code = 1)
    {
        return $this->success(["message" => $message], $code);
    }

    protected function errorMsg($message, $code = 0)
    {
        return $this->error(["message" => $message], $code);
    }

    private function getDefaultVars($params = null): array
    {
        if (!empty($this->moduleSetting['basic']) && !empty($this->moduleSetting['basic']['name'])) {
            $this->moduleInfo = array_merge(!empty($this->moduleInfo) ? $this->moduleInfo : [], $this->moduleSetting['basic']);
        }

        $var = [];
        $var['module'] = $this->module;
        $var['controller'] = $this->controller;
        $var['action'] = $this->action;
        $var['uniacid'] = $this->uniacid;
        $var['clientServiceName'] = $this->clientServiceName;
        $var['_GPC'] = $this->params;
        $var['uid'] = $this->userId;
        $var['url'] = $this->url;
        $var['siteRoot'] = $this->siteRoot;
        $var['moduleSiteRoot'] = $this->moduleSiteRoot;
        $var['moduleAttachUrl'] = $this->moduleAttachUrl;
        $var['token'] = RandomUtil::random(8);
        $var['isSystem'] = $this->isSystem;
        $var['userInfo'] = $this->adminSession;
        $var['websiteSets'] = $this->websiteSets;

        $menusList = [];
        if (!empty($this->adminSession) && is_array($this->adminSession)) {
            $menusList = MenuWrapper::getMenusList($this->adminSession['role'], $this->module, $this->controller, $this->action);
        }
        $var['menusList'] = $menusList;
        $var['pageTitle'] = empty($menusList['pageTitle']) ? $this->websiteSets['name'] : $menusList['pageTitle'];

        # 收缩菜单
        $var['foldNav'] = intval($_COOKIE["foldnav"] ?? 0);

        $var['account'] = $this->account;
        $var['moduleInfo'] = $this->moduleInfo;
        $var['attachUrl'] = getAttachmentUrl() . "/";
        $var['isLogin'] = $this->isLogin;

        # 选中系统菜单
        $var['selSystemNav'] = intval($_COOKIE[$this->module . "_systemnav"] ?? 0);
        $var['selSystemNavUrl'] = $this->getSelSystemNavUrl();

        # 菜单通知点
        $var['oneMenuNoticePoint'] = Cache::get($this->module . "_" . SysSettingsKeyEnum::ADMIN_ONE_MENU_NOTICE_POINT) ?? [];
        $var['twoMenuNoticePoint'] = Cache::get($this->module . "_" . SysSettingsKeyEnum::ADMIN_TWO_MENU_NOTICE_POINT) ?? [];

        if (!empty($params)) {
            $var = array_merge($var, $params);
        }

        return $var;
    }

    private function getSelSystemNavUrl()
    {
        $uniacid = $this->uniacid;
        $selSystemNavUrl = $_COOKIE[$this->module . "_systemnavurl"] ?? null;

        if (empty($selSystemNavUrl)) {
            $selSystemNavUrl = url('admin/system/index', ['i' => $uniacid, 'module' => $this->module]);
        } else {
            $urlParts = parse_url($selSystemNavUrl);

            // 解析查询字符串为数组
            parse_str($urlParts['query'], $queryParams);

            // 检查i参数是否存在，并且是否和uniacid不同
            if ((isset($queryParams['i']) && $queryParams['i'] != $uniacid) || empty($queryParams['i'])) {
                // 替换i参数的值
                $queryParams['i'] = $uniacid;

                // 增加module参数
                $queryParams['module'] = $queryParams['module'] ?: $this->module;

                // 重新构建查询字符串
                $newQuery = http_build_query($queryParams);

                // 重新组合URL
                $selSystemNavUrl = $urlParts['path'] . '?' . $newQuery;
            }

        }

        return strval($selSystemNavUrl);
    }
}