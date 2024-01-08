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
            $data                = array();
            $data['touser']      = $openid;
            $data['template_id'] = trim($templateId);
            $data['url']         = trim($url);
            $data['topcolor']    = trim($topColor);
            $data['data']        = $postData;
            $data                = json_encode($data);
            $postUrl             = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$token}";

            $response = RequestUtil::request($postUrl, $data, true);
            $result   = json_decode($response, true);

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

        $data = array(
            "touser"  => $openid,
            "msgtype" => "text",
            "text"    => array(
                'content' => urlencode($content),
            )
        );

        $token = $this->getAccessToken($appId, $secret);
        if (ErrorUtil::isError($token)) {
            return $token;
        }
        $postUrl  = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
        $response = RequestUtil::request($postUrl, urldecode(json_encode($data)), true);

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
        $nonceStr  = RandomUtil::random(16, true);

        $jsApiTicket = $this->getJsApiTicket($appId, $secret);

        if (ErrorUtil::isError($jsApiTicket)) {
            return ErrorUtil::error(-1, $jsApiTicket['msg']);
        }

        // $url = 'https://api.lymlart.com/mIndex';
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsApiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => strval($timestamp),
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
            "debug"     => false,
        );
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
            $url              = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $accessToken;
            $res              = RequestUtil::httpGet($url);
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
    private function getAccessToken($appId, $secret, $expire = 7000, $isReload = false)
    {
        $accessTokenKey   = 'accessToken' . "_" . $appId;
        $accessTokenCache = Cache::get($accessTokenKey);
        $accessTokenCache = json_decode($accessTokenCache, true);

        if (empty($accessTokenCache) || empty($accessTokenCache['expire_time']) || $accessTokenCache['expire_time'] <= time() || $isReload) {
            $url              = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $secret;
            $res              = RequestUtil::httpGet($url);
            $accessTokenCache = json_decode($res, true);
            $accessToken      = $accessTokenCache['access_token'] ?? '';

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

        $userInfo = array();
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
}