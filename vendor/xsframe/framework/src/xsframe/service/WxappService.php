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

use xsframe\base\BaseService;
use xsframe\exception\ApiException;
use xsframe\util\ErrorUtil;
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;
use think\facade\Cache;

class WxappService extends BaseService
{
    private $wxappSet = null;

    protected function _service_initialize()
    {
        parent::_service_initialize();

        if (empty($this->smsSet)) {
            $this->wxappSet = $this->accountSetting['wxapp'] ?? [];
        }
    }

    // 文本内容安全识别
    public function msgSecCheck($checkData = [], $appId = null, $secret = null, $isReload = false)
    {
        if (empty($appId)) {
            $appId = $this->wxappSet['appid'];
            $secret = $this->wxappSet['secret'];
        }

        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);

        $postData = [];
        $postData['content'] = $checkData['content'] ?? ''; // 是 需检测的文本内容，文本字数的上限为2500字，需使用UTF-8编码
        $postData['version'] = $checkData['version'] ?? '2'; // 是 接口版本号，2.0版本为固定值2
        $postData['scene'] = $checkData['scene'] ?? '2'; // 是 场景枚举值（1 资料；2 评论；3 论坛；4 社交日志）
        $postData['openid'] = $checkData['openid'] ?? ''; // 是 用户的openid（用户需在近两小时访问过小程序）
        $postData['title'] = $checkData['title'] ?? ''; // 否 文本标题，需使用UTF-8编码
        $postData['nickname'] = $checkData['nickname'] ?? ''; // 否 用户昵称，需使用UTF-8编码
        $postData['signature'] = $checkData['signature'] ?? ''; // 否 个性签名，该参数仅在资料类场景有效(scene=1)，需使用UTF-8编码

        $data = json_encode($postData);
        $response = RequestUtil::httpPost("https://api.weixin.qq.com/wxa/msg_sec_check?access_token={$token}", $data);
        $result = json_decode($response, true);

        if (!empty($result['errcode'])) {
            throw new ApiException("数据校验失败:" . $result['errmsg']);
        }

