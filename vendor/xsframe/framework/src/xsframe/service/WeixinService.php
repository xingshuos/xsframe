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
use xsframe\util\ArrayUtil;
use xsframe\util\ErrorUtil;
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;
use think\facade\Cache;

class WeixinService extends BaseService
{
    private $weixinSet = null;

    protected function _service_initialize()
    {
        parent::_service_initialize();

        if (empty($this->smsSet)) {
            $this->weixinSet = $this->accountSetting['weixin'] ?? [];
        }
    }

    // 获取部门列表
    public function getDepartmentList($isReload = false, $appId = null, $secret = null)
    {
        $expireTime = 5;
        $departmentListCacheKey = "weixin_departmentList_" . md5($appId . $secret);

        if (empty($appId)) {
            $appId = $this->weixinSet['appid'];
            $secret = $this->weixinSet['corpsecret'];
        }

        $departmentList = Cache::get($departmentListCacheKey);
        if (empty($departmentList) || $isReload) {
            $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
            if ($token) {
                $getUrl = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token={$token}";

                $response = RequestUtil::httpGet($getUrl);
                $result = json_decode($response, true);

                # token过期
                if (intval($result['errcode']) == 40001 && !$isReload) {
                    $this->getDepartmentList(true, $appId, $secret);
                }

                if (ErrorUtil::isError($result)) {
                    throw new ApiException("访问企业微信接口失败, 错误: {$result['message']}");
                }

                if (empty($result)) {
                    throw new ApiException("接口调用失败, 元数据: {$result['meta']}");
                } else if (!empty($result['errcode'])) {
                    throw new ApiException("访问企业微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
                }

                $departmentList = $result['department'] ?? [];
                if (!empty($departmentList)) {
                    // 根据 parentid 从小到大排序
                    usort($departmentList, function ($a, $b) {
                        return $a['parentid'] - $b['parentid']; // 升序排序
                    });

                    // 1. 构建 id => item 的索引映射
                    $idNewMap = [];
                    foreach ($departmentList as $item) {
                        $idNewMap[$item['id']] = $item;
                    }
                    $departmentList = $idNewMap;

                    Cache::set($departmentListCacheKey, $idNewMap, $expireTime);
                }
            }
        }

        return $departmentList ?? [];
    }

    // 获取部门用户列表
    public function getDepartmentUserList($departmentId, $isReload = false, $appId = null, $secret = null)
    {
        $expireTime = 5;
        $departmentUserListCacheKey = "weixin_departmentUserList_" . md5($departmentId . $appId . $secret);

        if (empty($appId)) {
            $appId = $this->weixinSet['appid'];
            $secret = $this->weixinSet['corpsecret'];
        }

        $departmentUserList = Cache::get($departmentUserListCacheKey);
        if (empty($departmentUserList) || $isReload) {
            $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
            if ($token) {
                $getUrl = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$token}&department_id={$departmentId}";

                $response = RequestUtil::httpGet($getUrl);
                $result = json_decode($response, true);

                # token过期
                if (intval($result['errcode']) == 40001 && !$isReload) {
                    $this->getDepartmentUserList($departmentId, true, $appId, $secret);
                }

                if (ErrorUtil::isError($result)) {
                    throw new ApiException("访问企业微信接口失败, 错误: {$result['message']}");
                }
                if (empty($result)) {
                    throw new ApiException("接口调用失败, 元数据: {$result['meta']}");
                } else if (!empty($result['errcode'])) {
                    throw new ApiException("访问企业微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
                }

                $departmentUserList = $result['userlist'];
                Cache::set($departmentUserListCacheKey, $departmentUserList, $expireTime);
            }
        }
        return $departmentUserList ?? [];
    }

    // 获取客服帐号列表
    public function getAccountList($isReload = false, $appId = null, $secret = null)
    {
        if (empty($appId)) {
            $appId = $this->weixinSet['appid'];
            $secret = $this->weixinSet['corpsecret'];
        }

        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if ($token) {
            $data = [];
            $data['offset'] = 0;
            $data['limit'] = 100;
            $postUrl = "https://qyapi.weixin.qq.com/cgi-bin/kf/account/list?access_token={$token}";

            $response = RequestUtil::httpPost($postUrl, $data);
            $result = json_decode($response, true);

            # token过期
            if (intval($result['errcode']) == 40001 && !$isReload) {
                $this->getAccountList(true, $appId, $secret);
            }

            if (ErrorUtil::isError($result)) {
                throw new ApiException("访问企业微信接口失败, 错误: {$result['message']}");
            }
            if (empty($result)) {
                throw new ApiException("接口调用失败, 元数据: {$result['meta']}");
            } else if (!empty($result['errcode'])) {
                throw new ApiException("访问企业微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
            }
        }

        return $result['account_list'] ?? [];
    }

    // 获取客服帐号链接
    public function getContactWay($openKFid, $isReload = false, $appId, $secret)
    {
        if (empty($appId)) {
            $appId = $this->weixinSet['appid'];
            $secret = $this->weixinSet['corpsecret'];
        }

        $data = [
            "open_kfid" => $openKFid,
            "scene"     => 'xsframe',
        ];

        $token = $this->getAccessToken($appId, $secret, 7000, $isReload);
        if (ErrorUtil::isError($token)) {
            return $token;
        }
        $postUrl = "https://qyapi.weixin.qq.com/cgi-bin/kf/add_contact_way?access_token={$token}";
        $response = RequestUtil::httpPostJson($postUrl, $data);

        if (ErrorUtil::isError($response)) {
            throw new ApiException("访问企业微信接口失败, 错误: {$response['message']}");
        }
        $result = @json_decode($response['content'], true);
        if (empty($result)) {
            throw new ApiException("接口调用失败, 元数据: {$response['meta']}");
        } else if (!empty($result['errcode'])) {
            throw new ApiException("访问企业微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
        }

        return $result['qr_code'] ?? "";
    }

    // 获取accessToken
    public function getAccessToken($appId, $secret, $expire = 7000, $isReload = false)
    {
        $accessTokenKey = 'accessToken' . "_" . $appId;
        $accessTokenCache = Cache::get($accessTokenKey);
        $accessTokenCache = json_decode($accessTokenCache, true);

        if (empty($accessTokenCache) || empty($accessTokenCache['expire_time']) || $accessTokenCache['expire_time'] <= time() || $isReload) {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=" . $appId . "&corpsecret=" . $secret;
            $res = RequestUtil::httpGet($url);
            $accessTokenCache = json_decode($res, true);
            $accessToken = $accessTokenCache['access_token'] ?? '';

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