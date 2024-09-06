<?php

namespace xsframe\chinaums\Provider;

use xsframe\chinaums\Tools\Str;
use xsframe\chinaums\Tools\DES;
use Exception;


/**
 * 自助签约
 */
class Contract
{
    protected $config = [];
    /**
     * 加密算法
     *
     * @var string
     */
    protected $method = 'DES-EDE3';
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function __call(string $shortcut, array $params)
    {
        $class = '\\xsframe\\chinaums\\Service\\Contract\\' . Str::studly($shortcut);
        $objcet = new $class();
        $objcet->setConfig($this->config);
        return $objcet;
    }

    public function picUpload($order)
    {
        return $this->__call('PicUpload', [])->request($order);
    }

    public function complexUpload($order)
    {
        return $this->__call('ComplexUpload', [])->request($order);
    }

    public function requestAccountVerify($order)
    {
        return $this->__call('RequestAccountVerify', [])->request($order);
    }

    public function companyAccountVerify($order)
    {
        return $this->__call('CompanyAccountVerify', [])->request($order);
    }

    public function agreementSign($order)
    {
        return $this->__call('AgreementSign', [])->request($order);
    }

    public function applyQry($order)
    {
        return $this->__call('ApplyQry', [])->request($order);
    }

    public function callback($contents)
    {
        $json_data = $contents['json_data'] ?? '';
        $sign_data = $contents['sign_data'] ?? '';
        if (!$sign_data) {
            return $this->error('sign_data is not empty.');
        }
        if (!$json_data) {
            return $this->error('json_data is not empty.');
        }
        $method = $this->method;
        $key = $this->config['private_key'];
        $des = new DES($key, $method, DES::OUTPUT_HEX);
        $str = $des->decrypt($json_data);
        $sign = strtolower(hash('sha256', $str));
        if (trim($sign) !== trim(strtolower($sign_data))) {
            return $this->error('sign_data is invalid.');
        }
        return $str;
    }

    public function success($msg = '', $code = '0000')
    {
        $result = [
            'res_msg' => $msg,
            'res_code' => $code,
        ];
        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    public function error($msg, $code = '-1')
    {
        $result = [
            'res_msg' => $msg,
            'res_code' => $code,
        ];
        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }
}
