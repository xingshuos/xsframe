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

use xsframe\exception\ApiException;
use xsframe\util\ErrorUtil;
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;
use think\facade\Cache;

class WechatService
{
    // 发送模板消息通知
    public function sendTplNotice($appId, $secret, $openid, $postData, $url = '', $templateId = null, $topColor = '#FF683F', $isReload = false)
    {
        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);

        if ($token) {
            $data = [];
            $data['touser'] = $openid;
            $data['template_id'] = trim($templateId);
            $data['url'] = trim($url);
            $data['topcolor'] = trim($topColor);
            $data['data'] = $postData;
            $data = json_encode($data);
            $postUrl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$token}";

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
            return true;
        }
        return false;
    }

    // 发送客服消息通知
    public function sendCustomNotice($appId, $secret, $openid, $msg, $url = '')
    {
        $content = "";
        if (is_array($msg)) {
            foreach ($msg as $key => $value) {
                if (!empty($value['title'])) {
                    $content .= $value['title'] . ":" . $value['value'] . "\n";
                } else {
                    $content .= $value['value'] . "\n";
                    if ($key == 0) {
                        $content .= "\n";
                    }
                }
            }
        } else {
            $content = $msg;
        }

        // 双引号转单引号 得以让公众号识别
        $content = str_replace('"', "'", $content);

        if (!empty($url)) {
            $content .= "\n<a href='{$url}'>点击查看详情</a>";
        }

        $data = [
            "touser"  => $openid,
            "msgtype" => "text",
            "text"    => [
                'content' => urlencode($content),
            ]
        ];

        $token = $this->getAccessToken($appId, $secret);
        if (ErrorUtil::isError($token)) {
            return $token;
        }
        $postUrl = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
        $response = RequestUtil::httpPostJson($postUrl, $data);

        if (ErrorUtil::isError($response)) {
            return ErrorUtil::error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
        }
        $result = @json_decode($response['content'], true);
        if (empty($result)) {
            return ErrorUtil::error(-1, "接口调用失败, 元数据: {$result['meta']}");
        }
        if (!empty($result['errcode'])) {
            return ErrorUtil::error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
        }
        return true;
    }

    // 获取signPackage
    public function getSignPackage($appId, $secret, $url = '')
    {
        $timestamp = time();
        $nonceStr = RandomUtil::random(16, true);

        $jsApiTicket = $this->getJsApiTicket($appId, $secret);

        if (ErrorUtil::isError($jsApiTicket)) {
            return ErrorUtil::error(-1, $jsApiTicket['msg']);
        }

        // $url = 'https://api.lymlart.com/mIndex';
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsApiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = [
            "appId"     => $appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => strval($timestamp),
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
            "debug"     => false,
        ];
        return $signPackage;
    }

    // 获取jsApiTicket
    private function getJsApiTicket($appId, $secret, $expire = 7000)
    {
        $accessToken = $this->getAccessToken($appId, $secret);
        if (ErrorUtil::isError($accessToken)) {
            return ErrorUtil::error(-1, $accessToken['msg']);
        }

        $jsApiTicketKey = 'jsApiTicket' . "_" . $appId;

        $jsApiTicketCache = Cache::get($jsApiTicketKey);
        $jsApiTicketCache = json_decode($jsApiTicketCache, true);

        if (empty($jsApiTicketCache) || $jsApiTicketCache['errcode'] == 40001 || empty($jsApiTicketCache['expire_time']) || $jsApiTicketCache['expire_time'] < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $accessToken;
            $res = RequestUtil::httpGet($url);
            $jsApiTicketCache = json_decode($res, true);

            if (empty($jsApiTicketCache['ticket'])) {
                return ErrorUtil::error(-1, $jsApiTicketCache['errmsg']);
            }

            $jsApiTicketCache['expire_time'] = time() + $expire;
            Cache::set($jsApiTicketKey, json_encode($jsApiTicketCache), $expire + 200);
            $jsApiTicket = $jsApiTicketCache['ticket'];
        } else {
            $jsApiTicket = $jsApiTicketCache['ticket'];
        }
        return $jsApiTicket;
    }

    // 获取accessToken
    public function getAccessToken($appId, $secret, $expire = 7000, $isReload = false)
    {
        $accessTokenKey = 'accessToken' . "_" . $appId;
        $accessTokenCache = Cache::get($accessTokenKey);
        $accessTokenCache = json_decode($accessTokenCache, true);

        if (empty($accessTokenCache) || empty($accessTokenCache['expire_time']) || $accessTokenCache['expire_time'] <= time() || $isReload) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $secret;
            $res = RequestUtil::httpGet($url);
            $accessTokenCache = json_decode($res, true);
            $accessToken = $accessTokenCache['access_token'] ?? '';

            if (empty($accessToken)) {
                return ErrorUtil::error(-1, $accessTokenCache['errmsg']);
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

    /**
     * 微信授权
     * @param $code
     * @param $appid
     * @param $secret
     * @param string $snsapi
     * @return array|bool|mixed|string
     * @throws ApiException
     */
    public function wechatAuth($code, $appid, $secret, $snsapi = "snsapi_base")
    {
        $getOauthAccessToken = RequestUtil::httpGet("https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $secret . "&code=" . $code . "&grant_type=authorization_code");

        $result = json_decode($getOauthAccessToken, true);

        // dump($result);
        // dump($appid);
        // dump($secret);
        // die;

        if (!empty($result["errcode"]) && ($result["errcode"] == "40029" || $result["errcode"] == "40163")) {
            throw new ApiException($result['errmsg']);
        }

        $userInfo = [];
        if ($snsapi == "snsapi_userinfo") {
            $userInfo = RequestUtil::httpGet("https://api.weixin.qq.com/sns/userinfo?access_token=" . $result["access_token"] . "&openid=" . $result["openid"] . "&lang=zh_CN");
            $userInfo = json_decode($userInfo, true);
        } else {
            if ($snsapi == "snsapi_base") {
                $userInfo["openid"] = $result["openid"];
            }
        }

        return $userInfo;
    }

    /**
     * 微信创建子菜单
     * @param $appid
     * @param $secret
     * @param $menus
     * @return array|bool|mixed|string
     * @throws ApiException
     */

    public function createMenu($appId, $secret, $menus, $isReload = false)
    {
        $access_token = $this->getAccessToken($appId, $secret, 7000, $isReload);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
        $response = RequestUtil::httpPostJson($url, json_encode($menus, 320));
        $output = json_decode($response, true);
        return $output;
    }

    /**
     * 创建个性化菜单
     * @param string $appId 公众号appid
     * @param string $secret 公众号secret
     * @param array $button 一级菜单数组(1-3个)
     * @param array $matchrule 菜单匹配规则(至少一个非空字段)
     * @param bool $isReload 是否重新加载access_token
     * @return array|bool 返回结果数组或false
     * @throws ApiException
     */
    public function addConditionalMenu($appId, $secret, $button, $matchrule, $isReload = false)
    {
        // 验证菜单数组
        if (empty($button) || !is_array($button) || count($button) < 1 || count($button) > 3) {
            return ErrorUtil::error(-1, "菜单数量必须在1-3个之间");
        }

        // 验证匹配规则，至少有一个非空字段
        $validMatchrule = false;
        foreach ($matchrule as $value) {
            if (!empty($value)) {
                $validMatchrule = true;
                break;
            }
        }
        if (!$validMatchrule) {
            return ErrorUtil::error(-1, "菜单匹配规则至少需要一个非空字段");
        }

        // 过滤掉微信已不再支持的隐私字段（根据文档说明）
        $unsupportedFields = ['sex', 'country', 'province', 'city', 'language'];
        foreach ($unsupportedFields as $field) {
            if (isset($matchrule[$field])) {
                unset($matchrule[$field]);
            }
        }

        // 构建请求数据
        $data = [
            'button'    => $button,
            'matchrule' => $matchrule
        ];

        // 获取access_token
        $accessToken = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if (ErrorUtil::isError($accessToken)) {
            return $accessToken;
        }

        // 构建请求URL
        $url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token={$accessToken}";

        // 发送POST请求（使用JSON格式）
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response = RequestUtil::httpPostJson($url, $jsonData);

        // 处理响应
        if (ErrorUtil::isError($response)) {
            return ErrorUtil::error(-1, "访问微信接口失败: {$response['message']}");
        }

        $result = json_decode($response, true);

        // token过期重试
        if (isset($result['errcode']) && intval($result['errcode']) == 40001 && !$isReload) {
            return $this->addConditionalMenu($appId, $secret, $button, $matchrule, true);
        }

        // 检查结果
        if (empty($result)) {
            return ErrorUtil::error(-1, "接口调用失败，返回数据为空");
        }

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            $errmsg = $result['errmsg'] ?? '未知错误';
            return ErrorUtil::error(intval($result['errcode']), "创建个性化菜单失败: {$errmsg}");
        }

        return $result; // 返回完整结果，包含menuid
    }

    /**
     * 删除个性化菜单
     * @param string $appId 公众号appid
     * @param string $secret 公众号secret
     * @param string $menuid 个性化菜单ID
     * @param bool $isReload 是否重新加载access_token
     * @return array|bool 返回结果
     */
    public function deleteConditionalMenu($appId, $secret, $menuid, $isReload = false)
    {
        // 获取access_token
        $accessToken = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if (ErrorUtil::isError($accessToken)) {
            return $accessToken;
        }

        // 构建请求URL
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token={$accessToken}";

        // 构建请求数据
        $data = ['menuid' => $menuid];
        $jsonData = json_encode($data);

        // 发送POST请求
        $response = RequestUtil::httpPostJson($url, $jsonData);

        // 处理响应
        if (ErrorUtil::isError($response)) {
            return ErrorUtil::error(-1, "访问微信接口失败: {$response['message']}");
        }

        $result = json_decode($response, true);

        // token过期重试
        if (isset($result['errcode']) && intval($result['errcode']) == 40001 && !$isReload) {
            return $this->deleteConditionalMenu($appId, $secret, $menuid, true);
        }

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            $errmsg = $result['errmsg'] ?? '未知错误';
            return ErrorUtil::error(intval($result['errcode']), "删除个性化菜单失败: {$errmsg}");
        }

        return $result;
    }

    /**
     * 测试个性化菜单匹配结果
     * @param string $appId 公众号appid
     * @param string $secret 公众号secret
     * @param string $userId 用户openid或微信号
     * @param bool $isReload 是否重新加载access_token
     * @return array|bool 返回匹配的菜单
     */
    public function tryMatchConditionalMenu($appId, $secret, $userId, $isReload = false)
    {
        // 获取access_token
        $accessToken = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if (ErrorUtil::isError($accessToken)) {
            return $accessToken;
        }

        // 构建请求URL
        $url = "https://api.weixin.qq.com/cgi-bin/menu/trymatch?access_token={$accessToken}";

        // 构建请求数据
        $data = ['user_id' => $userId];
        $jsonData = json_encode($data);

        // 发送POST请求
        $response = RequestUtil::httpPostJson($url, $jsonData);

        // 处理响应
        if (ErrorUtil::isError($response)) {
            return ErrorUtil::error(-1, "访问微信接口失败: {$response['message']}");
        }

        $result = json_decode($response, true);

        // token过期重试
        if (isset($result['errcode']) && intval($result['errcode']) == 40001 && !$isReload) {
            return $this->tryMatchConditionalMenu($appId, $secret, $userId, true);
        }

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            $errmsg = $result['errmsg'] ?? '未知错误';
            return ErrorUtil::error(intval($result['errcode']), "测试菜单匹配失败: {$errmsg}");
        }

        return $result;
    }

    /**
     * 获取所有菜单（包含默认菜单和个性化菜单）
     * @param string $appId 公众号appid
     * @param string $secret 公众号secret
     * @param bool $isReload 是否重新加载access_token
     * @return array|bool 返回菜单信息
     */
    public function getAllMenus($appId, $secret, $isReload = false)
    {
        // 获取access_token
        $accessToken = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if (ErrorUtil::isError($accessToken)) {
            return $accessToken;
        }

        // 构建请求URL
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$accessToken}";

        // 发送GET请求
        $response = RequestUtil::httpGet($url);

        // 处理响应
        if (ErrorUtil::isError($response)) {
            return ErrorUtil::error(-1, "访问微信接口失败: {$response['message']}");
        }

        $result = json_decode($response, true);

        // token过期重试
        if (isset($result['errcode']) && intval($result['errcode']) == 40001 && !$isReload) {
            return $this->getAllMenus($appId, $secret, true);
        }

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            $errmsg = $result['errmsg'] ?? '未知错误';
            return ErrorUtil::error(intval($result['errcode']), "获取菜单失败: {$errmsg}");
        }

        return $result;
    }

}