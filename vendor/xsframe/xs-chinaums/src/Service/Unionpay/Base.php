<?php

namespace xsframe\chinaums\Service\Unionpay;

use xsframe\chinaums\Tools\Http;
use Exception;

class Base
{
    /**
     * @var array $config 网关
     */
    protected $config = [];

    /**
     * @var string 接口地址
     */
    protected $api;

    /**
     * @var array $body 请求参数
     */
    protected $body;
    /**
     * 必传的值
     * @var array
     */
    protected $require = [];
    public function __construct()
    {
    }

    public function request($data = [])
    {
        $data['mid'] = $this->config['mid'];
        $data['tid'] = $this->config['tid'];
        if ($data) {
            $this->body = array_merge($this->body, $data);
        }
        try {
            $this->validate();
            $data = $this->body;
            $sign = $this->generateSign($data);
            $gateway  = $this->config['gateway'] . $this->api;
            $data = json_encode($data);
            if ('cli' == php_sapi_name()) {
                echo 'api:' . $gateway . PHP_EOL;
                echo 'request:' . $data . PHP_EOL;
            }
            $headers = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
                'Authorization: ' . $sign
            ];
            $headers = $headers;
            $options = [
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 30
            ];
            $response = Http::post($gateway, $data, $options);
            return $response;
        } catch (Exception $e) {
            return json_encode(['errCode' => -1, 'errMsg' => $e->getMessage(), 'responseTimestamp' => null]);
        }
    }

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    public function setBody($value)
    {
        $this->body = array_merge($this->body, $value);
        return $this;
    }

    protected function validate()
    {
        $require = $this->require;
        $key = array_keys($this->body);
        foreach ($require as $v) {
            if (!in_array($v, $key)) {
                throw new Exception($v . ' is require！！');
            }
        }
        return true;
    }
    /**
     * 根绝类型生成sign
     * @param $params
     * @param string $signType
     * @return string
     */
    public function generateSign($body)
    {
        $body = (!is_string($body)) ? json_encode($body) : $body;
        $appid = $this->config['appid'];
        $appkey = $this->config['appkey'];
        $timestamp = date("YmdHis", time());
        $nonce = md5(uniqid(microtime(true), true));
        $str = bin2hex(hash('sha256', $body, true));
        $signature = base64_encode(hash_hmac('sha256', "$appid$timestamp$nonce$str", $appkey, true));
        $authorization = "OPEN-BODY-SIG AppId=\"$appid\", Timestamp=\"$timestamp\", Nonce=\"$nonce\", Signature=\"$signature\"";
        return $authorization;
    }

    public function __set($name, $value)
    {
        $this->body[$name] = $value;
    }
}
