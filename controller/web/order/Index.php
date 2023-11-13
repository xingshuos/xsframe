<?php

namespace app\xs_cloud\controller\web\order;

use app\store\facade\service\CouponServiceFacade;
use app\store\facade\service\DispatchServiceFacade;
use app\store\facade\service\MemberServiceFacade;
use app\store\facade\service\OrderDataServiceFacade;
use app\store\facade\service\OrderServiceFacade;
use xsframe\base\AdminBaseController;
use xsframe\util\PriceUtil;
use think\facade\Db;

class Index extends AdminBaseController
{
    public function index()
    {
        $result = [];
        return $this->template('index', $result);
    }

    public function main($status = null)
    {
        $keyword = $this->params['keyword'];

        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime("-1 month");
            $endtime   = time();
        }

        $orderBy    = "createtime";
        $searchTime = trim($this->params["searchtime"]);
        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, array("create", "pay", "send", "finish"))) {
            $starttime     = strtotime($this->params["time"]["start"]);
            $endtime       = strtotime($this->params["time"]["end"]);
            $condition[''] = Db::raw($searchTime . "time >= {$starttime} AND " . $searchTime . "time <= {$endtime} ");
            $orderBy       = $searchTime . "time";
        }

        if (is_numeric($status)) {
            switch ($status) {
                case 0:
                    $condition['status']       = 0;
                    $condition['service_type'] = 1;
                    break;
                case 1:
                    $condition['status']       = 1;
                    $condition['service_type'] = 1;
                    break;
                case 2:
                    $condition['status']       = 2;
                    $condition['service_type'] = 1;
                    break;
                case 3:
                    $condition['status']       = 3;
                    $condition['service_type'] = 1;
                    break;
                case 4:
                    $condition['status']       = 4;
                    $condition['service_type'] = 1;
                    break;
                case 5:
                    $condition['status']       = 5;
                    $condition['service_type'] = 1;
                    break;
                case 10:
                    $condition['status']       = 0;
                    $condition['service_type'] = 2;
                    break;
                case 11:
                    $condition['status']       = 1;
                    $condition['service_type'] = 2;
                    break;
                case 12:
                    $condition['service_type'] = 2;
                    break;
                case 13:
                    $condition['status']       = -1;
                    $condition['service_type'] = 2;
                    break;
                case -1:
                    $condition['status']       = -1;
                    $condition['service_type'] = 1;
                    break;
            }
        } else {
            $condition['service_type'] = 1;
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" ordersn like '%" . trim($keyword) . "%' or transaction_id like '%" . trim($keyword) . "%' ");
        }

        $list  = OrderServiceFacade::getList($condition, "*", "{$orderBy} desc");
        $total = OrderServiceFacade::getTotal($condition);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $goodsStatus  = array(
            -1 => array("css" => "default", "name" => "已关闭"),
            0  => array("css" => "danger", "name" => "待付款"),
            1  => array("css" => "info", "name" => "待发货"),
            2  => array("css" => "warning", "name" => "待收货"),
            3  => array("css" => "success", "name" => "已完成")
        );
        $courseStatus = array(
            -1 => array("css" => "default", "name" => "已关闭"),
            0  => array("css" => "danger", "name" => "待付款"),
            1  => array("css" => "success", "name" => "已付款"),
        );
        $paytype      = array(
            0 => array("css" => "default", "name" => "未支付"),
            1 => array("css" => "success", "name" => "微信支付"),
            2 => array("css" => "warning", "name" => "支付宝支付"),
            3 => array("css" => "danger", "name" => "余额支付"),
            4 => array("css" => "default", "name" => "后台付款"),
        );

        $totalMoney = 0.00;
        foreach ($list as &$value) {
            $value["statusvalue"]  = $value["status"];
            $value['paytypevalue'] = $value['paytype'];

            if ($value['service_type'] == 1) {
                $value["statusname"] = $goodsStatus[$value["status"]]["name"];
                $value["statuscss"]  = $goodsStatus[$value["status"]]["css"];

                $orderGoods     = Db::query("select g.id,g.title,g.thumb, og.total,og.price,og.changeprice,og.oldprice from " . tablename("store_order_goods") . " og " . " left join " . tablename("store_goods") . " g on g.id=og.goodsid " . " where og.orderid=:orderid ", ['orderid' => $value['id']]);
                $value["goods"] = set_medias($orderGoods, "thumb");

                $orderData = OrderDataServiceFacade::getInfo(['orderid' => $value['id']]);
                if (!empty($orderData)) {
                    $orderData['address'] = unserialize($orderData['address']);
                    $value['data']        = $orderData;
                    $value['address']     = $orderData['address'];
                }
            } else {
                if ($value['service_type'] == 2) {
                    $value["statusname"] = $courseStatus[$value["status"]]["name"];
                    $value["statuscss"]  = $courseStatus[$value["status"]]["css"];

                    $orderCourse    = Db::query("select g.id,g.title,g.thumb,og.price,og.changeprice,og.oldprice from " . tablename("store_order_course") . " og " . " left join " . tablename("store_goods") . " g on g.id=og.goodsid " . " where og.orderid=:orderid ", ['orderid' => $value['id']]);
                    $value["goods"] = set_medias($orderCourse, "thumb");
                }
            }

            $memberInfo      = MemberServiceFacade::getInfo(['id' => $value['mid']], "id,username,nickname,realname,mobile");
            $value["member"] = $memberInfo;

            $totalMoney = $totalMoney + $value['price'];
        }
        // dump($list[0]['goods']);
        // die;

        $result = [
            'list'       => $list,
            'paytype'    => $paytype,
            'pager'      => $pager,
            'total'      => $total,
            'totalMoney' => PriceUtil::numberFormat($totalMoney),
            'status'     => $status,
            'starttime'  => $starttime,
            'endtime'    => $endtime,
        ];

        // dump($result);die;
        return $this->template('list', $result);
    }

    public function post()
    {
        $id = $this->params['id'];

        if ($this->request->isPost()) {
            $data = array(
                "uniacid"      => $this->uniacid,
                'cates'        => intval($this->params["pcateid"]), // 全部
                'pcateid'      => intval($this->params["pcateid"]), // 主
                'cateid'       => 0, // 次
                "title"        => trim($this->params["title"]),
                'sub_title'    => trim($this->params['sub_title']),
                'keywords'     => trim($this->params['keywords']),
                'description'  => trim($this->params['description']),
                "author"       => trim($this->params["author"]),
                "editor"       => trim($this->params["editor"]),
                "thumb"        => trim($this->params["thumb"]),
                "article_mp"   => trim($this->params["article_mp"]),
                "isrecommend"  => trim($this->params["isrecommend"]),
                "enabled"      => intval($this->params["enabled"]),
                "content"      => htmlspecialchars_decode($this->params["content"]),
                "displayorder" => intval($this->params["displayorder"]),
                "showtime"     => strtotime($this->params["showtime"]),
                'updatetime'   => time(),
                'viewcount_v'  => trim($this->params['viewcount_v']),
                'likenum_v'    => trim($this->params['likenum_v']),
            );
            if (!empty($id)) {
                Db::name('store_order')->where(["id" => $id])->update($data);
            } else {
                $data['createtime'] = time();
                Db::name('store_order')->insert($data);
            }

            $this->success(array("url" => webUrl("web.article.index/list")));
        }

        $item      = Db::name('store_order')->where(['id' => $id])->find();
        $categorys = Db::name('store_order_category')->where(['uniacid' => $this->uniacid, 'deleted' => 0])->select();
        return $this->template('post', ['item' => $item, 'categorys' => $categorys]);
    }

    public function status0()
    {
        return $this->main(0);
    }

    public function status1()
    {
        return $this->main(1);
    }

    public function status2()
    {
        return $this->main(2);
    }

    public function status3()
    {
        return $this->main(3);
    }

    public function status4()
    {
        return $this->main(4);
    }

    public function status5()
    {
        return $this->main(5);
    }

    public function status_1()
    {
        return $this->main(-1);
    }

    public function course0()
    {
        return $this->main(10);
    }

    public function course1()
    {
        return $this->main(11);
    }

    public function course2()
    {
        return $this->main(12);
    }

    public function course_1()
    {
        return $this->main(13);
    }

    public function ajaxOrder()
    {
        $day   = (int)$this->params['day'];
        $order = $this->selectOrderPrice($day);
        unset($order['list']);
        $allOrder = $this->selectOrderPrice($day, true);
        unset($allOrder['list']);
        $avg = $this->selectOrderPrice($day, true, true);
        unset($allOrder['list']);
        $orders = array('order_count' => $order['count'], 'order_price' => number_format($order['price'], 2), 'allorder_count' => $allOrder['count'], 'allorder_price' => number_format($allOrder['price'], 2), 'avg' => number_format($avg['avg'], 2));
        show_json(1, array('order' => $orders));
    }

    /**
     * ajax return 七日交易记录.近7日交易时间,交易金额,交易数量
     */
    public function ajaxTransaction()
    {
        $orderPrice  = $this->selectOrderPrice(7);
        $transaction = $this->selectTransaction($orderPrice['list'], 7);

        if (empty($transaction)) {
            $i = 7;

            while (1 <= $i) {
                $transaction['price'][date('Y-m-d', time() - $i * 3600 * 24)] = 0;
                $transaction['count'][date('Y-m-d', time() - $i * 3600 * 24)] = 0;
                --$i;
            }
        } else {
            foreach ($transaction['price'] as &$item) {
                $item = round($item, 2);
            }

            unset($item);
        }

        $allOrderPrice  = $this->selectOrderPrice(7, true);
        $allTransaction = $this->selectTransaction($allOrderPrice['list'], 7, true);

        if (empty($allTransaction)) {
            $i = 7;

            while (1 <= $i) {
                $allTransaction['price'][date('Y-m-d', time() - $i * 3600 * 24)] = 0;
                $allTransaction['count'][date('Y-m-d', time() - $i * 3600 * 24)] = 0;
                --$i;
            }
        } else {
            foreach ($allTransaction['price'] as &$item) {
                $item = round($item, 2);
            }

            unset($item);
        }

        echo json_encode(array('price_key' => array_keys($transaction['price']), 'price_value' => array_values($transaction['price']), 'count_value' => array_values($transaction['count']), 'allprice_value' => array_values($allTransaction['price']), 'allcount_value' => array_values($allTransaction['count'])));
    }

    /**
     * 查询订单金额
     * @param int $day 查询天数
     * @param bool $is_all 是否是全部订单
     * @param bool $is_avg 是否是查询付款平均数
     * @return array
     */
    private function selectOrderPrice($day = 0, $is_all = false, $is_avg = false)
    {
        $day = (int)$day;

        if ($day != 0) {
            if ($day == 30) {
                $yest        = date('Y-m-d');
                $createtime1 = strtotime(date('Y-m-d', strtotime('-30 day')));
                $createtime2 = strtotime($yest . ' 23:59:59');
            } else if ($day == 7) {
                $yest        = date('Y-m-d');
                $createtime1 = strtotime(date('Y-m-d', strtotime('-7 day')));
                $createtime2 = strtotime($yest . ' 23:59:59');
            } else {
                $yesterday   = strtotime('-1 day');
                $yy          = date('Y', $yesterday);
                $ym          = date('m', $yesterday);
                $yd          = date('d', $yesterday);
                $createtime1 = strtotime($yy . '-' . $ym . '-' . $yd . ' 00:00:00');
                $createtime2 = strtotime($yy . '-' . $ym . '-' . $yd . ' 23:59:59');
            }
        } else {
            $createtime1 = strtotime(date('Y-m-d', time()));
            $createtime2 = strtotime(date('Y-m-d', time())) + 3600 * 24 - 1;
        }

        $param = array(
            'uniacid' => $this->uniacid,
            'deleted' => 0,
            'paytype' => 3,
            'status'  => Db::raw("> 0"),
        );
        $where = Db::raw(" (( status > 0 and (paytime between {$createtime1} and {$createtime2})) or ((createtime between {$createtime1} and {$createtime2} ) and status>=0 and paytype=3)) ");

        $time = 'paytime';
        if (!empty($is_all)) {
            $time  = 'createtime';
            $where = Db::raw(" createtime between {$createtime1} and {$createtime2}");
        }

        if (!empty($is_avg)) {
            $time  = 'paytime';
            $where = Db::raw(" (status >0 and (paytime between {$createtime1} and {$createtime2}))");
        }

        $param[''] = $where;
        $pdo_res   = OrderServiceFacade::getList($param, 'id,price,mid,' . $time);

        $price  = 0;
        $avg    = 0;
        $member = array();

        foreach ($pdo_res as $arr) {
            $price    += $arr['price'];
            $member[] = $arr['mid'];
        }

        if (!empty($is_avg)) {
            $member_num = count(array_unique($member));
            $avg        = empty($member_num) ? 0 : round($price / $member_num, 2);
        }

        $result = array('price' => $price, 'count' => count($pdo_res), 'avg' => $avg, 'list' => $pdo_res);
        return $result;
    }

    /**
     * 查询近七天交易记录
     * @param array $list 查询订单的记录
     * @param int $days 查询天数默认7
     * @param bool $is_all 是否是全部订单
     * @return array $transaction["price"] 七日 每日交易金额
     */
    private function selectTransaction(array $list, $days = 7, $is_all = false)
    {
        $transaction = array();
        $days        = (int)$days;

        if (!empty($list)) {
            $i = $days;

            while (1 <= $i) {
                $transaction['price'][date('Y-m-d', time() - $i * 3600 * 24)] = 0;
                $transaction['count'][date('Y-m-d', time() - $i * 3600 * 24)] = 0;
                --$i;
            }

            if (empty($is_all)) {
                foreach ($list as $key => $value) {
                    if (array_key_exists(date('Y-m-d', $value['paytime']), $transaction['price'])) {
                        $transaction['price'][date('Y-m-d', $value['paytime'])] += $value['price'];
                        $transaction['count'][date('Y-m-d', $value['paytime'])] += 1;
                    }
                }
            } else {
                foreach ($list as $key => $value) {
                    if (array_key_exists(date('Y-m-d', $value['createtime']), $transaction['price'])) {
                        $transaction['price'][date('Y-m-d', $value['createtime'])] += $value['price'];
                        $transaction['count'][date('Y-m-d', $value['createtime'])] += 1;
                    }
                }
            }

            return $transaction;
        }

        return array();
    }

    public function detail()
    {
        $id = intval($this->params['id']);

        $item = OrderServiceFacade::getInfo(['id' => $id]);

        $orderData            = OrderDataServiceFacade::getInfo(['orderid' => $id]);
        $item['data']         = $orderData;
        $item['statusvalue']  = $item['status'];
        $item['paytypevalue'] = $item['paytype'];

        if ($this->request->isPost()) {
            OrderServiceFacade::updateInfo(array('remark' => trim($this->params['remark'])), array('id' => $item['id'], 'uniacid' => $this->uniacid));

            // plog('order.op.remarksaler', '订单保存备注  ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
            // $this->message('订单备注保存成功！', webUrl('order', array('op' => 'detail', 'id' => $item['id'])), 'success');

            show_json(1, ['message' => '订单备注保存成功！', 'url' => webUrl('web.order.index', array('op' => 'detail', 'id' => $item['id']))]);
        }

        $member = MemberServiceFacade::getInfo(['id' => $item['mid']]);

        $dispatch = DispatchServiceFacade::getInfo(['id' => $item['dispatchid']]);

        $user = [];
        if ($item['service_type'] == 1) {
            if (empty($orderData['addressid'])) {
                $user = unserialize($orderData['carrier']);
            } else {
                $user = iunserializer($orderData['address']);

                $user['address']     = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['street'] . ' ' . $user['address'];
                $item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
            }

            if (0 < $item['data']['sendtype']) {
                $order_goods = Db::query('SELECT orderid,goodsid,sendtype,expresssn,expresscom,express,sendtime FROM ' . tablename('store_order_goods') . '
            WHERE orderid = ' . $id . ' and sendtime > 0 and uniacid=' . $this->uniacid . ' and sendtype > 0 group by sendtype order by sendtime desc ');

                foreach ($order_goods as $key => $value) {
                    $order_goods[$key]['goods'] = Db::query('select g.id,g.title,g.thumb,og.sendtype,g.ispresell,og.price from ' . tablename('store_order_goods') . ' og ' . ' left join ' . tablename('store_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid and og.sendtype=' . $value['sendtype'] . ' ', array('uniacid' => $this->uniacid, 'orderid' => $id));
                }

                $item['sendtime'] = $order_goods[0]['sendtime'];
            }

            $goods = Db::query('SELECT op.id as option_id,op.specs,g.*,o.total,g.type,op.title optionname,o.optionid,o.price as orderprice,o.price realprice,o.changeprice,o.oldprice' . ' FROM ' . tablename('store_order_goods') . ' o left join ' . tablename('store_goods') . ' g on o.goodsid=g.id ' . ' left join ' . tablename('store_goods_option') . ' op on o.optionid=op.id ' . ' WHERE o.orderid=:orderid and o.uniacid=:uniacid', array('orderid' => $id, 'uniacid' => $this->uniacid));
            foreach ($goods as &$r) {
                $r['marketprice'] = $r['orderprice'] / $r['total'];
            }
            unset($r);
            $item['goods'] = $goods;
        } else {
            $goods         = Db::query('SELECT g.*,o.price as orderprice,o.price realprice,o.changeprice,o.oldprice' . ' FROM ' . tablename('store_order_course') . ' o left join ' . tablename('store_goods') . ' g on o.goodsid=g.id ' . ' WHERE o.orderid=:orderid and o.uniacid=:uniacid', array('orderid' => $id, 'uniacid' => $this->uniacid));
            $item['goods'] = $goods;
        }

        $refund = Db::query('SELECT * FROM ' . tablename('store_order_refund') . ' WHERE orderid = :orderid and uniacid=:uniacid order by id desc', array('orderid' => $item['id'], 'uniacid' => $this->uniacid));

        $coupon = false;
        if (!empty($item['couponid'])) {
            $coupon = CouponServiceFacade::getInfo(['id' => $item['couponid']]);
        }

        $result = [
            'item'     => $item,
            'user'     => $user,
            'coupon'   => $coupon,
            'refund'   => $refund,
            'member'   => $member,
            'dispatch' => $dispatch,
        ];
        return $this->template('detail', $result);
    }
}
