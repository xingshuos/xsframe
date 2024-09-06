<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Tools\Http;
use xsframe\chinaums\Tools\DES;
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
    /**
     * 加密算法
     *
     * @var string
     */
    protected $method = 'DES-EDE3';
    /**
     * http请求方式 
     *
     * @var string get post
     */
    protected $httpMethod = 'get';
    public function __construct()
    {
    }

    public function request($data = [])
    {
        $data['accesser_id'] = $this->config['accesser_id'];
        $key = $this->config['private_key'];
        if ($data) {
            $this->body = array_merge($this->body, $data);
        }
        try {
            $this->validate();
            $data = $this->body;
            $gateway  = $this->config['gateway'] . $this->api;
            $data = json_encode($data);
            $sign = hash('sha256', $data);
            $method = $this->method;
            $des = new DES($key, $method, DES::OUTPUT_HEX);
            // 加密
            $str = $des->encrypt($data);
            if ('cli' == php_sapi_name()) {
                echo 'api:' . $gateway . PHP_EOL;
                echo 'request:' . $data . PHP_EOL;
            }
            $headers = [
                'Content-Type: application/json',
            ];
            $headers = $headers;
            $options = [
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 30
            ];
            $params = [
                'json_data' => $str,
                'sign_data' => $sign,
                'accesser_id' => $this->config['accesser_id']
            ];
            $httpMethod = strtolower($this->httpMethod);
            $response = Http::$httpMethod($gateway, $params, $options);
            return $response;
        } catch (Exception $e) {
            return json_encode(['res_code' => -1, 'res_msg' => $e->getMessage(), 'request_seq' => null]);
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

    public function __set($name, $value)
    {
        $this->body[$name] = $value;
    }
}
