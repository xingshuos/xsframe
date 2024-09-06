<?php

namespace xsframe\chinaums\Service\Unionpay;

use xsframe\chinaums\Service\Unionpay\Base;

/**
 * 银联下单接口
 */
class Mini extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/netpay/upg/order';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = ['authorization', 'appId', 'timestamp', 'nonce', 'content', 'signature'];
}
