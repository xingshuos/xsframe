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

/**
 * Created by Date: 2019/4/13
 */

namespace xsframe\pay\Alipay\Data;


use xsframe\util\PriceUtil;

/**
 * 支付宝交易参数数据转化类
 * Class AlipayTradeAppPayData
 *
 * @package Pay\Alipay\Data
 */
class AlipayTradeAppPayData
{
    /** @var string 对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。 */
    public $body = '';
    /** @var string 商品的标题/交易标题/订单标题/订单关键字等。 */
    public $subject = '';
    /** @var string 商户网站唯一订单号 */
    public $out_trade_no = '';
    /** @var string 该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。
     * 注：若为空，则默认为15d。 */
    public $timeout_express = null;
    /** @var string  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000] */
    public $total_amount;
    /** @var string 销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY */
    public $product_code = '';
    /** @var string 商品主类型：0—虚拟类商品，1—实物类商品  注：虚拟类商品不支持使用花呗渠道 */
    public $goods_type = '1';
    /** @var string 公用回传参数，如果请求时传递了该参数，则返回给商户时会回传该参数。支付宝会在异步通知时将该参数原样返回。本参数必须进行UrlEncode之后才可以发送给支付宝    merchantBizType%3d3C%26merchantBizNo%3d2016010101111 */
    public $passback_params = '';

    /** @var string 优惠参数 注：仅与支付宝协商后可用 */
    public $promo_params = '';
    /** @var string 业务扩展参数，详见下面的“业务扩展参数说明 https://docs.open.alipay.com/204/105465” json {"sys_service_provider_id":"2088511833207846"} */
    public $extend_params = '';

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getOutTradeNo(): string
    {
        return $this->out_trade_no;
    }

    /**
     * @param string $out_trade_no
     */
    public function setOutTradeNo(string $out_trade_no): void
    {
        $this->out_trade_no = $out_trade_no;
    }

    /**
     * @return string
     */
    public function getTimeoutExpress(): string
    {
        return $this->timeout_express;
    }

    /**
     * @param string $timeout_express
     */
    public function setTimeoutExpress(string $timeout_express): void
    {
        $this->timeout_express = $timeout_express;
    }

    /**
     * @return string
     */
    public function getTotalAmount(): string
    {
        return $this->total_amount;
    }

    /**
     * @param string $total_amount
     */
    public function setTotalAmount(string $total_amount): void
    {
        $total_amount       = PriceUtil::fen2yuan($total_amount);
        $this->total_amount = $total_amount;
    }

    /**
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->product_code;
    }

    /**
     * @param string $product_code
     */
    public function setProductCode(string $product_code): void
    {
        $this->product_code = $product_code;
    }

    /**
     * @return string
     */
    public function getGoodsType(): string
    {
        return $this->goods_type;
    }

    /**
     * @param string $goods_type
     */
    public function setGoodsType(string $goods_type): void
    {
        $this->goods_type = $goods_type;
    }

    /**
     * @return string
     */
    public function getPassbackParams(): string
    {
        return $this->passback_params;
    }

    /**
     * @param array $passback_params
     */
    public function setPassbackParams(array $passback_params): void
    {
        $passback_params       = urlencode(json_encode($passback_params));
        $this->passback_params = $passback_params;
    }

    /**
     * @return string
     */
    public function getPromoParams(): string
    {
        return $this->promo_params;
    }

    /**
     * @param string $promo_params
     */
    public function setPromoParams(string $promo_params): void
    {
        $this->promo_params = $promo_params;
    }

    /**
     * @return string
     */
    public function getExtendParams(): string
    {
        return $this->extend_params;
    }

    /**
     * @param string $extend_params
     */
    public function setExtendParams(string $extend_params): void
    {
        $this->extend_params = $extend_params;
    }

    public function __toString()
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE);
    }

    public function toArray()
    {
        return (array) $this;
    }
}