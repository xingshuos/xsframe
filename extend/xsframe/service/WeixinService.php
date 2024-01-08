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

class WeixinService
{
    // 获取客服帐号列表
    public function getAccountList($appId, $secret, $isReload = false)
    {
        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if ($token) {
            $data           = array();
            $data['offset'] = 0;
            $data['limit']  = 100;
            $postUrl        = "https://qyapi.weixin.qq.com/cgi-bin/kf/account/list?access_token={$token}";

            $response = RequestUtil::request($postUrl, $data, true);
            $result   = json_decode($response, true);

            # token过期
            if (intval($result['errcode']) == 40001 && !$isReload) {
                $this->getAccountList($appId, true);
            }

            if (ErrorUtil::isError($result)) {
                throw new ApiException("访问企业微信接口失败, 错误: {$result['message']}");
            }
            if (empty($result)) {
                throw new ApiException("接口调用失败, 元数据: {$result['meta']}");
            } elseif (!empty($result['errcode'])) {
                throw new ApiException("访问企业微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
            }
        }

        return true;
    }

    // 获取客服帐号链接
    public function getContactWay($appId, $secret, $openKFid, $isReload = false)
    {
        $data = array(
            "open_kfid" => $openKFid,
            "scene"     => 'xsframe',
        );

        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if (ErrorUtil::isError($token)) {
            return $token;
        }
        $postUrl  = "https://qyapi.weixin.qq.com/cgi-bin/kf/add_contact_way?access_token={$token}";
        $response = RequestUtil::request($postUrl, urldecode(json_encode($data)), true);

        if (ErrorUtil::isError($response)) {
            throw new ApiException("访问企业微信接口失败, 错误: {$response['message']}");
        }
        $result = @json_decode($response['content'], true);
        if (empty($result)) {
            throw new ApiException("接口调用失败, 元数据: {$response['meta']}");
        } elseif (!empty($result['errcode'])) {
            throw new ApiException("访问企业微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
        }
        return true;
    }

    // 获取accessToken
    private function getAccessToken($appId, $secret, $expire = 7000, $isReload = false)
    {
        $accessTokenKey   = 'accessToken' . "_" . $appId;
        $accessTokenCache = Cache::get($accessTokenKey);
        $accessTokenCache = json_decode($accessTokenCache, true);

        if (empty($accessTokenCache) || empty($accessTokenCache['expire_time']) || $accessTokenCache['expire_time'] <= time() || $isReload) {
            $url              = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=" . $appId . "&corpsecret=" . $secret;
            $res              = RequestUtil::httpGet($url);
            $accessTokenCache = json_decode($res, true);
            $accessToken      = $accessTokenCache['access_token'] ?? '';

            if (empty($accessToken)) {
                return false;
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