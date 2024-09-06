<?php

namespace xsframe\chinaums\Service\Wechat;

use xsframe\chinaums\Service\Wechat\Base;

/**
 * 小程序下单接口
 */
class Mini extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/netpay/wx/unified-order';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'tradeType' => 'MINI_PAY'
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = ['requestTimestamp', 'merOrderId', 'mid', 'tid', 'subAppId', 'subOpenId', 'tradeType'];
}
