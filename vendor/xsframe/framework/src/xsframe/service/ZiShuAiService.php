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

namespace xsframe\service;

use think\facade\Env;
use xsframe\base\BaseService;
use xsframe\exception\ApiException;
use xsframe\util\RequestUtil;
use xsframe\wrapper\SettingsWrapper;

class ZiShuAiService
{
    private $accessKeyId = null;
    private $accessKeySecret = null;
    private $clientUrl = "https://ai.zishuju.cn/newAi";

    private $uniacid = null;
    private $settingsController;
    private $accountSettings;

    private $username = "xingshu";
    private $password = "f6bdfc5c6fa296866c748f21a50145bf";

    public function __construct($uniacid)
    {
        $this->uniacid = $uniacid;

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
            $this->accountSettings = $this->settingsController->getAccountSettings($uniacid, 'settings');
            $aiDriveSets = $this->accountSettings['aidrive'] ?? [];
            $this->accessKeyId = $aiDriveSets['accessKeyId'] ?? '';
            $this->accessKeySecret = $aiDriveSets['accessKeySecret'] ?? '';
        }
    }

    // 生成用户
    public function generateUser()
    {
        $url = $this->clientUrl . '/ai/user/generateUser';
        $postData = [
            'authParams' => [
                'username' => $this->username,
                'password' => md5($this->username . $this->password)
            ]
        ];
        return $this->doHttpPostJson($url, $postData);
    }

    // 获取用户基本信息
    public function getUser()
    {
        $url = $this->clientUrl . '/ai/user/getUser';
        $postData = [
            'authParams' => [
                'accessKeyId'     => $this->accessKeyId,
                'accessKeySecret' => md5($this->accessKeyId . $this->accessKeySecret)
            ]
        ];
        return $this->doHttpPostJson($url, $postData);
    }

    private function doHttpPostJson($url, $postData)
    {
        $response = RequestUtil::httpPostJson($url, $postData, true);
        $result = json_decode($response, true);
        if ($result['code'] != '0000') {
            throw new ApiException($result['message']);
        }
        return $result['data'];
    }
}