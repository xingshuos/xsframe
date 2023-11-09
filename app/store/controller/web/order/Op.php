<?php

namespace app\store\controller\web\order;

use app\store\facade\service\ExpressServiceFacade;
use app\store\facade\service\MemberServiceFacade;
use app\store\facade\service\OrderDataServiceFacade;
use app\store\facade\service\OrderGoodsServiceFacade;
use app\store\facade\service\OrderRefundServiceFacade;
use app\store\facade\service\OrderServiceFacade;
use xsframe\base\AdminBaseController;
use think\facade\Db;

class Op extends AdminBaseController
{
    public function delete()
    {
        $status  = intval($this->params['status']);
        $orderid = intval($this->params['id']);
        pdo_update('ewei_shop_order', array('deleted' => 1), array('id' => $orderid, 'uniacid' => $this->uniacid));
        plog('order.op.delete', '订单删除 ID: ' . $orderid);
        show_json(1, webUrl('order', array('status' => $status)));
    }

    protected function opData()
    {
        $id   = intval($this->params['id']);
        $item = OrderServiceFacade::getInfo(['id' => $id]);

        if (empty($item)) {
            if ($this->request->isAjax()) {
                show_json(0, '未找到订单!');
            }
            // $this->message('未找到订单!', '', 'error');
        }

        $orderData = OrderDataServiceFacade::getInfo(['orderid' => $id]);
        if (!empty($orderData)) {
            $orderData['address'] = unserialize($orderData['address']);
            $item['data']         = $orderData;
        }

        // 是否在维权
        // $order_goods = pdo_fetchall('select single_refundstate from ' . tablename('ewei_shop_order_goods') . ' where orderid=:orderid and uniacid=:uniacid', array(':uniacid' => $this->$this->uniacid, ':orderid' => $item['id']));
        $isSingleRefund = false;
        // foreach ($order_goods as $og) {
        //     if (!$isSingleRefund && ($og['single_refundstate'] == 1 || $og['single_refundstate'] == 2)) {
        //         $isSingleRefund = true;
        //         break;
        //     }
        // }

        return array('id' => $id, 'item' => $item, 'is_singlerefund' => $isSingleRefund);
    }