        return [
            'detail' => $result['detail'],
            'result' => $result['result'],
        ];
    }

    // 文本内容安全识别
    public function mediaCheckAsync($checkData = [], $appId = null, $secret = null, $isReload = false)
    {
        if (empty($appId)) {
            $appId = $this->wxappSet['appid'];
            $secret = $this->wxappSet['secret'];
        }

        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);

        $postData = [];
        $postData['media_url'] = $checkData['media_url'] ?? ''; // 要检测的图片或音频的url，支持图片格式包括jpg, jpeg, png, bmp, gif（取首帧），支持的音频格式包括mp3, aac, ac3, wma, flac, vorbis, opus, wav
        $postData['media_type'] = $checkData['media_type'] ?? 2; // 1:音频;2:图片
        $postData['version'] = $checkData['version'] ?? 2; // 接口版本号，2.0版本为固定值2
        $postData['scene'] = $checkData['scene'] ?? 1; // 场景枚举值（1 资料；2 评论；3 论坛；4 社交日志）
        $postData['openid'] = $checkData['openid'] ?? ''; // 用户的openid（用户需在近两小时访问过小程序）

        $data = json_encode($postData);
        $response = RequestUtil::httpPost("https://api.weixin.qq.com/wxa/media_check_async?access_token={$token}", $data);
        $result = json_decode($response, true);

        if (!empty($result['errcode'])) {
            throw new ApiException("数据校验失败:" . $result['errmsg']);
        }

        return [
            'trace_id' => $result['trace_id'],
        ];
    }

    // 获取小程序openid
    public function getOpenid($appId, $secret, $code)
    {
        $response = RequestUtil::httpGet('https://api.weixin.qq.com/sns/jscode2session?appid=' . $appId . '&secret=' . $secret . '&js_code=' . $code . '&grant_type=authorization_code');
        $result = json_decode($response, true);

        if (isset($result['errcode'])) {
            throw new ApiException("微信登录失败:" . $result['errmsg']);
        }

        return [
            'session_key' => $result['session_key'],
            'openid'      => $result['openid'],
        ];
    }

    // 获取手机号(无法正常调用，报access_token错误，但是并没有错的)
    public function getPhoneNumber($appId, $secret, $code, $isReload = false)
    {
        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);

        $data = [];
        $data['code'] = $code;

        $postUrl = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$token}";
        $response = RequestUtil::httpPostJson($postUrl, $data);
        $result = json_decode($response, true);

        # token过期
        if (intval($result['errcode']) == 40001 && !$isReload) {
            $this->getPhoneNumber($appId, $secret, $code, true);
        } else {
            if (intval($result['errcode']) != 0) {
                throw new ApiException($result['errmsg']);
            }
        }

        // 获取手机号信息失败
        if (empty($result['phone_info'])) {
            throw new ApiException($result['errmsg']);
        }

        return (array)$result['phone_info'];
    }

    // 发送模板消息通知
    public function sendTplNotice($appId, $secret, $openid, $templateId, $postData, $url = '', $topColor = '#FF683F', $isReload = false)
    {
        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if ($token) {
            $data = [];
            $data['touser'] = $openid;
            $data['template_id'] = trim($templateId);
            $data['page'] = trim($url);
            $data['miniprogram_state'] = "formal"; // 跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
            $data['lang'] = "zh_CN";
            $data['data'] = $postData;

            $data = json_encode($data);
            $postUrl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$token}";

            $response = RequestUtil::httpPost($postUrl, $data);
            $result = json_decode($response, true);

            # token过期
            if (intval($result['errcode']) == 40001 && !$isReload) {
                $this->sendTplNotice($appId, $secret, $openid, $postData, $url, $templateId, $topColor, true);
            }

            if (ErrorUtil::isError($result)) {
                return ErrorUtil::error(-1, "访问公众平台接口失败, 错误: {$result['message']}");
            }
            if (empty($result)) {
                return ErrorUtil::error(-1, "接口调用失败, 元数据: {$result['meta']}");
            }
            if (!empty($result['errcode'])) {
                return ErrorUtil::error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
            }
        }

        return true;
    }

    // 获取accessToken
    public function getAccessToken($appId, $secret, $expire = 6000, $isReload = false)
    {
        $accessTokenKey = 'accessToken' . "_" . $appId;
        $accessTokenCache = Cache::get($accessTokenKey);
        $accessTokenCache = json_decode($accessTokenCache, true);

        if (empty($accessTokenCache) || empty($accessTokenCache['expire_time']) || $accessTokenCache['expire_time'] <= time() || $isReload) {
            if ($isReload) {
                $url = "https://api.weixin.qq.com/cgi-bin/stable_token";  // 该接口调用频率限制为 1万次 每分钟，每天限制调用 50万 次
                $data = [
                    'grant_type'    => 'client_credential',
                    'appid'         => $appId,
                    'secret'        => $secret,
                    'force_refresh' => $isReload,
                ];
                $res = RequestUtil::httpPostJson($url, $data);
                $accessTokenCache = json_decode($res, true);
                if ($accessTokenCache['errcode'] == '45009') {
                    $res = RequestUtil::httpPost("https://api.weixin.qq.com/cgi-bin/clear_quota/v2", ['appid' => $appId, 'appsecret' => $secret]);
                    $resArr = json_decode($res, true);
                    if ($resArr['errcode'] == 0) {
                        $this->getAccessToken($appId, $secret, $expire, $isReload);
                    }
                }
            } else {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $secret;
                $res = RequestUtil::httpGet($url);
                $accessTokenCache = json_decode($res, true);
            }

            $accessToken = $accessTokenCache['access_token'] ?? '';

            if (empty($accessToken)) {
                throw new ApiException($accessTokenCache['errmsg']);
            }

            $accessTokenCache['expire_time'] = time() + $expire;
            Cache::set($accessTokenKey, json_encode($accessTokenCache), $expire + 200);
        } else {
            $accessToken = $accessTokenCache['access_token'] ?? '';
            if (empty($accessToken)) {
                Cache::set($accessTokenKey, null, $expire + 200);
                return false;
            }
        }
        return $accessToken;
    }

}