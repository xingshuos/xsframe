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
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;
use xsframe\wrapper\SettingsWrapper;

class ZiShuAiService
{
    private $accessKeyId = null;
    private $accessKeySecret = null;

    private $testClientUrl = "https://19002-frp.wang1278.top/api/admin";
    private $clientUrl = "https://ai.zishuju.cn/newAi";
    private $isTest = false;

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

    /**
     * @param $content
     * @param $targetLang
     * @return mixed
     * "promptTokens": 30,
     * "promptMoney": 0.00006,
     * "completionTokens": 10,
     * "completionMoney": 0.00008,
     * "totalTokens": 40,
     * "totalMoney": 0.00014,
     * "type": "ASSISTANT",
     * "contentText": "I didn't laugh after watching this video. ",
     * "createTime": "2025-06-23 09:32:39",
     * "params": "",
     * "finished": true
     * @throws ApiException
     */
    public function translate($content, $targetLang = "English")
    {
        $postData = [
            "authParams"             => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => $this->accessKeySecret,
            ],
            "translationModelParams" => [
                "sessionId"     => "XS" . RandomUtil::random(10),
                "modelPlatform" => "aliyun",
                "callbackUrl"   => "",
            ],
            "translationParams"      => [
                "model"               => "qwen-mt-turbo",
                "messages"            => [
                    [
                        "role"    => "user",
                        "content" => $content
                    ],
                ],
                "translation_options" => [
                    "source_lang" => "auto",
                    "target_lang" => "English"
                ]
            ]
        ];
        // dd($this->doHttpPostJson('/ai/tool/generate', $postData));
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