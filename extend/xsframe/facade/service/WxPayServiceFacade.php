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


use xsframe\service\WxPayService;
use think\Facade;

/**
 * @method static unifiedOrder($goodsBody, int $orderPrice, $outTradeNo, string $attach, string $tradeType, string $goodsTag, string $openid, string $bundleName, false|string $timeExpire)
 * @method static WxPayAppPay($unifiedReturn)
 */
class WxPayServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return WxPayService::class;
    }
}