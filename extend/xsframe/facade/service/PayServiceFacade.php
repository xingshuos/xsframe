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

namespace xsframe\facade\service;


use xsframe\service\PayService;
use think\Facade;

/**
 * @method static wxNative($ordersn, $price, $serviceType, $title = '')
 * @method static aliPagePay($ordersn, $price, $serviceType, $title = '', string $returnUrl = '', bool $returnQrcode = false, int $qrcodeWidth = 300)
 * @method static aliRsaCheck(array $get, $signType = 'RSA2')
 * @method static wxPay(string $ordersn, float $price, string $title, int $service_type, string $openid = '')
 * @method static wapPay(mixed $ordersn, float $price, int $serviceType = 0, string $title = '', string $returnUrl = '')
 * @method static wxPayRefund(string $outTradeNo, string $outRefundNo, float $totalFee = 0, float $refundFee = '', string $opUserId = '')
 * @method static aliOauthToken(string $authCode)
 * @method static aliUserInfo(mixed $accessToken)
 * @method static aliRefund(string $ordersn, float $price, string $reason)
 */
class PayServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return PayService::class;
    }
}