    // 订单改价
    public function changePrice()
    {
        $opData = $this->opData();

        $item = $opData['item'];

        $is_peerpay = false;
        if (!empty($is_peerpay)) {
            show_json(0, '代付订单不能改价');
        }

        if (100 <= $item['change_price_sn']) {
            $item['change_price_sn'] = 0;
        }

        if ($this->request->isPost()) {
            $changegoodsprice = $this->params['changegoodsprice'];

            if (!is_array($changegoodsprice)) {
                show_json(0, '未找到改价内容!');
            }

            $changeprice = 0;

            foreach ($changegoodsprice as $ogid => $change) {
                $changeprice += floatval($change);
            }

            $dispatchprice = floatval($this->params['changedispatchprice']);

            if ($dispatchprice < 0) {
                $dispatchprice = 0;
            }

            $orderprice          = $item['price'] + $changeprice;
            $changedispatchprice = 0;

            if ($dispatchprice != $item['dispatchprice']) {
                $changedispatchprice = $dispatchprice - $item['dispatchprice'];
                $orderprice          += $changedispatchprice;
            }

            if ($orderprice < 0) {
                show_json(0, '订单实际支付价格不能小于0元!');
            }

            foreach ($changegoodsprice as $ogid => $change) {
                $og = OrderGoodsServiceFacade::getInfo(['id' => $ogid], 'id,price');
                if (!empty($og)) {
                    $realprice = $og['price'] + $change;
                    if ($realprice < 0) {
                        show_json(0, '单个商品不能优惠到负数');
                    }
                }
            }

            $ordersn2 = $item['change_price_sn'] + 1;

            if (99 < $ordersn2) {
                show_json(0, '超过改价次数限额');
            }

            $orderupdate = array();

            if ($orderprice != $item['price']) {
                $orderupdate['price']           = $orderprice;
                $orderupdate['change_price_sn'] = $item['change_price_sn'] + 1;
            }

            $orderupdate['changeprice'] = $item['changeprice'] + $changeprice;

            if ($dispatchprice != $item['dispatchprice']) {
                $orderupdate['dispatchprice']       = $dispatchprice;
                $orderupdate['changedispatchprice'] = $item['changedispatchprice'] ?? 0;
                $orderupdate['changedispatchprice'] += $changedispatchprice;
            }

            if (!empty($orderupdate)) {
                OrderServiceFacade::updateInfo($orderupdate, ['id' => $item['id']]);
            }

            foreach ($changegoodsprice as $ogid => $change) {
                $og = OrderGoodsServiceFacade::getInfo(['id' => $ogid], 'id,price,changeprice');
                if (!empty($og)) {
                    $realprice   = $og['price'] + $change;
                    $changeprice = $og['changeprice'] + $change;
                    OrderGoodsServiceFacade::updateInfo(array('price' => $realprice, 'changeprice' => $changeprice), array('id' => $ogid));
                }
            }

            // m('notice')->sendOrderChangeMessage($item['openid'], array('title' => '订单金额', 'orderid' => $item['id'], 'ordersn' => $item['ordersn'], 'olddata' => $item['price'], 'data' => round($orderprice, 2), 'type' => 1), 'orderstatus');
            show_json(1);
        }

        if ($item['service_type'] == 1) {
            $order_goods = Db::query('select og.id,g.title,g.thumb,g.goodssn, og.total,og.price,og.oldprice from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));
            $orderData   = OrderDataServiceFacade::getInfo(['orderid' => $item['id']]);

            if (empty($orderData['addressid'])) {
                $user                = unserialize($item['carrier']);
                $item['addressdata'] = array('realname' => $user['carrier_realname'], 'mobile' => $user['carrier_mobile']);
            } else {
                $user = iunserializer($orderData['address']);

                $user['address']     = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['address'];
                $item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
            }

        } else {
            if ($item['service_type'] == 2) {
                $order_goods         = Db::query('select og.id,g.title,g.thumb,og.price,og.oldprice from ' . tablename('store_order_course') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));
                $user                = MemberServiceFacade::getInfo(['id' => $item['mid']]);
                $item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => '');
            }
        }

