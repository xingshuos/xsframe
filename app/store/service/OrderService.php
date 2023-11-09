<?php

namespace app\store\service;

use app\store\enum\OrderSnKeyEnum;
use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\OrderDataServiceFacade;
use app\store\facade\service\OrderGoodsServiceFacade;
use app\store\facade\service\OrderServiceFacade;
use app\store\facade\service\OrderCourseServiceFacade;
use app\store\facade\service\UserAddressServiceFacade;
use app\store\facade\service\UserCartServiceFacade;
use app\store\facade\service\UserCourseServiceFacade;
use xsframe\base\BaseService;
use xsframe\exception\ApiException;
use xsframe\util\PriceUtil;
use xsframe\util\RandomUtil;
use think\facade\Db;

class OrderService extends BaseService
{
    protected $tableName = "shop_order";

    /**
     * @param $userId
     * @param $id
     * @param int $goodsType
     * @param int $total
     * @param int $addressId
     * @param int $cartIds
     * @param string $remark
     * @return string
     * @throws ApiException
     */
    public function create($userId, $id, $goodsType = 1, $total = 1, $addressId = 0, $cartIds = 0, $remark = '')
    {
        $ordersn = "";
        if ($goodsType == 1) {
            try {
                $ordersn = $this->createGoods($userId, $id, $total, $addressId, $cartIds, $remark);
            } catch (ApiException $e) {
                throw new ApiException($e->msg);
            }
        } else {
            if ($goodsType == 2) {
                try {
                    $ordersn = $this->createCourse($userId, $id, $remark);
                } catch (ApiException $e) {
                    throw new ApiException($e->msg);
                }
            }
        }

        return $ordersn;
    }

    /**
     * 获取订单列表
     * @param $condition
     * @return mixed
     */
    public function getOrderList($condition)
    {
        $orderList = OrderServiceFacade::getAll($condition,"*","id desc");

        foreach ($orderList as &$item) {
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            $item['status_val'] = $this->getStatusVal($item['status'], $item['service_type']);

            if ($item['service_type'] == 1) {
                $orderGoodsList = OrderGoodsServiceFacade::getAll(['orderid' => $item['id']]);
                $orderGoodsList = GoodsServiceFacade::listMergeGoodsInfo($orderGoodsList, 'goodsid');
                $orderData      = OrderDataServiceFacade::getDataInfo($item['mid'], $item['id']);

                $orderGoodsTotal = 0;
                foreach ($orderGoodsList as $og) {
                    $orderGoodsTotal = $orderGoodsTotal + $og['total'];
                }
                unset($og);

                $item['orderData']  = $orderData;
                $item['orderGoods'] = $orderGoodsList;
                $item['orderTotal'] = $orderGoodsTotal;
            } else {
                if ($item['service_type'] == 2) {
                    $course = OrderCourseServiceFacade::getInfo(['orderid' => $item['id']], "goodsid,price");
                    $goods  = GoodsServiceFacade::getInfo(['id' => $course['goodsid']], "id,title,thumb");

                    $goods['thumb']      = tomedia($goods['thumb']);
                    $item['orderCourse'] = $course;
                    $item['goods']       = $goods;
                }
            }

        }
        unset($item);
        return $orderList;
    }

    /**
     * 获取订单基本信息
     * @param $userId
     * @param $ordersn
     * @return mixed
     * @throws ApiException
     */
    public function getOrderInfo($userId, $ordersn)
    {
        $orderInfo = OrderServiceFacade::getInfo(['ordersn' => $ordersn, 'mid' => $userId]);

        if (!empty($orderInfo)) {
            $orderInfo['paytime']    = date('Y-m-d H:i:s', $orderInfo['paytime']);
            $orderInfo['createtime'] = date('Y-m-d H:i:s', $orderInfo['createtime']);

            $orderInfo['status_val']  = $this->getStatusVal($orderInfo['status'], $orderInfo['service_type']);
            $orderInfo['paytype_val'] = $this->getPayTypeVal($orderInfo['paytype']);

            /* 业务信息 start */
            switch ($orderInfo['service_type']) {
                case 1:
                    $orderData = OrderDataServiceFacade::getDataInfo($orderInfo['mid'], $orderInfo['id']);
                    // dump($orderInfo);die;
                    $orderGoodsList = OrderGoodsServiceFacade::getAll(['orderid' => $orderInfo['id']]);
                    $orderGoodsList = GoodsServiceFacade::listMergeGoodsInfo($orderGoodsList, 'goodsid');

                    $orderGoodsTotal = 0;
                    foreach ($orderGoodsList as $og) {
                        $orderGoodsTotal = $orderGoodsTotal + $og['total'];
                    }
                    unset($og);

                    // dump($orderGoodsList);die;

                    $orderInfo['orderData']  = $orderData;
                    $orderInfo['orderGoods'] = $orderGoodsList;
                    $orderInfo['orderTotal'] = $orderGoodsTotal;
                    $orderInfo['title']      = count($orderGoodsList) > 1 ? "合并支付订单" : $orderGoodsList[0]['goods']['title'];
                    break;
                case 2:
                    $course = OrderCourseServiceFacade::getInfo(['orderid' => $orderInfo['id']], "goodsid,price");
                    $goods  = GoodsServiceFacade::getInfo(['id' => $course['goodsid']], "id,title,thumb,marketprice");

                    $goods['thumb']      = tomedia($goods['thumb']);
                    $orderInfo['course'] = $course;
                    $orderInfo['goods']  = $goods;
                    $orderInfo['title']  = $goods['title'];
                    break;
            }
            /* 业务信息 end */
        } else {
            throw new ApiException("订单不存在");
        }

        return $orderInfo;
    }

