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

namespace app\store\controller;

use app\store\facade\service\OrderServiceFacade;

class Pay
{
    private $params = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function payResult()
    {
        $orderInfo = OrderServiceFacade::getInfo(['ordersn' => $this->params['out_trade_no']], 'id,uniacid,mid,status,service_type');
        if (!empty($orderInfo) && intval($orderInfo['status']) == 0) {
            OrderServiceFacade::updateInfo(['status' => 1, 'paytype' => $this->params['pay_type'], 'paytime' => time(), 'transaction_id' => $this->params['transaction_id'] ?? ''], ['id' => $orderInfo['id']]);
        }
        return OrderServiceFacade::payResult($orderInfo['id'], $orderInfo['service_type']);
    }
}