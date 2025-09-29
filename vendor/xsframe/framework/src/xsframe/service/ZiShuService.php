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
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;
use xsframe\util\StringUtil;
use xsframe\wrapper\SettingsWrapper;
use xsframe\exception\ApiException;

class ZiShuService extends BaseService
{
    private $accessKeyId = null;
    private $accessKeySecret = null;
    private $userToken = null;

    private $testClientUrl = "https://zishu.souxue.cc/zishu";
    private $clientUrl = "https://ai.zishuju.cn/newAi";
    private $isTest = false;

    private $zsUniacid = 2;
    private $username = "zishuai";
    private $password = "fb860f536151057762df854d35465cf3";

    protected function _service_initialize()
    {
        parent::_service_initialize();

        if (empty($userToken)) {
            $userToken = trim(request()->param('ai_token') ?? '');
        }

        $this->userToken = $userToken;

        if ($this->isTest) {
            $this->clientUrl = $this->testClientUrl;
        } else {
            $this->clientUrl = $this->clientUrl;
        }
    }

    // 获取用户ID
    public function getUserId()
    {
        return $this->userToken ? $this->authcode(base64_decode($this->userToken), "DECODE", 'zishu') : 0;
    }

    // 获取紫薯的用户信息
    public function getUserInfo()
    {
        try {
            $url = $this->clientUrl . '/api/user/user_info';
            $response = RequestUtil::httpPost($url, ['uniacid' => $this->zsUniacid], ['authorization' => $this->userToken]);
            $result = json_decode($response, true);
            if ($result != 200 && $result['code'] != 200) {
                throw new ApiException("获取用户信息失败");
            }
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
        return $result['data']['user_info'];
    }

    // 文生图
    public function doImage($params = [])
    {
        $callbackUrl = $this->clientUrl . '/api/user/callback';
        $postData = [
            "authParams"         => [
                // "accessKeyId"     => "3030303030303131",
                // "accessKeySecret" => "f42e3017f6e53bca3fcd924fcf0d837b",
                "username" => $this->username,
                "password" => $this->password,
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
        $result = json_decode($retJson, true);
        return $result;
    }

    // 加解密用户ID
    private function authcode($string, $operation = 'DECODE', $key = 'xsframe', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key != 'xsframe' ? $key : 'xsframe');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $operation = strtoupper($operation);
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = [];
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            try {
                if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                    return substr($result, 26);
                } else {
                    return '';
                }
            } catch (\Exception $exception) {
                return '';
            }

        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
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