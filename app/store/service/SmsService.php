<?php

namespace app\store\service;

use xsframe\base\BaseService;
use xsframe\enum\ExceptionEnum;
use xsframe\exception\ApiException;
use xsframe\facade\service\SmsServiceFacade;
use xsframe\util\RandomUtil;

class SmsService extends BaseService
{
    private $codeKey = "user_verify_code_session_";
    private $codeTimeKey = "user_verify_code_sendtime_";

    // 校验验证码
    public function checkSmsCode($mobile, $verifyCode, $clear = true)
    {
        $key     = $this->getKey($this->codeKey . $mobile);
        $keyTime = $this->getKey($this->codeTimeKey . $mobile);

        $sendCode = $this->getCache($key);
        $sendTime = $this->getCache($keyTime);

        # 通用验证码 6868
        if ($verifyCode == 6868) {
            return true;
        }

        if (!preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
            throw new ApiException("请输入正确的手机号!");
        }

        if (!isset($sendCode) || $sendCode !== $verifyCode) {
            throw new ApiException("验证码错误!");
        }

        if (!isset($sendTime) || 600 * 1000 < time() - $sendTime) {
            throw new ApiException("验证码失效，请重新获取!");
        }

        if ($clear) {
            $this->clearCache($key);
            $this->clearCache($keyTime);
        }

        return true;
    }

    // 发送验证码
    public function sendSMS($smsSet, $mobile, $tplId)
    {
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
            throw new ApiException(ExceptionEnum::getText(ExceptionEnum::SMS_MOBILE_ERROR));
        }

        if (empty($tplId)) {
            throw new ApiException(ExceptionEnum::SMS_SMSID_ERROR);
        }

        if (empty($smsSet)) {
            throw new ApiException(ExceptionEnum::SMS_PARAMS_ERROR);
        }

        $key     = $this->getKey($this->codeKey . $mobile);
        $keyTime = $this->getKey($this->codeTimeKey . $mobile);

        $sendTime = $this->getCache($keyTime);

        if (!is_numeric($sendTime)) {
            $sendTime = 0;
        }

        $time = time() - $sendTime;

        if ($time < 60) {
            throw new ApiException(ExceptionEnum::getText(ExceptionEnum::SMS_RATE_ERROR));
        }

        $code = RandomUtil::random(4, true);

        # TODO 测试中 start
        // $this->setCache($key, $code, 10 * 60);
        // $this->setCache($keyTime, time(), 10 * 60);
        // return $code;
        # TODO 测试中 end

        $accessKeyId     = $smsSet['accessKeyId'];
        $accessKeySecret = $smsSet['accessKeySecret'];
        $signName        = $smsSet['sign'];

        $ret = SmsServiceFacade::send($accessKeyId, $accessKeySecret, $signName, $mobile, $tplId, array('code' => $code));

        if ($ret['status']) {
            $this->setCache($key, $code, 10 * 60);
            $this->setCache($keyTime, time(), 10 * 60);
        } else {
            throw new ApiException($ret['message']);
        }

        return true;
    }

}