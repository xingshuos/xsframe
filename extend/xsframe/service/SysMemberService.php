<?php

namespace xsframe\service;

use xsframe\base\BaseService;
use xsframe\enum\ExceptionEnum;
use xsframe\exception\ApiException;
use xsframe\facade\service\JingTanServiceFacade;
use xsframe\facade\service\PayServiceFacade;
use xsframe\facade\service\SmsServiceFacade;
use xsframe\facade\service\SysMemberCreditsRecordServiceFacade;

class SysMemberService extends BaseService
{
    protected $tableName = "sys_member";
    protected $expire = 60 * 60 * 24 * 7; // 默认1周时间过期

    // 暂时无用 主要是用来缓存用户信息
    private function getMemberInfoKey($key): string
    {
        return md5($this->uniacid . "_" . env('authkey') . "_sysMemberInfo_" . $key);
    }

    // 获取用户ID
    public function getUserId($token = null)
    {
        try {
            return $this->checkLogin($token);
        } catch (ApiException $e) {
            return 0;
        }
    }

    // 验证用户登录信息
    public function checkLogin($token = null)
    {
        $token = empty($token) ? ($this->header['authorization'] ?? ($this->header['Authorization'] ?? null)) : $token;

        if (env('APP_DEBUG') && empty($token)) {
            $token = $this->params['token'] ?? '';
            $memberId = $token ? authcode2($token) : 1;
        } else {
            $memberId = authcode2($token);
        }

        if (empty($memberId)) {
            throw new ApiException("用户未登录", ExceptionEnum::API_LOGIN_ERROR_CODE);
        }

        return $memberId;
    }

    /**
     * 手机号登录
     * @throws ApiException
     */
    public function mobileLogin($mobile, $password = null, $code = null, $testCode = null): string
    {
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
            throw new ApiException("请输入正确的手机号!");
        }

        if (!empty($code)) {
            if (empty($password)) {
                SmsServiceFacade::checkSmsCode($mobile, $code, $testCode);
            }
        } else {
            if (empty($password)) {
                throw new ApiException("请输入正确的密码信息");
            } else {
                $memberInfo = self::getInfo(['username' => $mobile], "id,password,salt");
                if ($memberInfo['password'] != md5($password . $memberInfo['salt'])) {
                    throw new ApiException("用户密码错误请重试");
                }
            }
        }

        $nickname = substr($mobile, 0, 3) . "xxxx" . substr($mobile, 7, 4);
        return self::checkMember('username', $mobile, $nickname);
    }

    /**
     * 阿里H5授权登录
     *
     * @throws ApiException
     */
    public function aliH5Login(): \think\response\Json
    {
        $authCode = $this->params['auth_code'] ?? '';

        if (empty($authCode)) {
            throw new ApiException('授权登录失败，授权码为空');
        }

        $aliOauthToken = PayServiceFacade::aliOauthToken($authCode);
        $accessToken = $aliOauthToken['access_token'];
        $userId = $aliOauthToken['user_id'];

        if (empty($userId)) {
            throw new ApiException("授权登录失败");
        }

        $userInfo = PayServiceFacade::aliUserInfo($accessToken);
        $nickname = $userInfo['nick_name'] ?? '';
        $avatar = $userInfo['avatar'] ?? '';

        return self::checkMember('ali_openid', $userId, $nickname, $avatar);
    }

    /**
     * 鲸探APP授权登录
     * 保存授权code,无该用户则创建该用户
     * 其他信息不需要
     *
     * @throws ApiException
     */
    public function authCodeLogin(): \think\response\Json
    {
        $auth_code = $this->params['auth_code'] ?? '';
        if (empty($auth_code)) {
            throw new ApiException('授权登录失败，授权码为空2');
        }

        $response = JingTanServiceFacade::getAccessToken($auth_code);
        $accessToken = $response['access_token'];

        if (empty($accessToken)) {
            throw new ApiException("授权登录失败");
        }

        $authUserInfo = JingTanServiceFacade::getUserInfo($accessToken);
        return $this->checkMember('jt_openid', $authUserInfo['open_user_id'], $authUserInfo['nick_name'], $authUserInfo['avatar']);
    }

    // 校验注册用户
    private function checkMember($type, $value, $nickname = '', $avatar = '')
    {
        $memberInfo = self::getInfo([$type => $value]);
        if (!$memberInfo) {
            $insertData = [
                'uniacid'     => $this->uniacid,
                'nickname'    => $nickname,
                'avatar'      => $avatar,
                'create_time' => time(),
                'update_time' => time(),
            ];
            $insertData[$type] = $value;
            $memberId = self::insertInfo($insertData);
            if (!$memberId) throw new ApiException('创建用户信息失败，请稍后再试');
        } else {
            $memberId = $memberInfo['id'];
        }

        return authcode2($memberId, "ENCODE", $this->expire);
    }

    // 退出登录 TODO
    public function logout($token = null): bool
    {
        return true;
    }

    // 更新积分或余额字段
    public function setCredit($userId, $credittype = 'credit1', $credits = 0, $log = []): bool
    {
        if (empty($log)) {
            $log = [$userId, '未记录'];
        } else {
            if (!is_array($log)) {
                $log = [0, $log];
            }
        }

        $log_data = [
            'uid'        => intval($userId),
            'credittype' => $credittype,
            'uniacid'    => $this->uniacid,
            'num'        => $credits,
            'createtime' => TIMESTAMP,
            'module'     => $this->module,
            'operator'   => intval($log[0]),
            'remark'     => $log[1]
        ];

        if (!empty($userId)) {
            $member = self::getInfo(['id' => $userId]);
            $value = $member[$credittype];
            $newcredit = $credits + $value;

            if ($newcredit <= 0) {
                $newcredit = 0;
            }

            $log_data['remark'] = $log_data['remark'] . ' 剩余: ' . $newcredit;
            self::updateInfo([$credittype => $newcredit], ['id' => $userId]);

            $a = $newcredit;
            SysMemberCreditsRecordServiceFacade::insertInfo($log_data);
        }

        return true;
    }
}