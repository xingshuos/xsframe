<?php

namespace app\store\service;

use app\store\controller\Pay;
use app\store\facade\service\UserRecordServiceFacade;
use xsframe\base\BaseService;
use xsframe\enum\ExceptionEnum;
use xsframe\enum\PayTypeEnum;
use xsframe\exception\ApiException;
use xsframe\util\RandomUtil;
use think\Exception;
use think\facade\Db;

class MemberService extends BaseService
{
    protected $tableName = "shop_member";
    private $loginKey = "member_login_session_";

    /**
     * 验证当前用户是否登录
     * @return bool
     * @throws ApiException
     */
    public function checkLogin()
    {
        $sessionKey = $_COOKIE[$this->getKey($this->loginKey)] ?? null;
        if (empty($sessionKey)) {
            $memberInfo = $this->checkMember();

            if (!empty($memberInfo)) {
                return true;
            }

            $url = $_SERVER["QUERY_STRING"];
            if (!empty($this->params['backUrl'])) {
                $url = $this->params['backUrl'];
            }

            if (!empty($url)) {
                $url = urlencode(base64_encode($url));
            }

            $loginUrl = url("user/login", array("backUrl" => $url), true, true);

            if ($this->request->isAjax()) {
                throw new ApiException(strval($loginUrl), ExceptionEnum::API_LOGIN_ERROR_CODE);
            }

            header("location: " . $loginUrl);
            exit;
        }
        return true;
    }

    public function checkMember()
    {
        $sessionKey = $_COOKIE[$this->getKey($this->loginKey)] ?? null;
        $memberInfo = [];

        if (isset($sessionKey)) {
            $session = json_decode(base64_decode($sessionKey), true);

            if (is_array($session)) {
                $member = $this->getInfo(['username' => $session['username']]);
                if (is_array($member) && $session["member_hash"] == md5($member["password"] . $member["salt"])) {
                    $member['avatar'] = tomedia($member['avatar']);
                    $memberInfo       = $member;
                } else {
                    isetcookie($this->getKey($this->loginKey), false, -100);
                }
            }
        }
        return $memberInfo;
    }

    /**
     * 用户登录
     * @param $username
     * @param $password
     * @return bool
     * @throws ApiException
     */
    public function login($username, $password)
    {
        if (empty($username)) {
            throw new ApiException("请输入账号名称");
        }

        if (empty($password)) {
            throw new ApiException("请输入登录密码");
        }

        $where      = [];
        $where[]    = Db::raw("username = '{$username}' or mobile = '{$username}'");
        $memberInfo = $this->getInfo($where, "id,username,salt,password");

        if (empty($memberInfo)) {
            throw new ApiException("用户不存在");
        }

        if (md5($password . $memberInfo["salt"]) !== $memberInfo["password"]) {
            throw new ApiException("用户或密码错误");
        }

        $memberInfo["member_hash"] = md5($memberInfo["password"] . $memberInfo["salt"]);
        unset($memberInfo['id'], $memberInfo['password'], $memberInfo['salt']);

        $cookie = base64_encode(json_encode($memberInfo));
        isetcookie($this->getKey($this->loginKey), $cookie, 30 * 86400);
        return true;
    }

    public function logout()
    {
        return isetcookie($this->getKey($this->loginKey), false, -100);
    }

    /**
     * 用户注册
     * @param $username
     * @param $mobile
     * @param $password
     * @return int|string
     * @throws ApiException
     */
    public function register($username, $mobile, $password)
    {
        if (empty($username)) {
            throw new ApiException("请输入账号名称");
        }

        if (empty($mobile)) {
            throw new ApiException("请输入手机号码");
        }

        if (empty($password)) {
            throw new ApiException("请输入登录密码");
        }

        $where      = [];
        $where[]    = Db::raw("username = '{$username}' or mobile = '{$mobile}'");
        $memberInfo = $this->getInfo($where, "id,username,password,salt,mobile");

        if (!empty($memberInfo)) {
            if ($memberInfo['username'] == $username) {
                throw new ApiException("此账号已注册, 请直接登录");
            } else {
                throw new ApiException("此手机号已注册, 请直接登录");
            }
        }

        $salt     = RandomUtil::random(6);
        $openid   = "wap_user_" . $this->uniacid . "_" . $mobile;
        $nickname = substr($mobile, 0, 3) . "xxxx" . substr($mobile, 7, 4);

        $data = array(
            "uniacid"    => $this->uniacid,
            "username"   => $username,
            "mobile"     => $mobile,
            "nickname"   => $nickname,
            "openid"     => $openid,
            "password"   => md5($password . $salt),
            "salt"       => $salt,
            "createtime" => time(),
        );
        return $this->insertInfo($data);
    }

    /**
     * 忘记密码
     * @param $mobile
     * @param $password
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DbException
     */
    public function forget($mobile, $password)
    {
        if (empty($mobile)) {
            throw new ApiException("请输入手机号码");
        }

        if (empty($password)) {
            throw new ApiException("请输入登录密码");
        }

        $where           = [];
        $where['mobile'] = $mobile;
        $memberInfo      = $this->getInfo($where, "id");

        if (empty($memberInfo)) {
            throw new ApiException("账号不存在");
        }

        $salt = RandomUtil::random(6);

        $data = array(
            "password" => md5($password . $salt),
            "salt"     => $salt,
        );
        return $this->updateInfo($data, ['id' => $memberInfo['id']]);
    }

    /**
     * 用户余额支付
     * @param $orderInfo
     * @return bool
     * @throws ApiException
     */
    public function creditPay($orderInfo)
    {
        $memberInfo = $this->getInfo(['id' => $orderInfo['mid']]);
        if ($memberInfo['credit2'] < $orderInfo['price']) {
            throw new ApiException("余额不足");
        }

        try {
            $payResult = $this->updateInfo(['credit2' => Db::raw('credit2 - ' . $orderInfo['price'])], ['id' => $orderInfo['mid']]);

            $recordData = [
                'uniacid'    => $memberInfo['uniacid'],
                'mid'        => $memberInfo['id'],
                'type'       => -1,
                'fee'        => $orderInfo['price'],
                'amount'     => $memberInfo['credit2'],
                'note'       => $orderInfo['title'],
                'tag'        => serialize($orderInfo),
                'createtime' => time(),
            ];
            UserRecordServiceFacade::insertInfo($recordData);

            $data = [
                'out_trade_no' => $orderInfo['ordersn'],
                'pay_type'     => PayTypeEnum::CREDIT_TYPE,
            ];

            $orderPay = new Pay($data);
            $orderPay->payResult();
        } catch (Exception $exception) {
            throw new ApiException("支付失败");
        }

        return $payResult;
    }
}