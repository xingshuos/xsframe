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
use xsframe\base\BaseService;
use xsframe\enum\ExceptionEnum;
use xsframe\exception\ApiException;
use xsframe\util\ErrorUtil;
use xsframe\util\RandomUtil;
use xsframe\util\RequestUtil;

class SmsService extends BaseService
{
    private $codeKey = "member_verify_code_session_";
    private $codeTimeKey = "member_verify_code_sendtime_";

    // 发送注册验证码
    public function sendLoginCode($mobile, $tplId = null, $smsSet = null)
    {
        if (empty($smsSet)) {
            $smsSet = $this->moduleSetting['sms'];
        }

        if (!empty($smsSet)) {
            if (empty($tplId)) {
                $tplId = $smsSet['login_code'];
            }
        }

        return $this->sendSMS($smsSet, $mobile, $tplId);
    }

    // 发送验证码
    public function sendSMS($smsSet, $mobile, $tplId)
    {
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
            throw new ApiException(ExceptionEnum::getText(ExceptionEnum::SMS_MOBILE_ERROR));
        }

        if (empty($tplId)) {
            throw new ApiException(ExceptionEnum::getText(ExceptionEnum::SMS_SMSID_ERROR));
        }

        if (empty($smsSet) || empty($smsSet['accessKeyId']) || empty($smsSet['accessKeySecret']) || empty($smsSet['sign'])) {
            throw new ApiException(ExceptionEnum::getText(ExceptionEnum::SMS_PARAMS_ERROR));
        }

        $key = $this->getKey($this->codeKey . $mobile);
        $keyTime = $this->getKey($this->codeTimeKey . $mobile);

        $sendTime = Cache::get($keyTime);

        if (!is_numeric($sendTime)) {
            $sendTime = 0;
        }

        $time = time() - $sendTime;

        if ($time < 60) {
            throw new ApiException(ExceptionEnum::getText(ExceptionEnum::SMS_RATE_ERROR));
        }

        $code = RandomUtil::random(4, true);

        $accessKeyId = $smsSet['accessKeyId'];
        $accessKeySecret = $smsSet['accessKeySecret'];
        $signName = $smsSet['sign'];

        $ret = self::send($accessKeyId, $accessKeySecret, $signName, $mobile, $tplId, ['code' => $code]);

        if ($ret['status']) {
            Cache::set($key, $code, 10 * 60);
            Cache::set($keyTime, TIMESTAMP, 10 * 60);
        } else {
            throw new ApiException($ret['message']);
        }

