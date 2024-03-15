<?php

namespace app\xs_cloud\controller\api;

use app\xs_cloud\facade\service\MemberServiceFacade;
use app\xs_cloud\facade\service\MemberSiteServiceFacade;
use xsframe\base\ApiBaseController;

class Base extends ApiBaseController
{
    protected $memberInfo = null;
    protected $hostIp = null;
    protected $hostUrl = null;
    protected $phpVersion = null;

    public function _api_initialize()
    {
        parent::_api_initialize();

        $this->checkToken();
    }

    // 监测站点信息
    protected function checkToken(): void
    {
        $key = $this->params['key']; // 站点ID
        $token = $this->params['token']; // 通信秘钥

        if (empty($key) || empty($token)) {
            $this->error("请查看站点设置中“站点ID”和“通信秘钥”是否配置");
        }

        $memberInfo = MemberServiceFacade::getInfo(['token' => $token]);
        if (empty($memberInfo)) {
            $this->error("当前站点不存在或已暂停服务,请联系官方客服处理");
        }
        if ($memberInfo['status'] == 0) {
            $this->error("您的站点已暂停服务,请联系官方客服处理");
        }
        if ($memberInfo['is_black'] == 1) {
            $this->error("您的站点已被系统列入黑名单,请联系官方客服处理");
        }
        $memberSiteInfo = MemberSiteServiceFacade::getInfo(['key' => $key]);
        if (empty($memberSiteInfo) || $memberSiteInfo['mid'] != $memberInfo['id']) {
            $this->error("请查看站点设置中“站点ID”和“通信秘钥”是否配置正确");
        }

        $this->memberInfo = $memberInfo;
        $this->hostIp = $this->params['host_ip'] ?? '';
        $this->hostUrl = $this->params['host_url'] ?? '';
        $this->phpVersion = $this->params['php_version'] ?? '';
    }

}
