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

    private $testClientUrl = "https://19002-frp.wang1278.top/api/admin";
    private $clientUrl = "https://ai.zishuju.cn/newAi";
    private $isTest = true;

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
        $postData = [
            'authParams' => [
                'username' => $this->username,
                'password' => $this->password
            ]
        ];
        return $this->doHttpPostJson('/ai/user/generateUser', $postData);
    }

    // 获取用户基本信息
    public function getUser()
    {
        $postData = [
            'authParams' => [
                'accessKeyId'     => $this->accessKeyId,
                'accessKeySecret' => md5($this->accessKeyId . $this->accessKeySecret)
            ]
        ];
        return $this->doHttpPostJson('/ai/user/getUser', $postData);
    }

    // 翻译
    public function translate()
    {
        $postData = [
            "translationModelParams" => [
                "params"        => [],
                "callbackUrl"   => "ullamco in esse",
                "modelPlatform" => "aliyun",
                "sessionId"     => "003"
            ],
            "authParams"             => [
                "username"        => "zishuai",
                "accessKeyId"     => "3030303030303131",
                "accessKeySecret" => "f42e3017f6e53bca3fcd924fcf0d837b",
                "password"        => "fb860f536151057762df854d35465cf3"
            ],
            "translationParams"      => [
                "messages"            => [
                    [
                        "role"    => "user",
                        "content" => "看完这个视频我没有笑"
                    ],
                    [
                        "content" => "看完这个视频我没有笑",
                        "role"    => "user"
                    ],
                    [
                        "role"    => "user",
                        "content" => "看完这个视频我没有笑"
                    ]
                ],
                "model"               => "qwen-mt-turbo",
                "translation_options" => [
                    "source_lang" => "tempor magna",
                    "target_lang" => "voluptate"
                ]
            ]
        ];
        // dd(json_encode($postData));
        return $this->doHttpPostJson('/ai/tool/generate', $postData);
    }

    // 账号充值 ALI_PAY("支付宝"),WX_PAY("微信"),
    public function initiatePay($money = 0.00, $payType = 'ALI_PAY')
    {
        $postData = [
            'authParams'    => [
                'accessKeyId'     => $this->accessKeyId,
                'accessKeySecret' => md5($this->accessKeyId . $this->accessKeySecret)
            ],
            'payMethodEnum' => $payType,
            'money'         => $money,
        ];
        return $this->doHttpPostJson('/ai/pay/initiatePay', $postData);
    }

    private function doHttpPostJson($url, $postData)
    {
        $response = RequestUtil::httpPostJson((!$this->isTest ? $this->clientUrl : $this->testClientUrl) . $url, $postData, true);
        $result = json_decode($response, true);
        if ($result['code'] != '0000') {
            throw new ApiException($result['message']);
        }
        return $result['data'];
    }

}