        return true;
    }

    // 校验验证码
    public function checkSmsCode($mobile, $verifyCode, $testCode = null, $clear = true)
    {
        $key = $this->getKey($this->codeKey . $mobile);
        $keyTime = $this->getKey($this->codeTimeKey . $mobile);

        $sendCode = Cache::get($key);
        $sendTime = Cache::get($keyTime);

        # 通用验证码 调试方便
        if ($verifyCode == $testCode) {
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
            Cache::delete($key);
            Cache::delete($keyTime);
        }

        return true;
    }

    /**
     * 发送短信
     * @param string $rootAccessKeyId
     * @param string $rootAccessKeySecret
     * @param $signName
     * @param int $mobile 手机号
     * @param string $tplId 短信模板iID
     * @param array $data 发送数据  $replace=true $data替换模板数据  $replace=false 则直接使用$data作为发送数据
     * @param true $replace 是否替换数据
     * @return
     */
    public function send($rootAccessKeyId, $rootAccessKeySecret, $signName, $mobile, $tplId, $data, $replace = true)
    {
        date_default_timezone_set('GMT');
        $post = [
            'PhoneNumbers'     => $mobile,
            'SignName'         => $signName,
            'TemplateCode'     => trim($tplId),
            'OutId'            => '',
            'RegionId'         => 'cn-hangzhou',
            'AccessKeyId'      => $rootAccessKeyId,
            'Format'           => 'json',
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            'SignatureNonce'   => uniqid(),
            'Timestamp'        => date('Y-m-d\\TH:i:s\\Z'),
            'Action'           => 'SendSms',
            'Version'          => '2017-05-25'
        ];

        if (!empty($data)) {
            $post['TemplateParam'] = json_encode($data);
        }

        ksort($post);
        $str = '';

        foreach ($post as $key => $value) {
            $str .= '&' . $this->encode($key) . '=' . $this->encode($value);
        }

        $stringToSign = 'GET' . '&%2F&' . $this->encode(substr($str, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $rootAccessKeySecret . '&', true));
        $post['Signature'] = $signature;
        $url = 'http://dysmsapi.aliyuncs.com/?' . http_build_query($post);

        $result = RequestUtil::httpGet($url);

        $ret = [
            'status' => 0,
        ];

        $result = @json_decode($result, true);
        if (ErrorUtil::isError($result)) {
            $ret['message'] = "短信发送失败";
        } else {
            if ($result['Code'] != 'OK') {
                if (isset($result['Code'])) {
                    $msg = $this->sms_error_code($result['Code']);
                    $ret['message'] = $msg['msg'];
                } else {
                    $ret['message'] = "短信发送失败";
                }
            } else {
                $ret['status'] = 1;
                $ret['message'] = "success";
            }
        }

        return $ret;
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private function sms_error_code($code)
    {
        $msgs = [
            'isv.OUT_OF_SERVICE'      => [
                'msg'    => '业务停机',
                'handle' => '登陆www.alidayu.com充值',
            ],
            'isv.PRODUCT_UNSUBSCRIBE' => [
                'msg'    => '产品服务未开通',
                'handle' => '登陆www.alidayu.com开通相应的产品服务',
            ],
            'isv.ACCOUNT_NOT_EXISTS'  => [
                'msg'    => '账户信息不存在',
                'handle' => '登陆www.alidayu.com完成入驻',
            ],
            'isv.ACCOUNT_ABNORMAL'    => [
                'msg'    => '账户信息异常',
                'handle' => '联系技术支持',
            ],

            'isv.SMS_TEMPLATE_ILLEGAL' => [
                'msg'    => '模板不合法',
                'handle' => '登陆www.alidayu.com查询审核通过短信模板使用',
            ],

            'isv.SMS_SIGNATURE_ILLEGAL'   => [
                'msg'    => '签名不合法',
                'handle' => '登陆www.alidayu.com查询审核通过的签名使用',
            ],
            'isv.MOBILE_NUMBER_ILLEGAL'   => [
                'msg'    => '手机号码格式错误',
                'handle' => '使用合法的手机号码',
            ],
            'isv.MOBILE_COUNT_OVER_LIMIT' => [
                'msg'    => '手机号码数量超过限制',
                'handle' => '批量发送，手机号码以英文逗号分隔，不超过200个号码',
            ],

            'isv.TEMPLATE_MISSING_PARAMETERS' => [
                'msg'    => '短信模板变量缺少参数',
                'handle' => '确认短信模板中变量个数，变量名，检查传参是否遗漏',
            ],
            'isv.INVALID_PARAMETERS'          => [
                'msg'    => '参数异常',
                'handle' => '检查参数是否合法',
            ],
            'isv.BUSINESS_LIMIT_CONTROL'      => [
                'msg'    => '触发业务流控限制',
                'handle' => '短信验证码，使用同一个签名，对同一个手机号码发送短信验证码，允许每分钟1条，累计每小时7条。 短信通知，使用同一签名、同一模板，对同一手机号发送短信通知，允许每天50条（自然日）',
            ],

            'isv.INVALID_JSON_PARAM' => [
                'msg'    => '触发业务流控限制',
                'handle' => 'JSON参数不合法	JSON参数接受字符串值',
            ],

            'isp.RAM_PERMISSION_DENY' => [
                'msg'    => 'RAM权限拒绝',
                'handle' => 'RAM权限拒绝',
            ],
        ];

        return $msgs[$code];
    }


}