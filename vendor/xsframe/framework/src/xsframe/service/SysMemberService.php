<?php

namespace xsframe\service;

use think\facade\Cache;
use xsframe\base\BaseService;
use xsframe\enum\ExceptionEnum;
use xsframe\exception\ApiException;
use xsframe\facade\service\JingTanServiceFacade;
use xsframe\facade\service\PayServiceFacade;
use xsframe\facade\service\SmsServiceFacade;
use xsframe\facade\service\SysMemberCreditsRecordServiceFacade;
use xsframe\util\RandomUtil;

class SysMemberService extends BaseService
{
    protected $tableName = "sys_member";
    protected $expire = 60 * 60 * 24 * 7; // 默认1周时间过期

    // 暂时无用 主要是用来缓存用户信息
    private function getMemberInfoKey($key = null): string
    {
        return md5($this->uniacid . "_" . env('authkey') . "_sysMemberInfo") . ($key ? '_' . $key : '');
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

        if (empty($token)) {
            $token = $_COOKIE[$this->getMemberInfoKey()];
        }

        if (env('APP_DEBUG') && empty($token)) {
            $token = $this->params['token'] ?? '';
            $memberId = $token ? authcode2($token) : 0;
        } else {
            $memberId = authcode2($token);

            if (!empty($memberId)) {
                $deleteKey = $this->getMemberInfoKey() . "_" . $memberId;
                $deleteMemberId = Cache::get($deleteKey);

                if ($deleteMemberId) {
                    $memberId = 0;
                    Cache::delete($deleteKey);
                }

            }

        }

        if (empty($memberId)) {
            throw new ApiException("用户未登录", ExceptionEnum::API_LOGIN_ERROR_CODE);
        }

        return $memberId;
    }

    /**
     * 用户注册
     * @param $username - 手机号或邮箱
     * @throws ApiException
     */
    public function register($username, $code = null, $testCode = null, $updateData = [])
    {
        $memberInfoRet = self::getMemberInfo($username, $code, $testCode);
        $memberInfo = $memberInfoRet['memberInfo'];
        $type = $memberInfoRet['type'];

        if (!empty($memberInfo)) {
            if ($type == 'username') {
                throw new ApiException("该账号已经注册过了，请直接登录");
            }
            if ($type == 'mobile') {
                throw new ApiException("该手机号已经注册过了，请直接登录");
            }
            if ($type == 'email') {
                throw new ApiException("该邮箱已经注册过了，请直接登录");
            }
        }

        return self::checkMember($memberInfoRet['type'], $username, null, null, $memberInfo, $updateData);
    }

    /**
     * 手机号绑定
     */
    public function mobileBind($mobile, $code = null, $testCode = null, $token = null): bool
    {
        $userId = $this->getUserId($token);

        if ($userId) {
            if (!empty($code)) {
                SmsServiceFacade::checkSmsCode($mobile, $code, $testCode);
            }
            try {
                self::checkMember("id", $userId, '', '', null, [
                    'username'      => $mobile,
                    'mobile'        => $mobile,
                    'mobile_verify' => 1,
                ]);
            } catch (ApiException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * 手机号登录
     * @param $username - 手机号或邮箱
     * @param string|null $password - 密码
     * @param string|int $code - 验证码
     * @param string|int $testCode - 测试验证码
     * @param bool $autoLogin - 是否自动登录
     * @return string
     * @throws ApiException
     */
    public function mobileLogin($username, string $password = null, $code = null, $testCode = null, bool $autoLogin = true): string
    {
        if (empty($password) && empty($code)) {
            throw new ApiException("参数错误");
        }

        $memberInfoRet = self::getMemberInfo($username, $code, $testCode);
        $memberInfo = $memberInfoRet['memberInfo'];
        $type = $memberInfoRet['type'];

        $updateData = [];
        if (!empty($code)) {
            if (!empty($password)) {
                $updateData['password'] = $password;
            }
        } else {
            if (!empty($memberInfo)) {
                if ($memberInfo['password'] != md5($password . $memberInfo['salt'])) {
                    throw new ApiException("用户密码错误请重试");
                }
            } else {
                throw new ApiException("当前账号不存在");
            }
        }

        return self::checkMember($type, $username, null, null, $memberInfo, $updateData, $autoLogin);
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
            throw new ApiException('授权登录失败，授权码为空');
        }

        $response = JingTanServiceFacade::getAccessToken($auth_code);
        $accessToken = $response['access_token'];

        if (empty($accessToken)) {
            throw new ApiException("授权登录失败");
        }

        $authUserInfo = JingTanServiceFacade::getUserInfo($accessToken);
        return $this->checkMember('jt_openid', $authUserInfo['open_user_id'], $authUserInfo['nick_name'], $authUserInfo['avatar']);
    }

    // 通过账号获取用户信息
    private function getMemberInfo($username, $code = null, $testCode = null): array
    {
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $username) && !filter_var($username, FILTER_VALIDATE_EMAIL) && strlen($username) < 6) {
            throw new ApiException("请输入正确的账号信息");
        }

        if (!empty($code)) {
            SmsServiceFacade::checkSmsCode($username, $code, $testCode);
        }

        $memberInfo = self::getInfo(['username' => $username, 'uniacid' => $this->uniacid, 'is_deleted' => 0], "id,password,salt");
        $type = "username";
        if (empty($memberInfo)) {
            $memberInfo = self::getInfo(['mobile' => $username, 'uniacid' => $this->uniacid, 'is_deleted' => 0], "id,password,salt");
            $type = "mobile";
            if (empty($memberInfo)) {
                $memberInfo = self::getInfo(['email' => $username, 'uniacid' => $this->uniacid, 'is_deleted' => 0], "id,password,salt");
                $type = "email";
                if (empty($memberInfo)) {
                    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
                        if (preg_match("/^1[3456789]{1}\d{9}$/", $username)) {
                            $type = "mobile";
                        } else {
                            $type = "username";
                        }
                    } else {
                        $type = "username";
                    }
                }
            }
        }

