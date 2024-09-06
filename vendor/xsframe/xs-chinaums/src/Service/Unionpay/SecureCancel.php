<?php

namespace xsframe\chinaums\Service\Unionpay;

use xsframe\chinaums\Service\Unionpay\Base;

/**
 * 担保撤销
 */
class SecureCancel extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/netpay/refund-query';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = ['requestTimestamp', 'merOrderId', 'mid', 'tid','instMid'];
}
