<?php


namespace xsframe\wrapper;

use xsframe\facade\service\WechatServiceFacade;
use think\Request;

class WechatWrapper
{
    public $get = null;
    private $request;
    protected $settingsController;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->get     = $this->request->param();

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
        }

        $this->init();
    }

    private function init()
    {
        $action = $this->get['action'] ?? 'success';

        switch ($action) {
            case 'getSignPackage':
                $uniacid = $this->get['uniacid'];
                $url     = $this->get['url'];

                $account = $this->settingsController->getAccountSettings($uniacid);

                $appId  = $account['settings']['wechat']['appid'];
                $secret = $account['settings']['wechat']['secret'];

                $result = WechatServiceFacade::getSignPackage($appId, $secret, $url);
                $result['debug'] = false;
                $this->success($result);
                break;
            default:
                self::success();
        }
    }

    protected function success($data = [], $code = "200", $message = 'success')
    {
        $code    = $data['code'] ?? $code;
        $message = $data['msg'] ?? $message;
        $data    = $data['data'] ?? $data;

        $retData = [
            'code' => (string)$code,
            'msg'  => $message,
            'data' => $data
        ];
        exit(json_encode($retData));
    }
}