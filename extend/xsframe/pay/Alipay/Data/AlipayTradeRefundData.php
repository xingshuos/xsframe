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
/**
 * Created by Date: 2019/4/18
 */

namespace xsframe\pay\Alipay\Data;


use xsframe\util\PriceUtil;

class AlipayTradeRefundData
{
    /** @var string 特殊可选    64    订单支付时传入的商户订单号,不能和 trade_no同时为空。 */
    public $out_trade_no = '';
    /** @var string 特殊可选    64    支付宝交易号，和商户订单号不能同时为空 */
    public $trade_no = '';
    /** @var string 必选    9    需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数    200.12 */
    public $refund_amount = '';
    /** @var string    可选    8    订单退款币种信息 */
    public $refund_currency = '';
    /** @var string 可选    256    退款的原因说明 */
    public $refund_reason = '';
    /** @var string 可选    64    标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。 */
    public $out_request_no = '';
    /** @var string 可选    30    商户的操作员编号 */
    public $operator_id = '';

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
    public function getTradeNo(): string
    {
        return $this->trade_no;
    }

    /**
     * @param string $trade_no
     */
    public function setTradeNo(string $trade_no): void
    {
        $this->trade_no = $trade_no;
    }

    /**
     * @return string
     */
    public function getRefundAmount(): string
    {
        return $this->refund_amount;
    }

    /**
     * @param string $refund_amount
     */
    public function setRefundAmount(string $refund_amount): void
    {
        $this->refund_amount = PriceUtil::fen2yuan($refund_amount);
    }

    /**
     * @return string
     */
    public function getRefundCurrency(): string
    {
        return $this->refund_currency;
    }

    /**
     * @param string $refund_currency
     */
    public function setRefundCurrency(string $refund_currency): void
    {
        $this->refund_currency = $refund_currency;
    }

    /**
     * @return string
     */
    public function getRefundReason(): string
    {
        return $this->refund_reason;
    }

    /**
     * @param string $refund_reason
     */
    public function setRefundReason(string $refund_reason): void
    {
        $this->refund_reason = $refund_reason;
    }

    /**
     * @return string
     */
    public function getOutRequestNo(): string
    {
        return $this->out_request_no;
    }

    /**
     * @param string $out_request_no
     */
    public function setOutRequestNo(string $out_request_no): void
    {
        $this->out_request_no = $out_request_no;
    }

    /**
     * @return string
     */
    public function getOperatorId(): string
    {
        return $this->operator_id;
    }

    /**
     * @param string $operator_id
     */
    public function setOperatorId(string $operator_id): void
    {
        $this->operator_id = $operator_id;
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