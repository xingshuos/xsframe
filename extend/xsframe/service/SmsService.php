<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\service;


use xsframe\util\ErrorUtil;
use xsframe\util\RequestUtil;
use think\facade\Env;

class SmsService
{
    private $aliyunUrl = "http://cf.51welink.com/submitdata/service.asmx/g_Submit?";

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
        $post = array(
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
        );

        if (!empty($data)) {
            $post['TemplateParam'] = json_encode($data);
        }

        ksort($post);
        $str = '';

        foreach ($post as $key => $value) {
            $str .= '&' . $this->encode($key) . '=' . $this->encode($value);
        }

        $stringToSign      = 'GET' . '&%2F&' . $this->encode(substr($str, 1));
        $signature         = base64_encode(hash_hmac('sha1', $stringToSign, $rootAccessKeySecret . '&', true));
        $post['Signature'] = $signature;
        $url               = 'http://dysmsapi.aliyuncs.com/?' . http_build_query($post);

        $result = RequestUtil::httpGet($url);

        $ret = [
            'status'  => 0,
            'message' => ''
        ];

        $result = @json_decode($result, true);
        if (ErrorUtil::isError($result)) {
            $ret['message'] = "短信发送失败";
        }

        if ($result['Code'] != 'OK') {
            if (isset($result['Code'])) {
                $msg            = $this->sms_error_code($result['Code']);
                $ret['message'] = $msg['msg'];
            } else {
                $ret['message'] = "短信发送失败";
            }
        } else {
            $ret['status']  = 1;
            $ret['message'] = "success";
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
        $msgs = array(
            'isv.OUT_OF_SERVICE'      => array(
                'msg'    => '业务停机',
                'handle' => '登陆www.alidayu.com充值',
            ),
            'isv.PRODUCT_UNSUBSCRIBE' => array(
                'msg'    => '产品服务未开通',
                'handle' => '登陆www.alidayu.com开通相应的产品服务',
            ),
            'isv.ACCOUNT_NOT_EXISTS'  => array(
                'msg'    => '账户信息不存在',
                'handle' => '登陆www.alidayu.com完成入驻',
            ),
            'isv.ACCOUNT_ABNORMAL'    => array(
                'msg'    => '账户信息异常',
                'handle' => '联系技术支持',
            ),

            'isv.SMS_TEMPLATE_ILLEGAL' => array(
                'msg'    => '模板不合法',
                'handle' => '登陆www.alidayu.com查询审核通过短信模板使用',
            ),

            'isv.SMS_SIGNATURE_ILLEGAL'   => array(
                'msg'    => '签名不合法',
                'handle' => '登陆www.alidayu.com查询审核通过的签名使用',
            ),
            'isv.MOBILE_NUMBER_ILLEGAL'   => array(
                'msg'    => '手机号码格式错误',
                'handle' => '使用合法的手机号码',
            ),
            'isv.MOBILE_COUNT_OVER_LIMIT' => array(
                'msg'    => '手机号码数量超过限制',
                'handle' => '批量发送，手机号码以英文逗号分隔，不超过200个号码',
            ),

            'isv.TEMPLATE_MISSING_PARAMETERS' => array(
                'msg'    => '短信模板变量缺少参数',
                'handle' => '确认短信模板中变量个数，变量名，检查传参是否遗漏',
            ),
            'isv.INVALID_PARAMETERS'          => array(
                'msg'    => '参数异常',
                'handle' => '检查参数是否合法',
            ),
            'isv.BUSINESS_LIMIT_CONTROL'      => array(
                'msg'    => '触发业务流控限制',
                'handle' => '短信验证码，使用同一个签名，对同一个手机号码发送短信验证码，允许每分钟1条，累计每小时7条。 短信通知，使用同一签名、同一模板，对同一手机号发送短信通知，允许每天50条（自然日）',
            ),

            'isv.INVALID_JSON_PARAM' => array(
                'msg'    => '触发业务流控限制',
                'handle' => 'JSON参数不合法	JSON参数接受字符串值',
            ),

            'isp.RAM_PERMISSION_DENY' => array(
                'msg'    => 'RAM权限拒绝',
                'handle' => 'RAM权限拒绝',
            ),
        );

        return $msgs[$code];
    }


}