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

    public function __construct($uniacid, $isDevelop = null, $clientUrl = null)
    {
        $this->uniacid = $uniacid;

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
            $this->accountSettings = $this->settingsController->getAccountSettings($uniacid, 'settings');
            $aiDriveSets = $this->accountSettings['aidrive'] ?? [];
            $this->accessKeyId = $aiDriveSets['accessKeyId'] ?? '';
            $this->accessKeySecret = $aiDriveSets['accessKeySecret'] ?? '';
            $this->isTest = $aiDriveSets['isDevelop'] ?? false;
            if ($this->isTest) {
                $this->clientUrl = $clientUrl ?? ($aiDriveSets['clientUrl'] ?? $this->testClientUrl);
            } else {
                $this->clientUrl = $clientUrl ?? ($aiDriveSets['clientUrl'] ?? $this->clientUrl);
            }
        }
    }

    // 获取模型和平台
    public function getModelAndPlatform()
    {
        return $this->doHttpGet("/ai/getModelPlatform");
    }

    // 获取平台列表
    public function getPlatformList()
    {
        return $this->doHttpGet("/ai/getPlatform");
    }

    // 根据平台获取模型
    public function getModelByPlatform($platform = null)
    {
        return $this->doHttpGet("/ai/getModelByPlatform/{$platform}");
    }

    // 生成视频
    public function doVideo($params = [])
    {
        $postData = [
            "authParams"       => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
            ],
            "videoModelParams" => [
                "modelPlatform" => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "siliconflow",
                "modelName"     => !empty($params['modelName']) ? $params['modelName'] : "Wan-AI/Wan2.1-I2V-14B-720P-Turbo",
            ],
            "videoParams"      => [
                "model"           => !empty($params['model']) ? $params['model'] : "Wan-AI/Wan2.1-I2V-14B-720P-Turbo",
                "prompt"          => !empty($params['prompt']) ? $params['prompt'] : "", // 帮我生成一个猫咪在阳光下奔跑的图片
                "negative_prompt" => !empty($params['negative_prompt']) ? $params['negative_prompt'] : "", // 不出现月光和人类
                "image_size"      => !empty($params['image_size']) ? $params['image_size'] : "", // 1280x720
                "image"           => !empty($params['image']) ? $params['image'] : "", // https://cdn.translate.alibaba.com/r/wanx-demo-1.png
                "seed"            => !empty($params['seed']) ? $params['seed'] : "658943",
            ],
            "callbackUrl"      => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "", // 回调地址
            "params"           => !empty($params['params']) ? $params['params'] : ['user_id' => 0, 'uniacid' => 0, 'chat_key' => ''] // 回调地址携带参数 user_id 必传
        ];
        // requestId
        return $this->doHttpPost("/ai/tool/video", $postData);
    }

    // 查询视频生成进度
    public function queryVideo($requestId)
    {
        return $this->doHttpGet("/ai/tool/get/video/{$requestId}");
    }

    // 知识库添加
    public function addKnowledgeAsync($params)
    {
        $postData = [
            "authParams"      => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
            ],
            "fileParamsList"  => !empty($params['fileParamsList']) ? $params['fileParamsList'] : [], // 文件列表 fileId，fileUrl，fileName
            "embeddingParams" => [
                'modelName'       => !empty($params['modelName']) ? $params['modelName'] : "BAAI/bge-m3",
                'modelPlatform'   => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "siliconflow",
                'knowledgeBaseId' => !empty($params['knowledgeBaseId']) ? $params['knowledgeBaseId'] : "123",
                'maxToken'        => !empty($params['maxToken']) ? $params['maxToken'] : "2000000000",
                "callbackUrl"     => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "", // 回调地址
                "params"          => !empty($params['params']) ? $params['params'] : ['user_id' => 0, 'uniacid' => 0, 'chat_key' => ''] // 回调地址携带参数 user_id 必传
            ],
        ];
        return $this->doHttpPost("/ai/async/embedding/doVector/fileUrlList", $postData);
    }

    // 删除知识库
    public function deleteKnowledge($params = [])
    {
        $postData = [
            "authParams"      => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
            ],
            "knowledgeBaseId" => !empty($params['knowledgeBaseId']) ? $params['knowledgeBaseId'] : "123",
            "modelName"       => !empty($params['modelName']) ? $params['modelName'] : "BAAI/bge-m3",
            "modelPlatform"   => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "siliconflow",
            "fileId"          => !empty($params['fileId']) ? $params['fileId'] : "",
        ];
        return $this->doHttpPost("/ai/embedding/delete", $postData);
    }

    // 文生图
    public function doImage($params = [])
    {
        $postData = [
            "authParams"         => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
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
            "callbackUrl"        => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "", // 回调地址
            "params"             => !empty($params['params']) ? $params['params'] : ['user_id' => 0, 'uniacid' => 0, 'chat_key' => ''] // 回调地址携带参数 user_id 必传
        ];
        return $this->doHttpPost("/ai/tool/text2image", $postData);
    }

    // 图片向量化
    public function imageEmbedding($params = [])
    {
        $postData = [
            "authParams"         => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
            ],
            "imageParams"        => !empty($params['imageParams']) ? $params['imageParams'] : [], // 图片地址 imageId,imageUrl,imageName
            "imageModelPlatform" => [
                "imageModelPlatform" => !empty($params['imageModelPlatform']) ? $params['imageModelPlatform'] : "aliyun",
                "imageModelName"     => !empty($params['imageModelName']) ? $params['imageModelName'] : "qwen2.5-vl-32b-instruct",
            ],
            "embeddingParams"    => [
                'modelName'       => !empty($params['modelName']) ? $params['modelName'] : "qllama/bge-m3",
                'modelPlatform'   => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "ollama",
                "knowledgeBaseId" => !empty($params['knowledgeBaseId']) ? $params['knowledgeBaseId'] : "123",
                "maxToken"        => !empty($params['maxToken']) ? $params['maxToken'] : "2000000000",
                "callbackUrl"     => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "", // 回调地址
                "params"          => !empty($params['params']) ? $params['params'] : ['user_id' => 0, 'uniacid' => 0, 'chat_key' => ''] // 回调地址携带参数 user_id 必传
            ],
        ];
        return $this->doHttpPost("/ai/tool/imageEmbedding", $postData);
    }

    /**
     * 翻译
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
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
            ],
            "translationModelParams" => [
                "sessionId"     => "XSTL" . RandomUtil::random(10),
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

    // AI会话_只返回内容_小程序专用
    // event:message
    // data:{"reasoningContent":"","content":"响应"}
    public function chatMini($question, $params = [])
    {
        // 设置流式响应头
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no'); // 对于Nginx服务器很重要
        ob_implicit_flush(true);
        ob_end_flush();

        // 原始接口配置
        $targetUrl = $this->clientUrl . '/ai/chat/sendMessageReturnOnlyContent';

        // 准备转发数据
        $postData = [
            "authParams"          => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
            ],
            "messageParams"       => [
                "sessionId" => !empty($params['sessionId']) ? $params['sessionId'] : "XSCM" . RandomUtil::random(10), // 生成唯一会话ID
                "type"      => !empty($params['type']) ? $params['type'] : "USER",
                "content"   => $question
            ],
            "chatParams"          => [
                "modelName"     => !empty($params['modelName']) ? $params['modelName'] : "DeepSeek-V3", // 模型名称
                "modelPlatform" => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "siliconflow", // 模型平台
                "contextNumber" => !empty($params['contextNumber']) ? $params['contextNumber'] : 5, // 上下文数量
                "temperature"   => !empty($params['temperature']) ? $params['temperature'] : 0.8, // 随机性
                "prompt"        => !empty($params['prompt']) ? $params['prompt'] : "", // 提示词
                "maxTokens"     => !empty($params['maxTokens']) ? $params['maxTokens'] : 2000000000, // 最大生成长度
                "callbackUrl"   => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "" // 回调地址
            ],
            "embeddingParamsList" => !empty($params['embeddingParamsList']) ? $params['embeddingParamsList'] : []
        ];

        // 创建CURL请求处理流式响应
        $ch = curl_init($targetUrl);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => false, // 重要：不要返回字符串
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($postData),
            CURLOPT_TIMEOUT        => 0, // 无超时限制
            CURLOPT_WRITEFUNCTION  => function ($curl, $data) {
                // 直接输出接收到的数据块
                echo $data;
                flush(); // 立即刷新输出缓冲区
                return strlen($data); // 必须返回已处理的数据长度
            },
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        try {
            // 执行请求并转发数据
            curl_exec($ch);

            // 检查cURL错误
            if (curl_errno($ch)) {
                $this->sendError('cURL Error: ' . curl_error($ch));
            }

            // 传输完成
            $this->sendComplete();
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        } finally {
            curl_close($ch);
        }

        exit;
    }

    // AI会话2.3_返回内容(完整内容)
    // event:message
    // data:{"promptToken":739,"promptMoney":"","completionTokens":150,"completionMoney":"","totalToken":889,"totalMoney":"","type":"ASSISTANT","contentText":"\" →","reasoningContentText":"","params":"","createTime":"","retrievedDocumentList":[],"finished":false}
    public function chat($question, $params = [])
    {
        // 设置流式响应头
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no'); // 对于Nginx服务器很重要
        ob_implicit_flush(true);
        ob_end_flush();

        // 原始接口配置
        $targetUrl = $this->clientUrl . '/ai/chat/sendMessage';

        // 准备转发数据
        $postData = [
            "authParams"          => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
            ],
            "messageParams"       => [
                "sessionId" => !empty($params['sessionId']) ? $params['sessionId'] : "XSCH" . RandomUtil::random(10), // 生成唯一会话ID
                "type"      => !empty($params['type']) ? $params['type'] : "USER",
                "content"   => $question
            ],
            "chatParams"          => [
                "modelName"     => !empty($params['modelName']) ? $params['modelName'] : "DeepSeek-V3", // 模型名称
                "modelPlatform" => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "siliconflow", // 模型平台
                "contextNumber" => !empty($params['contextNumber']) ? $params['contextNumber'] : 5, // 上下文数量
                "temperature"   => !empty($params['temperature']) ? $params['temperature'] : 0.8, // 随机性
                "prompt"        => !empty($params['prompt']) ? $params['prompt'] : "", // 提示词
                "maxTokens"     => !empty($params['maxTokens']) ? $params['maxTokens'] : 2000000000, // 最大生成长度
                "callbackUrl"   => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "" // 回调地址
            ],
            "embeddingParamsList" => !empty($params['embeddingParamsList']) ? $params['embeddingParamsList'] : []
        ];

        // 创建CURL请求处理流式响应
        $ch = curl_init($targetUrl);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => false, // 重要：不要返回字符串
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($postData),
            CURLOPT_TIMEOUT        => 0, // 无超时限制
            CURLOPT_WRITEFUNCTION  => function ($curl, $data) {
                // 直接输出接收到的数据块
                echo $data;
                flush(); // 立即刷新输出缓冲区
                return strlen($data); // 必须返回已处理的数据长度
            },
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        try {
            // 执行请求并转发数据
            curl_exec($ch);

            // 检查cURL错误
            if (curl_errno($ch)) {
                $this->sendError('cURL Error: ' . curl_error($ch));
            }

            // 传输完成
            $this->sendComplete();
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        } finally {
            curl_close($ch);
        }

        exit;
    }

    // AI会话，纯文本
    public function chatText($question, $params = [])
    {
        $postData = [
            "authParams"          => [
                "accessKeyId"     => $this->accessKeyId,
                "accessKeySecret" => md5($this->accessKeyId . $this->accessKeySecret),
                // "username"     => "zishuai",
                // "password" => "fb860f536151057762df854d35465cf3",
            ],
            "messageParams"       => [
                "type"      => !empty($params['type']) ? $params['type'] : "USER",
                "content"   => $question,
                "sessionId" => !empty($params['sessionId']) ? $params['sessionId'] : "XSCT" . RandomUtil::random(10), // 随机会话ID
            ],
            "chatParams"          => [
                "modelPlatform" => !empty($params['modelPlatform']) ? $params['modelPlatform'] : "siliconflow", // 模型平台
                "modelName"     => !empty($params['modelName']) ? $params['modelName'] : "deepSeek-V3", // 模型名称
                "contextNumber" => !empty($params['contextNumber']) ? $params['contextNumber'] : 3, // 上下文数量
                "prompt"        => !empty($params['prompt']) ? $params['prompt'] : "", // 提示词
                "maxTokens"     => !empty($params['maxTokens']) ? $params['maxTokens'] : "",
                "temperature"   => !empty($params['temperature']) ? $params['temperature'] : 0.7, //
                "callbackUrl"   => !empty($params['callbackUrl']) ? $params['callbackUrl'] : "", // 回调地址
                "params"        => !empty($params['params']) ? $params['params'] : ['user_id' => 0, 'uniacid' => 0, 'chat_key' => ''] // 回调地址携带参数 user_id 必传
            ],
            "embeddingParamsList" => !empty($params['embeddingParamsList']) ? $params['embeddingParamsList'] : [],
        ];
        return $this->doHttpPostJson("/ai/chat/sendMessageAll", $postData);
        // return $this->doHttpPostJson("https://58dbccf9.r16.cpolar.top/api/admin/ai/chat/sendMessageAll", $postData);
    }

    // 发送http post json请求
    private function doHttpPostJson($url, $postData)
    {
        $response = RequestUtil::httpPostJson(StringUtil::strexists($url, 'http') ? $url : $this->clientUrl . $url, $postData, true);
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