    /**
     * 订单支付成功回调
     * @param $orderId
     * @param $serviceType
     * @return bool
     */
    public function payResult($orderId, $serviceType)
    {
        switch ($serviceType) {
            case 1:
                $this->buyGoods($orderId);
                break;
            case 2:
                $this->buyCourse($orderId);
                break;
        }
        return true;
    }

    /**
     * 创建商品订单
     * @param $userId
     * @param $goodsId
     * @param int $total
     * @param int $addressId
     * @param int $cartIds
     * @param string $remark
     * @return string
     * @throws ApiException
     */
    private function createGoods($userId, $goodsId, $total = 1, $addressId = 0, $cartIds = null, $remark = '')
    {
        $sumPrice = 0.00;

        if (empty($cartIds)) {
            $goodsList = GoodsServiceFacade::getAll(['id' => $goodsId], "id,title,thumb,marketprice,unit,total");
        } else {
            $cartList  = UserCartServiceFacade::getAll(['id' => explode(",", $cartIds), 'mid' => $userId, 'deleted' => 0], "id,goodsid,optionid,total", "createtime desc", "goodsid");
            $goodsList = GoodsServiceFacade::getAll(['id' => array_column($cartList, 'goodsid')], "id,title,thumb,marketprice,unit,total", "createtime desc", "id");
        }

        if (empty($goodsList)) {
            throw new ApiException("下单商品不存在");
        }

        foreach ($goodsList as $goodsInfo) {
            if (!empty($cartIds)) {
                $total = $cartList[$goodsInfo['id']]['total'];
            }
            $sumPrice = $sumPrice + PriceUtil::numberFormat($goodsInfo['marketprice'] * $total);
        }

        if ($sumPrice <= 0) {
            throw new ApiException("支付金额不能为0");
        }

        if (empty($addressId)) {
            throw new ApiException("请填写收货地址");
        }

        // 启动事务
        Db::startTrans();
        try {
            $ordersn               = OrderSnKeyEnum::GOODS_CODE . date("Ymd") . RandomUtil::random(12, true);
            $orderData             = [
                'uniacid'      => $this->uniacid,
                'mid'          => $userId,
                'ordersn'      => $ordersn,
                'price'        => $sumPrice,
                'service_type' => 1,
                'createtime'   => time(),
                'remark'       => $remark,
            ];
            $orderData['oldprice'] = $orderData['price'];

            $orderId = OrderServiceFacade::insertInfo($orderData);

            // 清购物车商品
            if (!empty($cartIds)) {
                UserCartServiceFacade::updateInfo(['deleted' => 1], ['id' => explode(",", $cartIds)]);
            }

            foreach ($goodsList as $goodsInfo) {
                if (!empty($cartIds)) {
                    $total = $cartList[$goodsInfo['id']]['total'];
                }

                $orderGoodsData             = [
                    'uniacid'    => $this->uniacid,
                    'mid'        => $userId,
                    'orderid'    => $orderId,
                    'goodsid'    => $goodsInfo['id'],
                    'optionid'   => 0,
                    'price'      => PriceUtil::numberFormat($goodsInfo['marketprice'] * $total),
                    'total'      => $total,
                    'createtime' => time(),
                ];
                $orderGoodsData['oldprice'] = $orderGoodsData['price'];
                OrderGoodsServiceFacade::insertInfo($orderGoodsData);

                # 锁库存操作 TODO
                if ($goodsInfo['total'] >= 0) {
                    if ($goodsInfo['total'] >= $total) {
                        GoodsServiceFacade::updateInfo(['total' => Db::raw('total - ' . $total)], ['id' => $goodsInfo['id']]);
                    } else {
                        throw new ApiException("“{$goodsInfo['title']}”库存不足");
                    }
                }
            }

            $addressInfo = UserAddressServiceFacade::getInfo(['id' => $addressId]);

            $orderDataData = [
                'mid'        => $userId,
                'orderid'    => $orderId,
                'addressid'  => $addressId,
                'address'    => serialize($addressInfo),
                'createtime' => time(),
            ];
            OrderDataServiceFacade::insertInfo($orderDataData);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

        return $ordersn;
    }

    /**
     * 创建课程订单
     * @param $userId
     * @param $goodsId
     * @param string $remark
     * @return string
     * @throws ApiException
     */
    private function createCourse($userId, $goodsId, $remark = '')
    {
        $goodsInfo = GoodsServiceFacade::getInfo(['id' => $goodsId]);

        if (empty($goodsInfo)) {
            throw new ApiException("该课程已下架！");
        }

        $price = $goodsInfo['marketprice'];
        if ($price <= 0) {
            throw new ApiException("该课程免费无需支付！");
        }

        $memberCourse = UserCourseServiceFacade::getTotal(['mid' => $userId, 'goodsid' => $goodsId, 'is_deleted' => 0]);
        if (!empty($memberCourse)) {
            throw new ApiException("课程已支付！");
        }

        // 启动事务
        Db::startTrans();
        try {
            $ordersn   = OrderSnKeyEnum::COURSE_CODE . date("Ymd") . RandomUtil::random(12, true);
            $orderData = [
                'uniacid'      => $this->uniacid,
                'mid'          => $userId,
                'ordersn'      => $ordersn,
                'price'        => $price,
                'service_type' => 2,
                'createtime'   => time(),
                'remark'       => $remark,
            ];
            $orderId   = OrderServiceFacade::insertInfo($orderData);

            $orderCourseData = [
                'uniacid'          => $this->uniacid,
                'mid'              => $userId,
                'orderid'          => $orderId,
                'goodsid'          => $goodsId,
                'chapters_id'      => 0,
                'chapters_item_id' => 0,
                'price'            => $price,
                'createtime'       => time(),
            ];
            OrderCourseServiceFacade::insertInfo($orderCourseData);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

        return $ordersn;
    }

    /**
     * 获取支付类型
     * @param string $payType
     * @return string
     */
    private function getPayTypeVal($payType = '')
    {
        switch ($payType) {
            case 0:
                $typeVal = '未支付';
                break;
            case 1:
                $typeVal = '微信支付';
                break;
            case 2:
                $typeVal = '支付宝支付';
                break;
            case 3:
                $typeVal = '余额支付';
                break;
            case 4:
                $typeVal = '后台支付';
                break;
            default:
                $typeVal = '未支付';
        }
        return $typeVal;
    }

    /**
     * 获取订单状态
     * @param $status
     * @param int $serviceType
     * @return string
     */
    private function getStatusVal($status, $serviceType = 1)
    {
        // 支付状态 0未支付 1已支付(待发货) 2已发货(待收货) 3已完成 4退款中 5已退款 -1已关闭
        switch ($status) {
            case 0:
                $statusVal = '未支付';
                break;
            case 1:
                $statusVal = $serviceType == 1 ? '待发货' : '已支付';
                break;
            case 2:
                $statusVal = '已发货';
                break;
            case 3:
                $statusVal = '已完成';
                break;
            case 4:
                $statusVal = '退款中';
                break;
            case 5:
                $statusVal = '已退款';
                break;
            case -1:
                $statusVal = '已关闭';
                break;
            default:
                $statusVal = '';
        }
        return $statusVal;
    }

    /**
     * 购买商品成功
     * @param $orderId
     * @return bool
     */
    private function buyGoods($orderId)
    {
        return true;
    }

    /**
     * 购买课程成功
     * @param $orderId
     * @return bool
     */
    private function buyCourse($orderId)
    {
        $orderGoodsInfo = OrderCourseServiceFacade::getInfo(['orderid' => $orderId]);
        $memberCourse   = UserCourseServiceFacade::getInfo(['goodsid' => $orderGoodsInfo['goodsid'], 'mid' => $orderGoodsInfo['mid']]);

        if (empty($memberCourse)) {
            $memberCourseData = [
                'uniacid'          => $this->params['uniacid'],
                'mid'              => $orderGoodsInfo['mid'],
                'orderid'          => $orderId,
                'goodsid'          => $orderGoodsInfo['goodsid'],
                'chapters_id'      => $orderGoodsInfo['chapters_id'],
                'chapters_item_id' => $orderGoodsInfo['chapters_item_id'],
                'createtime'       => time(),
                'starttime'        => time(),
                'endtime'          => 0,
                'depth'            => 1, // 深度 1课程 2章 3节
            ];
            UserCourseServiceFacade::insertInfo($memberCourseData);
        } else {
            $memberGoodsData = [
                'orderid' => $orderId,
            ];
            UserCourseServiceFacade::updateInfo($memberGoodsData, ['id' => $memberCourse['id']]);
        }

        return true;
    }
}