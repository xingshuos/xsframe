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
use xsframe\util\StringUtil;
use xsframe\wrapper\SettingsWrapper;

class ZiShuService
{
    private $accessKeyId = null;
    private $accessKeySecret = null;
    private $userToken = null;

    private $testClientUrl = "https://zishu.souxue.cc/zishu";
    private $clientUrl = "https://ai.zishuju.cn/newAi";
    private $isTest = false;

    private $uniacid = 2;
    private $username = "zishuai";
    private $password = "fb860f536151057762df854d35465cf3";

    public function __construct($userToken = "", $isDevelop = false, $clientUrl = null, $uniacid = 2)
    {
        $this->uniacid = $uniacid;
        $this->userToken = $userToken;
        $this->isTest = $isDevelop;

        if ($this->isTest) {
            $this->clientUrl = $clientUrl ?? $this->testClientUrl;
        } else {
            $this->clientUrl = $clientUrl ?? $this->clientUrl;
        }
    }

    // 获取紫薯的用户信息
    public function getUserInfo()
    {
        $url = $this->clientUrl . '/api/user/user_info';
        $response = RequestUtil::httpPost($url, ['uniacid' => $this->uniacid], ['authorization' => $this->userToken]);
        $result = json_decode($response, true);
        return $result['data']['user_info'];
    }

    // 文生图
    public function doImage($params = [])
    {
        $postData = [
            "authParams"         => [
                // "accessKeyId"     => "3030303030303131",
                // "accessKeySecret" => "f42e3017f6e53bca3fcd924fcf0d837b",
                "username"        => $this->username,
                "password"        => $this->password,
            ],
            "diagramModelParams" => [
                "modelPlatform" => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "siliconflow",
                "modelName"     => !empty($params['modelName']) ? $params['modelName'] : "Kwai-Kolors/Kolors",
            ],
            "diagramParams"      => [
                "model"               => !empty($params['model']) ? $params['model'] : "Kwai-Kolors/Kolors",
                "prompt"              => !empty($params['prompt']) ? $params['prompt'] : "", // 生成一个美女的图片
                "negative_prompt"     => !empty($params['negative_prompt']) ? $params['negative_prompt'] : "", // 不要多人
                "image_size"          => !empty($params['image_size']) ? $params['image_size'] : "", // 1024x1024
                "batch_size"          => !empty($params['batch_size']) ? $params['batch_size'] : "1", //
                "seed"                => !empty($params['seed']) ? $params['seed'] : "4999999999",
                "num_inference_steps" => !empty($params['num_inference_steps']) ? $params['num_inference_steps'] : "20",
                "guidance_scale"      => !empty($params['guidance_scale']) ? $params['guidance_scale'] : "7.5",
            ],
            "callbackUrl"        => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "magna", // 回调地址
            "params"             => !empty($params['params']) ? $params['params'] : ['user_id' => 0, 'uniacid' => 0, 'chat_key' => ''] // 回调地址携带参数 user_id 必传
        ];
        // dd($postData);
        $retJson = $this->doHttpPostJson("/ai/tool/diagram", $postData);
        $result = json_decode($retJson,true);
        return $result;
    }

    // 发送http post json请求
    private function doHttpPostJson($url, $postData)
    {
        $postUrl = StringUtil::strexists($url, 'http') ? $url : $this->clientUrl . $url;
        $response = RequestUtil::httpPostJson($postUrl, $postData, true);
        $result = json_decode($response, true);
        if ($result['code'] != '0000') {
            throw new ApiException($result['message']);
        }
        return $result['data'];
    }

    // 发送http get 请求
    private function doHttpGet($url, $extra = [])
    {
        $response = RequestUtil::httpGet(StringUtil::strexists($url, 'http') ? $url : $this->clientUrl . $url, $extra);
        $result = json_decode($response, true);
        if ($result['code'] != '0000') {
            throw new ApiException($result['message']);
        }
        return $result['data'];
    }

    // 发送完成信号
    private function sendComplete()
    {
        echo "event: complete\n";
        echo "data: " . json_encode(['status' => 'completed']) . "\n\n";
        flush();
    }

    // 发送错误
    private function sendError($message)
    {
        echo "event: error\n";
        echo "data: " . json_encode(['error' => $message]) . "\n\n";
        flush();
    }

}