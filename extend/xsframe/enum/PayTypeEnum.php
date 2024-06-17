<?php


namespace xsframe\enum;

use xsframe\base\BaseEnum;

class PayTypeEnum extends BaseEnum
{
    # 微信支付
    const WXPAY_TYPE = 1;

    # 支付宝支付
    const ALIPAY_TYPE = 2;

    # 余额支付
    const CREDIT_TYPE = 3;

    # 后台支付
    const BACK_TYPE = 4;
}