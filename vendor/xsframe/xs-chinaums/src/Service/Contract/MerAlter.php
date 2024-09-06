<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Service\Contract\Base;
use xsframe\chinaums\Tools\DES;
use Exception;

/**
 * 平台类全前台商户变更接入接口
 */
class MerAlter extends Base
{
    /**
     * 平台 PC H5
     *
     * @var string
     */
    protected $platform = 'PC';
    /**
     * @var string h5接口地址
     */
    protected $api = '/self-sign-mobile/#/chooseChangeType';
    /**
     * @var string pc接口地址
     */
    protected $PCapi = '/self-sign-web/#/alterHome';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'service' => 'merAlter',
        'sign_type' => 'SHA-256',
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = ['service', 'accesser_id', 'sign_type', 'request_date', 'request_seq', 'mchnNo'];
    public function setPlatform($value)
    {
        $this->platform = $value;
        return $this;
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
            if (strtoupper($this->platform) == 'PC') {
                $gateway  = $this->config['gateway'] . $this->PCapi;
            }
            $data = json_encode($data);
            $sign = hash('sha256', $data);
            $method = $this->method;
            $des = new DES($key, $method, DES::OUTPUT_HEX);
            // 加密
            $str = $des->encrypt($data);
            $url = $gateway . '?sign_data=' . $sign . '&json_data=' . $str . '&accesser_id=' . $this->config['accesser_id'];
            return json_encode(['res_code' => '0000', 'res_msg' => 'success', 'url' => $url], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            return json_encode(['res_code' => -1, 'res_msg' => $e->getMessage(), 'request_seq' => null]);
        }
    }
}