        $opData['item']        = $item;
        $opData['order_goods'] = $order_goods;
        return $this->template('changeprice', $opData);
    }

    // 确认付款
    public function pay($a = array(), $b = array())
    {
        $opData = $this->opData();

        $item = $opData['item'];

        if (1 < $item['status']) {
            show_json(0, '订单已付款，不需重复付款！');
        }

        $updateOrder = array('status' => 1, 'paytype' => 4, 'paytime' => time());
        OrderServiceFacade::updateInfo($updateOrder, array('id' => $item['id'], 'uniacid' => $this->uniacid));

        OrderServiceFacade::payResult($item['id'], $item['service_type']);

        // m('notice')->sendOrderMessage($item['id']);
        // com_run('printer::sendOrderMessage', $item['id']);
        // plog('web.order.op/pay', '订单确认付款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);

        show_json(1);
    }

    public function close()
    {
        $opData = $this->opData();
        extract($opData);

        $item = $opData['item'];
        if ($opData['is_singlerefund']) {
            show_json(0, '订单商品存在维权，无法关闭订单！');
        }

        if ($item['status'] == -1) {
            show_json(0, '订单已关闭，无需重复关闭！');
        } else {
            if (1 <= $item['status']) {
                show_json(0, '订单已付款，不能关闭！');
            }
        }

        if ($this->request->isPost()) {
            $time = time();

            // if (!empty($item['refundid'])) {
            //     $change_refund               = array();
            //     $change_refund['status']     = -1;
            //     $change_refund['refundtime'] = $time;
            //     pdo_update('ewei_shop_order_refund', $change_refund, array('id' => $item['refundid'], 'uniacid' => $this->uniacid));
            // }

            OrderServiceFacade::updateInfo(array('status' => -1, 'canceltime' => $time, 'cancel_remark' => $this->params['remark']), array('id' => $item['id'], 'uniacid' => $this->uniacid));
            show_json(1);
        }

        return $this->template('close', $opData);
    }

    public function paycancel()
    {
        $opdata = $this->opData();
        extract($opdata);

        if ($is_singlerefund) {
            show_json(0, '订单商品存在维权，无法取消付款！');
        }

        if ($item['status'] != 1) {
            show_json(0, '订单未付款，不需取消！');
        }

        if ($this->request->isPost()) {
            m('order')->setStocksAndCredits($item['id'], 2);
            pdo_update('ewei_shop_order', array('status' => 0, 'cancelpaytime' => time()), array('id' => $item['id'], 'uniacid' => $this->uniacid));
            plog('order.op.paycancel', '订单取消付款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
            show_json(1);
        }
    }

    public function fetch()
    {
        $opdata = $this->opData();
        extract($opdata);

        if ($is_singlerefund) {
            show_json(0, '订单商品存在维权，无法确认取货！');
        }

        if ($item['status'] != 1) {
            show_json(0, '订单未付款，无法确认取货！');
        }

        $time = time();
        $d    = array('status' => 3, 'sendtime' => $time, 'finishtime' => $time);

        if ($item['isverify'] == 1) {
            $d['verified']     = 1;
            $d['verifytime']   = $time;
            $d['verifyopenid'] = '';
        }

        pdo_update('ewei_shop_order', $d, array('id' => $item['id'], 'uniacid' => $this->uniacid));
        m('order')->fullback($item['id']);

        if (com('coupon')) {
            com('coupon')->sendcouponsbytask($item['id']);
        }

        if (!empty($item['couponid'])) {
            com('coupon')->backConsumeCoupon($item['id']);
        }

        if (!empty($item['refundid'])) {
            $refund = pdo_fetch('select * from ' . tablename('ewei_shop_order_refund') . ' where id=:id limit 1', array(':id' => $item['refundid']));

            if (!empty($refund)) {
                pdo_update('ewei_shop_order_refund', array('status' => -1), array('id' => $item['refundid']));
                pdo_update('ewei_shop_order', array('refundstate' => 0), array('id' => $item['id']));
            }
        }

        $log = '订单确认取货';
        if (p('ccard') && !empty($item['ccardid'])) {
            p('ccard')->setBegin($item['id'], $item['ccardid']);
            $log = '订单确认充值';
        }

        $log .= ' ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'];
        m('order')->setGiveBalance($item['id'], 1);
        m('order')->setStocksAndCredits($item['id'], 3);
        m('member')->upgradeLevel($item['openid'], $item['id']);
        m('notice')->sendOrderMessage($item['id']);

        if (p('commission')) {
            p('commission')->checkOrderFinish($item['id']);
        }

        $goodscircle = p('goodscircle');

        if ($goodscircle) {
            $goodscircle->updateOrder($item['openid'], $item['id']);
        }

        plog('order.op.fetch', $log);
        show_json(1);
    }

    public function fetchcancel()
    {
        $opdata = $this->opData();
        extract($opdata);

        if ($is_singlerefund) {
            show_json(0, '订单商品存在维权，无法取消取货！');
        }

        if ($item['status'] != 3) {
            show_json(0, '订单未取货，不需取消！');
        }

        pdo_update('ewei_shop_order', array('status' => 1, 'finishtime' => 0), array('id' => $item['id'], 'uniacid' => $this->uniacid));
        plog('order.op.fetchcancel', '订单取消取货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
        show_json(1);
    }

    // 完成订单 TODO 减库存
    public function finish()
    {
        $opData = $this->opData();

        $item = $opData['item'];

        $is_singlerefund = $opData['is_singlerefund'];
        if ($is_singlerefund) {
            show_json(0, '订单商品存在维权，无法发货！');
        }

        OrderServiceFacade::updateInfo(array('finishtime' => time(), 'status' => 3), array('id' => $item['id'], 'uniacid' => $this->uniacid));

        // m('member')->upgradeLevel($item['openid'], $item['id']);
        // m('order')->setStocksAndCredits($item['id'], 3, true);
        // m('notice')->sendOrderMessage($item['id']);

        // plog('order.op.finish', '订单完成 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
        show_json(1);
    }

    // 确认发货
    public function send()
    {
        $opData = $this->opData();

        $item = $opData['item'];

        $is_singlerefund = $opData['is_singlerefund'];
        if ($is_singlerefund) {
            show_json(0, '订单商品存在维权，无法发货！');
        }

        if (empty($item['data']['addressid'])) {
            show_json(0, '无收货地址，无法发货！');
        }

        if ($item['status'] != 1) {
            show_json(0, '订单未付款，无法发货！');
        }

        if ($this->request->isPost()) {
            if ($item['data']['city_express_state'] == 0) { // 快递配送
                if (!empty($this->params['isexpress']) && empty($this->params['expresssn'])) {
                    show_json(0, '请输入快递单号！');
                }

                $time = time();
                $data = array(
                    'sendtype'   => 0 < $item['data']['sendtype'] ? $item['data']['sendtype'] : intval($this->params['sendtype']),
                    'express'    => trim($this->params['express']),
                    'expresscom' => trim($this->params['expresscom']),
                    'expresssn'  => trim($this->params['expresssn']),
                    'sendtime'   => $time
                );

                if (0 < $item['data']['sendtype'] || intval($this->params['sendtype']) == 1) {
                    if (empty($this->params['ordergoodsid'])) {
                        show_json(0, '请选择发货商品！');
                    }

                    $goodsid = $this->params['ordergoodsid'];

                    $ogoods           = Db::query('select sendtype from ' . tablename('store_order_goods') . 'where orderid = ' . $item['id'] . ' and uniacid = ' . $this->uniacid . ' order by sendtype desc ');
                    $data['sendtype'] = $ogoods[0]['sendtype'] + 1;
                    foreach ($goodsid as $key => $value) {
                        OrderGoodsServiceFacade::updateInfo($data, array('id' => $value, 'uniacid' => $this->uniacid));
                    }

                    # 分包全部发完显示已发货
                    $send_goods = OrderGoodsServiceFacade::getAll(['orderid' => $item['id'], 'sendtype' => 0]);
                    if (empty($send_goods)) {
                        $sendData['status']   = 2;
                        $sendData['sendtime'] = $time;
                    }

                    # 记录订单为分包发货
                    OrderDataServiceFacade::updateInfo(['sendtype' => $ogoods[0]['sendtype'] + 1, 'sendtime' => $time], array('orderid' => $item['id'], 'uniacid' => $this->uniacid));
                } else {
                    $sendData['status']   = 2;
                    $sendData['sendtime'] = $time;
                    OrderDataServiceFacade::updateInfo($data, array('orderid' => $item['id'], 'uniacid' => $this->uniacid));
                }

                $sendData['refundid'] = 0;
                OrderServiceFacade::updateInfo($sendData, array('id' => $item['id'], 'uniacid' => $this->uniacid));
            } else {
                // 同城配送
                $data['status']   = 2;
                $data['refundid'] = 0;
                OrderServiceFacade::updateInfo($data, array('id' => $item['id'], 'uniacid' => $this->uniacid));
            }

            if (!empty($item['refundid'])) {
                $refund = OrderRefundServiceFacade::getInfo(['id' => $item['refundid']]);
                if (!empty($refund)) {
                    OrderRefundServiceFacade::updateInfo(array('status' => -1, 'endtime' => $time), array('id' => $item['refundid']));
                    OrderServiceFacade::updateInfo(array('refundstate' => 0), array('id' => $item['id']));
                }
            }

            // m('notice')->sendOrderMessage($item['id']);
            // plog('order.op.send', '订单发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br/>快递公司: ' . $this->params['expresscom'] . ' 快递单号: ' . $this->params['expresssn']);

            show_json(1);
        }

        $noshipped = array();
        $shipped   = array();

        if (0 < $item['data']['sendtype']) {
            $noshipped = Db::query('select og.id,g.title,g.thumb,og.sendtype,g.ispresell from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.sendtype = 0 and og.orderid=:orderid ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));
            $i         = 1;

            while ($i <= $item['data']['sendtype']) {
                $shipped[$i]['sendtype'] = $i;
                $shipped[$i]['goods']    = Db::query('select g.id,g.title,g.thumb,og.sendtype,g.ispresell from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.sendtype = ' . $i . ' and og.orderid=:orderid ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));

                if (empty($shipped[$i]['goods'])) {
                    unset($shipped[$i]);
                }

                ++$i;
            }
        }

        $order_goods = Db::query('select og.id,g.title,g.thumb,g.goodssn,g.ispresell from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));

        $address = iunserializer($item['data']['address']);

        $express_list = ExpressServiceFacade::getAll(['status' => 1]);

        $result = [
            'id'           => $item['id'],
            'item'         => $item,
            'order_goods'  => $order_goods,
            'express_list' => $express_list,
            'address'      => $address,
            'shipped'      => $shipped,
            'noshipped'    => $noshipped,
        ];
        return $this->template('send', $result);
    }

    // 取消发货
    public function sendcancel()
    {
        $opData = $this->opData();

        $item = $opData['item'];

        $is_singlerefund = $opData['is_singlerefund'];
        if ($is_singlerefund) {
            show_json(0, '订单商品存在维权，无法取消发货！');
        }

        $sendtype = intval($this->params['sendtype'] ?? 0);

        if ($item['status'] != 2 && $item['data']['sendtype'] == 0) {
            show_json(0, '订单未发货，不需取消发货！');
        }

        if ($this->request->isPost()) {
            $remark = trim($this->params['remark']);

            if (!empty($item['remarksend'])) {
                $remark = $item['remarksend'] . '' . $remark;
            }

            if (0 < $item['data']['sendtype']) {
                if (empty($sendtype)) {
                    if (empty($this->params['bundle'])) {
                        show_json(0, '请选择您要修改的包裹！');
                    }

                    $sendtype = intval($this->params['bundle']);
                }

                OrderGoodsServiceFacade::updateInfo(['sendtype' => 0, 'sendtime' => 0, 'remarksend' => $remark], array('orderid' => $item['id'], 'sendtype' => $sendtype, 'uniacid' => $this->uniacid));
                OrderDataServiceFacade::updateInfo(['sendtype' => $item['data']['sendtype'] - 1, 'sendtime' => 0], ['id' => $item['data']['id'], 'uniacid' => $this->uniacid]);
            } else {
                OrderDataServiceFacade::updateInfo(['remarksend' => $remark, 'sendtime' => 0], ['id' => $item['data']['id'], 'uniacid' => $this->uniacid]);
            }

            OrderServiceFacade::updateInfo(['status' => 1, 'sendtime' => 0], ['id' => $item['id']]);
            // plog('order.op.sendcancel', '订单取消发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 原因: ' . $remark);

            show_json(1);
        }

        $sendgoods = array();
        $bundles   = array();

        if (0 < $sendtype) {
            $sendgoods = Db::query('select g.id,g.title,g.thumb,og.sendtype,g.ispresell from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid and og.sendtype=' . $sendtype . ' ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));
        } else {
            if (0 < $item['data']['sendtype']) {
                $i = 1;

                while ($i <= intval($item['data']['sendtype'])) {
                    $bundles[$i]['goods']    = Db::query('select g.id,g.title,g.thumb,og.sendtype,g.ispresell from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid and og.sendtype=' . $i . ' ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));
                    $bundles[$i]['sendtype'] = $i;

                    if (empty($bundles[$i]['goods'])) {
                        unset($bundles[$i]);
                    }

                    ++$i;
                }
            }
        }

        $result = [
            'id'        => $opData['id'],
            'item'      => $item,
            'sendgoods' => $sendgoods,
            'bundles'   => $bundles,
        ];
        return $this->template('sendcancel', $result);
    }

    // 订单备注
    public function remarksaler()
    {
        $opData = $this->opData();

        if ($this->request->isPost()) {
            OrderServiceFacade::updateInfo(array('remark' => $this->params['remark']), ['id' => $opData['id']]);
            show_json(1);
        }

        return $this->template('remarksaler', $opData);
    }

    // 修改物流
    public function changeexpress()
    {
        $opData = $this->opData();
        extract($opData);

        $item = $opData['item'];

        $sendtype  = intval($this->params['sendtype'] ?? 0);
        $edit_flag = 1;

        if ($this->request->isPost()) {
            $express    = $this->params['express'];
            $expresscom = $this->params['expresscom'];
            $expresssn  = trim($this->params['expresssn']);

            if (empty($id)) {
                $ret = '参数错误！';
                show_json(0, $ret);
            }

            if (!empty($expresssn)) {
                $change_data               = array();
                $change_data['express']    = $express;
                $change_data['expresscom'] = $expresscom;
                $change_data['expresssn']  = $expresssn;

                if (0 < $item['data']['sendtype']) {
                    if (empty($sendtype)) {
                        if (empty($this->params['bundle'])) {
                            show_json(0, '请选择您要修改的包裹！');
                        }

                        $sendtype = intval($this->params['bundle']);
                    }
                    OrderGoodsServiceFacade::updateInfo($change_data, array('orderid' => $id, 'sendtype' => $sendtype, 'uniacid' => $this->uniacid));
                } else {
                    OrderDataServiceFacade::updateInfo($change_data, array('orderid' => $id, 'uniacid' => $this->uniacid));
                }

                // plog('order.op.changeexpress', '修改快递状态 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 快递公司: ' . $expresscom . ' 快递单号: ' . $expresssn);
                show_json(1);
            } else {
                show_json(0, '请填写快递单号！');
            }
        }

        $sendgoods = array();
        $bundles   = array();

        if (0 < $sendtype) {
            $sendgoods = Db::query('select g.id,g.title,g.thumb,og.sendtype,g.ispresell from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid and og.sendtype=' . $sendtype . ' ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));
        } else {
            if (0 < $item['data']['sendtype']) {
                $i = 1;

                while ($i <= intval($item['data']['sendtype'])) {
                    $bundles[$i]['goods']    = Db::query('select g.id,g.title,g.thumb,og.sendtype,g.ispresell from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid and og.sendtype=' . $i . ' ', array('uniacid' => $this->uniacid, 'orderid' => $item['id']));
                    $bundles[$i]['sendtype'] = $i;

                    if (empty($bundles[$i]['goods'])) {
                        unset($bundles[$i]);
                    }
                    ++$i;
                }
            }
        }

        $address = $item['data']['address'];

        $express_list = ExpressServiceFacade::getAll(['status' => 1]);

        $result = [
            'id'           => $item['id'],
            'item'         => $item,
            'express_list' => $express_list,
            'address'      => $address,
            'edit_flag'    => $edit_flag,
            'sendgoods'    => $sendgoods,
            'bundles'      => $bundles,
        ];
        return $this->template('send', $result);
    }

    public function changeaddress()
    {
        $opData = $this->opData();

        $id   = $opData['id'];
        $item = $opData['item'];

        $address_info = "";
        if (empty($item['data']['addressid'])) {
            $user = unserialize($item['data']['carrier']);
        } else {
            $user = iunserializer($item['data']['address']);

            $address_info = $user['address'];

            $user['address']     = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['street'] . ' ' . $user['address'];
            $item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
        }

        if ($this->request->isPost()) {

            $realname = $this->params['realname'];
            $mobile   = $this->params['mobile'];
            $province = $this->params['province'];
            $city     = $this->params['city'];
            $area     = $this->params['area'];
            $street   = $this->params['street'] ?? '';
            $changead = intval($this->params['changead']);
            $address  = trim($this->params['address']);

            if (!empty($id)) {

                if (empty($realname)) {
                    $ret = '请填写收件人姓名！';
                    show_json(0, $ret);
                }

                if (empty($mobile)) {
                    $ret = '请填写收件人手机！';
                    show_json(0, $ret);
                }

                if ($changead) {
                    if ($province == '请选择省份') {
                        $ret = '请选择省份！';
                        show_json(0, $ret);
                    }

                    if (empty($address)) {
                        $ret = '请填写详细地址！';
                        show_json(0, $ret);
                    }
                }

                $address_array             = iunserializer($item['data']['address']);
                $address_array['realname'] = $realname;
                $address_array['mobile']   = $mobile;

                if ($changead) {
                    $address_array['province'] = $province;
                    $address_array['city']     = $city;
                    $address_array['area']     = $area;
                    $address_array['street']   = $street;
                    $address_array['address']  = $address;
                } else {
                    $address_array['province'] = $user['province'];
                    $address_array['city']     = $user['city'];
                    $address_array['area']     = $user['area'];
                    $address_array['street']   = $user['street'];
                    $address_array['address']  = $address_info;
                }

                $address_array = iserializer($address_array);
                OrderDataServiceFacade::updateInfo(array('address' => $address_array), array('orderid' => $id, 'uniacid' => $this->uniacid));

                // plog('order.op.changeaddress', '修改收货地址 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br>原地址: 收件人: ' . $oldaddress['realname'] . ' 手机号: ' . $oldaddress['mobile'] . ' 收件地址: ' . $oldaddress['address'] . '<br>新地址: 收件人: ' . $realname . ' 手机号: ' . $mobile . ' 收件地址: ' . $province . ' ' . $city . ' ' . $area . ' ' . $address);
                // m('notice')->sendOrderChangeMessage($item['openid'], array('title' => '订单收货地址', 'orderid' => $item['id'], 'ordersn' => $item['ordersn'], 'olddata' => $oldaddress['address'], 'data' => $province . $city . $area . $address, 'type' => 0), 'orderstatus');

                show_json(1);
            }
        }

        $result = [
            'id'           => $id,
            'item'         => $item,
            'user'         => $user,
            'address_info' => $address_info,
        ];
        return $this->template('changeaddress', $result);
    }

    public function upload_invoice()
    {
        $order_id = $this->params['order_id'];

        if (!$this->request->isPost()) {
            $invoice      = pdo_fetch('select invoicename,invoice_img from ' . tablename('ewei_shop_order') . ' where id = :order_id and uniacid = :uniacid limit 1', array(':order_id' => $order_id, ':uniacid' => $this->uniacid));
            $invoice_info = m('sale')->parseInvoiceInfo($invoice['invoicename']);

            if ($invoice_info['title']) {
                if ($invoice_info['entity']) {
                    show_json(0, '本单不支持电子发票');
                }

                include $this->template();
                return NULL;
            }

            show_json(0, '本单不支持电子发票');
        }

        $invoice_img = $this->params['invoice_img'];

        if (empty($invoice_img)) {
            show_json(0, '请选择图片');
        }

        $invoice_img = save_media($invoice_img);
        $update_ret  = pdo_update('ewei_shop_order', array('invoice_img' => $invoice_img), array('id' => $order_id));
        $update_ret ? show_json(1) : show_json(0, '电子发票上传失败，请重试');
    }

    public function peerpay()
    {
        $order_id = $this->params['id'];

        if (empty($order_id)) {
            show_json(0, '参数错误');
        }

        $peerpay = m('order')->checkpeerpay($order_id);

        if (!$peerpay) {
            show_json(0, '非代付订单');
        }

        $sql  = 'SELECT * FROM ' . tablename('ewei_shop_order_peerpay_payinfo') . ' where pid=:pid';
        $list = pdo_fetchall($sql, array(':pid' => $peerpay['id']));
        include $this->template();
    }
}
