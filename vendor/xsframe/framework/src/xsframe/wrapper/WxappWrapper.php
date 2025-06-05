<?php


namespace xsframe\wrapper;

use xsframe\facade\service\WechatServiceFacade;
use think\Request;

class WxappWrapper
{
    public $get = null;
    private $request;
    protected $settingsController;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->get = $this->request->param();

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
        }

        $this->init();
    }

    private function init()
    {
        $action = $this->get['action'] ?? 'success';
        $uniacid = $this->get['uniacid'] ?? 0;
        if (!empty($uniacid)) {
            $account = $this->settingsController->getAccountSettings($uniacid);
            $wxappSets = $account['settings']['wxapp'] ?? [];
        }
    }

}