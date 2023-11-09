<?php

namespace app\store\controller\pc;

use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\MemberServiceFacade;
use app\store\facade\service\OrderDataServiceFacade;
use app\store\facade\service\OrderGoodsServiceFacade;
use app\store\facade\service\OrderServiceFacade;
use xsframe\exception\ApiException;
use xsframe\facade\service\PayServiceFacade;
use xsframe\util\ErrorUtil;
use xsframe\util\PriceUtil;
use think\facade\Db;

class Order extends Base
{
    public function index()
    {
        $userId      = $this->userId;
        $status      = $this->params['status'] ?? 0;
        $serviceType = $this->params['serviceType'] ?? 1;

        $condition = [
            'mid'     => $userId,
            'deleted' => 0,
        ];

        switch ($serviceType) {
            case 0:// 全部订单
                break;
            case 1: // 购买商品
                $condition['service_type'] = 1;
                break;
            case 2: // 购买课程
                $condition['service_type'] = 2;
                break;
        }

        switch ($status) {
            case 0:// 全部订单
                break;
            case 1: // 未支付
                $condition['status'] = 0;
                break;
            case 2: // 已支付(待发货)
                $condition['status'] = 1;
                break;
            case 3: // 已发货 待收货
                $condition['status'] = 2;
                break;
            case 4: // 已完成 待评价
                $condition['status'] = 3;
                break;
            case 5: // 已完成 已评价
                $condition['status'] = 3;
                break;
            case 6: // 售后
                $condition['status'] = Db::raw(' 4 or 5 ');
                break;
        }

        $orderList = OrderServiceFacade::getOrderList($condition);

        $result = [
            'orderList' => $orderList,
        ];
        return $this->success($result);
    }

    public function confirm()
    {
        $id      = intval($this->params['id'] ?? 0);
        $total   = intval($this->params['total'] ?? 1);
        $cartIds = $this->params['cartids'] ?? '';

        $result = [
            'id'      => $id,
            'total'   => $total,
            'cartIds' => $cartIds,
        ];

        return $this->template(empty($cartIds) ? 'pc/order/confirm' : 'pc/order/confirmCart', $result);
    }

    public function payment()
    {
        $result = [
            'ordersn' => $this->params['ordersn']
        ];
        return $this->template('pc/order/payment', $result);
    }

    /**
     * 创建订单
     * @return array
     */
    public function create()
    {
        $userId    = $this->userId;
        $id        = $this->params['id'] ?? 0;
        $type      = $this->params['type'] ?? 1;
        $total     = $this->params['total'] ?? 1;
        $addressId = $this->params['addressId'] ?? 0;
        $cartIds   = $this->params['cartIds'] ?? 0;
        $remark    = $this->params['remark'] ?? '';

        $ordersn = OrderServiceFacade::create($userId, $id, $type, $total, $addressId, $cartIds, $remark);

        $result = [
            'ordersn' => $ordersn,
        ];
        return $this->success($result);
    }

    /**
     * 订单详情
     * @return array
     * @throws ApiException
     */
    public function detail()
    {
        $ordersn   = $this->params['ordersn'];
        $orderInfo = OrderServiceFacade::getOrderInfo($this->userId, $ordersn);

        $result = [
            'orderInfo' => $orderInfo,
        ];
        return $this->success($result);
    }

    /**
     * 订单其他数据
     * @return array
     */
    public function data()
    {
        $userId  = $this->userId;
        $orderId = $this->params['orderid'];

        $orderInfo = OrderDataServiceFacade::getDataInfo($userId, $orderId);

        $result = [
            'orderInfo' => $orderInfo,
        ];
        return $this->success($result);
    }

    /**
     * 余额支付
     * @return array
     * @throws ApiException
     */
    public function credit()
    {
        $ordersn   = $this->params['ordersn'];
        $orderInfo = OrderServiceFacade::getOrderInfo($this->userId, $ordersn);

        if ($orderInfo['status'] != 0) {
            throw new ApiException($orderInfo['status'] == -1 ? "该订单已超时" : "该订单已支付");
        }

        MemberServiceFacade::creditPay($orderInfo);
        $url = $this->getReturnUrlByServiceType($orderInfo);

        $result = [
            'url' => $url,
        ];
        return $this->success($result);
    }

