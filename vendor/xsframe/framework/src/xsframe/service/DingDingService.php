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

use think\facade\Cache;
use think\facade\Env;
use xsframe\base\BaseService;
use xsframe\exception\ApiException;
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;
use xsframe\util\StringUtil;
use xsframe\wrapper\SettingsWrapper;

class DingDingService
{
    private $appKey = null;
    private $appSecret = null;

    private $uniacid = null;
    private $settingsController;
    private $accountSettings;

    public function __construct($uniacid)
    {
        $this->uniacid = $uniacid;

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
            $this->accountSettings = $this->settingsController->getAccountSettings($uniacid, 'settings');
            $dingdingSets = $this->accountSettings['dingding'] ?? [];
            $this->appKey = $dingdingSets['appKey'] ?? '';
            $this->appSecret = $dingdingSets['appSecret'] ?? '';
        }
    }

    // 获取部门列表
    public function getDepartmentList()
    {
        return $this->getDeptList();
    }

    // 获取所有部门列表
    public function getDeptAllList()
    {
        $parentList = $this->getDeptList();
        // 最多查询8级部门
        foreach ($parentList as $key => $parent) {
            $parentList2 = $this->getDeptList($parent['dept_id']);

            if (!empty($parentList2)) {
                $parentList = array_merge($parentList, $parentList2);

                foreach ($parentList2 as $key2 => $parent2) {
                    $parentList3 = $this->getDeptList($parent2['dept_id']);

                    if (!empty($parentList3)) {
                        $parentList = array_merge($parentList, $parentList3);

                        foreach ($parentList3 as $key3 => $parent3) {
                            $parentList4 = $this->getDeptList($parent3['dept_id']);

                            if (!empty($parentList4)) {
                                $parentList = array_merge($parentList, $parentList4);

                                foreach ($parentList4 as $key4 => $parent4) {
                                    $parentList5 = $this->getDeptList($parent4['dept_id']);
                                    if (!empty($parentList5)) {
                                        $parentList = array_merge($parentList, $parentList5);

                                        foreach ($parentList5 as $key5 => $parent5) {
                                            $parentList6 = $this->getDeptList($parent5['dept_id']);
                                            if (!empty($parentList6)) {
                                                $parentList = array_merge($parentList, $parentList6);

                                                foreach ($parentList6 as $key6 => $parent6) {
                                                    $parentList7 = $this->getDeptList($parent6['dept_id']);
                                                    if (!empty($parentList7)) {
                                                        $parentList = array_merge($parentList, $parentList7);

                                                        foreach ($parentList7 as $key7 => $parent7) {
                                                            $parentList8 = $this->getDeptList($parent7['dept_id']);
                                                            if (!empty($parentList8)) {
                                                                $parentList = array_merge($parentList, $parentList8);
                                                            }
                                                        }

                                                    }
                                                }

                                            }
                                        }

                                    }
                                }
                            }

                        }
                    }

                }
            }

        }
        return $parentList;
    }

    // 获取部门子部门列表
    public function getDeptList($deptId = null)
    {
        $postData = [
            'dept_id' => $deptId
        ];
        return $this->doHttpPost("https://oapi.dingtalk.com/topapi/v2/department/listsub", $postData);
    }

    // 查询部门子部门id列表
    public function getDeptSubList($deptId = null)
    {
        $postData = [
            'dept_id' => $deptId
        ];
        return $this->doHttpPost("https://oapi.dingtalk.com/topapi/v2/department/listsubid", $postData);
    }

    // 获取部门用户userid列表
    public function getDepartmentUserList($deptId = null)
    {
        return $this->getDeptUserList($deptId);
    }

    public function getDeptUserList($deptId = null)
    {
        $postData = [
            'dept_id' => $deptId
        ];
        $userIdRet = $this->doHttpPost("https://oapi.dingtalk.com/topapi/user/listid", $postData);

        $newUserList = [];
        foreach ($userIdRet['userid_list'] ?? [] as $userId) {
            $userInfo = $this->getUserInfo($userId);
            $newUserList[] = $userInfo;
        }
        return $newUserList;
    }

    // 查询用户详情
    public function getUserInfo($userId = null)
    {
        $postData = [
            'userid' => $userId
        ];
        return $this->doHttpPost("https://oapi.dingtalk.com/topapi/v2/user/get", $postData);
    }

    // 获取accessToken
    public function getAccessToken($appId = null, $secret = null, $expire = 6000, $isReload = false)
    {
        if (empty($appId)) {
            $appId = $this->appKey;
        }

        if (empty($secret)) {
            $secret = $this->appSecret;
        }

        $accessTokenKey = 'accessToken_dd' . "_" . $this->appKey;
        $accessTokenCache = Cache::get($accessTokenKey);
        $accessTokenCache = json_decode($accessTokenCache, true);

        if (empty($accessTokenCache) || empty($accessTokenCache['expire_time']) || $accessTokenCache['expire_time'] <= time() || $isReload) {
            $url = "https://api.dingtalk.com/v1.0/oauth2/accessToken";
            $data = [
                'appKey'    => $appId,
                'appSecret' => $secret,
            ];
            $res = RequestUtil::httpPostJson($url, $data, true);
            $accessTokenCache = json_decode($res, true);

            $accessToken = $accessTokenCache['accessToken'] ?? '';
            if (empty($accessToken)) {
                throw new ApiException($accessTokenCache['message']);
            }

            $accessTokenCache['expire_time'] = time() + $expire;
            Cache::set($accessTokenKey, json_encode($accessTokenCache), $expire + 200);
        } else {
            $accessToken = $accessTokenCache['accessToken'] ?? '';
            if (empty($accessToken)) {
                Cache::set($accessTokenKey, null, $expire + 200);
                return false;
            }
        }
        return $accessToken;
    }

    // 发送http post 请求
    private function doHttpPost($url, $postData)
    {
        $accessToken = $this->getAccessToken();
        $response = RequestUtil::httpPost($url . "?access_token={$accessToken}", $postData);
        $result = json_decode($response, true);
        if ($result['errcode'] != '0') {
            throw new ApiException($result['errmsg']);
        }
        return $result['result'];
    }
}