<?php


namespace app\store\controller\pc;

use app\store\facade\service\MemberServiceFacade;
use app\store\facade\service\SmsServiceFacade;
use xsframe\exception\ApiException;

class Sms extends Base
{
    // 验证验证码准确性
    public function check()
    {
        $mobile = trim($this->params['mobile']) ?? '';
        $code   = trim($this->params['code']) ?? '';

        $memberExit = MemberServiceFacade::getTotal(['mobile' => $mobile, 'uniacid' => $this->uniacid]);
        if (!empty($memberExit)) {
            throw new ApiException("此手机号已注册，请直接登录");
        }

        $result = [
            'isCheck' => SmsServiceFacade::checkSmsCode($mobile, $code, false),
        ];

        return $this->success($result);
    }

    // 发送注册验证码
    public function register()
    {
        $mobile = trim($this->params['mobile']) ?? '';

        $smsSet = $this->moduleSetting['sms'];

        $memberExit = MemberServiceFacade::getTotal(['mobile' => $mobile, 'uniacid' => $this->uniacid]);
        if (!empty($memberExit)) {
            throw new ApiException("此手机号已注册，请直接登录");
        }

        $result = [
            'isSend' => SmsServiceFacade::sendSMS($smsSet, $mobile, $smsSet["register_code"]),
        ];

        return $this->success($result);
    }

    // 找回密码
    public function forget()
    {
        $mobile = trim($this->params['mobile']) ?? '';

        $smsSet = $this->moduleSetting['sms'];

        $memberExit = MemberServiceFacade::getTotal(['mobile' => $mobile, 'uniacid' => $this->uniacid]);
        if (empty($memberExit)) {
            throw new ApiException("此手机号未注册");
        }

        $result = [
            'isSend' => SmsServiceFacade::sendSMS($smsSet, $mobile, $smsSet["update_code"]),
        ];

        return $this->success($result);
    }

}