        return [
            'memberInfo' => $memberInfo,
            'type'       => $type,
        ];
    }

    // 获取登录凭证token
    public function getToken($memberId, $autoLogin = true)
    {
        $token = authcode2($memberId, "ENCODE", $this->expire);
        if ($autoLogin) {
            isetcookie($this->getMemberInfoKey(), $token, 30 * 86400);
        }
        return $token;
    }

    // 退出登录
    public function logout($memberId = null): bool
    {
        if ($memberId) {
            Cache::set($this->getMemberInfoKey() . "_" . $memberId, false, 86400 * 30);
        }
        return isetcookie($this->getMemberInfoKey(), false, -100);
    }

    // 更新积分或余额字段
    public function setCredit($userId, $credittype = 'credit1', $credits = 0, $log = [])
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

        $newCredit = 0;
        if (!empty($userId)) {
            $member = self::getInfo(['id' => $userId]);
            $value = $member[$credittype];
            $newCredit = $credits + $value;

            if ($newCredit <= 0) {
                $newCredit = 0;
            }

            $log_data['remark'] = $log_data['remark'] . ' 剩余: ' . $newCredit;
            self::updateInfo([$credittype => $newCredit], ['id' => $userId]);

            SysMemberCreditsRecordServiceFacade::insertInfo($log_data);
        }

        return $newCredit;
    }

    // 设置会员信息
    public function setMember($updateData = [], $where = [])
    {
        $memberInfo = self::getInfo($where);

        if (empty($memberInfo)) {
            $memberInfo = [
                'uniacid'     => $this->uniacid,
                'module'      => $this->module,
                'create_time' => TIMESTAMP,
                'update_time' => TIMESTAMP,
            ];
            $memberInfo = array_merge($memberInfo, $updateData);
            $memberInfo['id'] = self::insertInfo($memberInfo);
        }else{
            $memberInfo = array_merge($memberInfo, $updateData);
            self::updateInfo($memberInfo, $where);
        }

        return $memberInfo;
    }

    // ---------------------------------以下为私有方法，禁止外部调用----------------------------------

    // 校验注册用户
    private function checkMember($type, $value, $nickname = '', $avatar = '', $memberInfo = null, $updateData = [], $autoLogin = true)
    {
        if (empty($memberInfo)) {
            $memberInfo = self::getInfo([$type => $value, 'uniacid' => $this->uniacid, 'is_deleted' => 0]);
        }

        if (!$memberInfo) {
            $insertData = [
                'uniacid'     => $this->uniacid,
                'module'      => $this->module,
                'username'    => $value,
                'nickname'    => $nickname ?? '',
                'avatar'      => $avatar ?? '',
                'create_time' => TIMESTAMP,
                'update_time' => TIMESTAMP,
            ];
            $insertData[$type] = $value;

            if (empty($nickname)) {
                $insertData['nickname'] = substr($value, 0, 3) . "xxxx" . substr($value, 7, 4);
            }

            if (!empty($updateData)) {
                $insertData = array_merge($insertData, $updateData);
                if (!empty($updateData['password'])) {
                    $salt = RandomUtil::random(6);
                    if (strlen($updateData['password']) < 6) {
                        throw new ApiException('密码过于简单');
                    }
                    $insertData['password'] = md5($updateData['password'] . $salt);
                    $insertData['salt'] = $salt;
                }
            }

            $memberId = self::insertInfo($insertData);
        } else {
            $memberId = $memberInfo['id'];

            if (empty($memberInfo['username'])) {
                if ($type != 'id') {
                    $updateData['username'] = $value;
                }
                $updateData['update_time'] = TIMESTAMP;
            } else {
                if (!empty($updateData['username'])) {
                    unset($updateData['username']);
                }
            }

            if (!empty($updateData['password']) && $memberInfo['password'] != md5($updateData['password'] . $memberInfo['salt'])) {
                $salt = RandomUtil::random(6);
                if (strlen($updateData['password']) < 6) {
                    throw new ApiException('密码过于简单');
                }
                $updateData['password'] = md5($updateData['password'] . $salt);
                $updateData['salt'] = $salt;
            }

            if (!empty($updateData)) {
                self::updateInfo($updateData, ['id' => $memberId]);
            }
        }

        return $memberId ? $this->getToken($memberId, $autoLogin) : false;
    }
}