    /**
     * 微信支付
     * @return \think\response\View
     * @throws ApiException
     */
    public function wechat()
    {
        $ordersn   = $this->params['ordersn'];
        $orderInfo = OrderServiceFacade::getOrderInfo($this->userId, $ordersn);

        if ($orderInfo['status'] != 0) {
            throw new ApiException($orderInfo['status'] == -1 ? "该订单已超时" : "该订单已支付");
        }

        $unifiedReturn = PayServiceFacade::wxNative($ordersn, $orderInfo['price'], $orderInfo['service_type'], $orderInfo['title']);
        
        $errMsg  = "";
        $codeUrl = "";

        if (ErrorUtil::isError($unifiedReturn)) {
            $errMsg = $unifiedReturn['msg'];
        } else {
            $codeUrl = $unifiedReturn['code_url'];
        }

        $result = [
            'ordersn' => $ordersn,
            'price'   => PriceUtil::numberFormat($this->params['price']),
            'codeUrl' => $codeUrl,
            'errMsg'  => $errMsg,
        ];

        return $this->template('pc/order/wechat', $result);
    }

    /**
     * 支付宝支付
     * @return \think\response\View
     * @throws ApiException
     */
    public function alipay()
    {
        $ordersn   = $this->params['ordersn'];
        $returnUrl = $this->params['returnUrl'] ?? '';
        $orderInfo = OrderServiceFacade::getOrderInfo($this->userId, $ordersn);

        if ($orderInfo['status'] != 0) {
            throw new ApiException($orderInfo['status'] == -1 ? "该订单已超时" : "该订单已支付");
        }

        $codeUrl = PayServiceFacade::aliPagePay($ordersn, $orderInfo['price'], $orderInfo['service_type'], $orderInfo['title'], $returnUrl, true, 280);

        $result = [
            'ordersn' => $ordersn,
            'price'   => PriceUtil::numberFormat($this->params['price']),
            'codeUrl' => $codeUrl,
        ];
        return $this->template('pc/order/alipay', $result);
    }

    /**
     * 验证订单是否支付
     * @return array
     */
    public function check()
    {
        $ordersn   = $this->params['ordersn'];
        $orderInfo = OrderServiceFacade::getOrderInfo($this->userId, $ordersn);

        $url = $this->getReturnUrlByServiceType($orderInfo);

        $result = [
            'isPay' => $orderInfo['status'],
            'url'   => $url,
        ];
        return $this->success($result);
    }

    /**
     * 取消订单
     * @return array
     * @throws ApiException
     */
    public function cancel()
    {
        $ordersn   = $this->params['ordersn'];
        $orderInfo = OrderServiceFacade::getOrderInfo($this->userId, $ordersn);

        if ($orderInfo['status'] != 0) {
            throw new ApiException("该状态下不允许用户取消订单");
        }

        $updateRes = OrderServiceFacade::updateInfo(['status' => -1, 'canceltime' => time()], ['ordersn' => $ordersn]);

        if ($orderInfo['service_type'] == 1) {
            $orderGoodsList = OrderGoodsServiceFacade::getAll(['orderid' => $orderInfo['id']], "id,goodsid,total");
            $orderGoodsList = GoodsServiceFacade::listMergeGoodsInfo($orderGoodsList, 'goodsid');
            foreach ($orderGoodsList as $orderGoods) {
                if ($orderGoods['goods']['total'] != -1) {
                    GoodsServiceFacade::updateInfo(['total' => Db::raw('total + ' . $orderGoods['total'])], ['id' => $orderGoods['goodsid']]);
                }
            }
        }

        $result = [
            'isCancel' => $updateRes,
        ];
        return $this->success($result);
    }

    private function getReturnUrlByServiceType($orderInfo)
    {
        switch (intval($orderInfo['service_type'])) {
            case 1:
                $url = url("user/order", ['status' => 2], true, true);
                break;
            case 2:
                $url = url("goods/" . $orderInfo['course']['goodsid'], [], true, true);
                break;
            default:
                $url = url("user/order", [], true, true);
        }
        return strval($url);
    }
}