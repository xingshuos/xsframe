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

class WxappService
{
    // 获取手机号(无法正常调用，报access_token错误，但是并没有错的)
    public function getPhoneNumber($appId, $secret, $code, $isReload = false)
    {
        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);

        $data         = array();
        $data['code'] = $code;

        $postUrl  = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=={$token}";
        $response = RequestUtil::request($postUrl, $data, true);
        $result   = json_decode($response, true);

        # token过期
        if (intval($result['errcode']) == 40001 && !$isReload) {
            $this->getPhoneNumber($appId, $secret, $code, true);
        } else {
            if (intval($result['errcode']) != 0) {
                throw new ApiException($result['errmsg']);
            }
        }

        return $result['phone_info'];
    }

    // 发送模板消息通知
    public function sendTplNotice($appId, $secret, $openid, $templateId, $postData, $url = '', $topColor = '#FF683F', $isReload = false)
    {
        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if ($token) {
            $data                      = array();
            $data['touser']            = $openid;
            $data['template_id']       = trim($templateId);
            $data['page']              = trim($url);
            $data['miniprogram_state'] = "formal"; // 跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
            $data['lang']              = "zh_CN";
            $data['data']              = $postData;

            $data    = json_encode($data);
            $postUrl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$token}";

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
        }

        return true;
    }

    // 获取signPackage
    public function getSignPackage($appId, $secret, $url = '')
    {
        $timestamp   = time();
        $nonceStr    = RandomUtil::random(16, true);
        $jsApiTicket = $this->getJsApiTicket($appId, $secret);

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
        $accessToken    = $this->getAccessToken($appId, $secret);
        $jsApiTicketKey = 'jsApiTicket' . "_" . $appId;

        $jsApiTicketCache = Cache::get($jsApiTicketKey);
        $jsApiTicketCache = json_decode($jsApiTicketCache, true);

        if ($jsApiTicketCache['errcode'] == 40001 || empty($jsApiTicketCache) || empty($jsApiTicketCache['expire_time']) || $jsApiTicketCache['expire_time'] < time()) {
            $url              = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $accessToken;
            $res              = RequestUtil::httpGet($url);
            $jsApiTicketCache = json_decode($res, true);

            $jsApiTicketCache['expire_time'] = time() + $expire;
            Cache::set($jsApiTicketKey, json_encode($jsApiTicketCache), $expire + 200);
            $jsApiTicket = $jsApiTicketCache['ticket'];
        } else {
            $jsApiTicket = $jsApiTicketCache['ticket'];
        }
        return $jsApiTicket;
    }

    // 获取accessToken
    public function getAccessToken($appId, $secret, $expire = 6000, $isReload = false)
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
                return false;
                // throw new ApiException($accessTokenCache['errmsg']);
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