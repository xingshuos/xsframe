<?php


namespace app\store\controller\pc;

use app\store\facade\service\UserCourseServiceFacade;
use app\store\facade\service\MemberServiceFacade;
use app\store\facade\service\UserFavoriteServiceFacade;
use app\store\facade\service\OrderServiceFacade;
use app\store\facade\service\UserRecordServiceFacade;

class User extends Base
{

    public function index()
    {
        $memberInfo = MemberServiceFacade::getInfo(['id' => $this->userId]);

        $memberInfo['courseTotal']   = UserCourseServiceFacade::getTotal(['mid' => $this->userId, 'is_deleted' => 0]);
        $memberInfo['favoriteTotal'] = UserFavoriteServiceFacade::getTotal(['mid' => $this->userId, 'deleted' => 0]);

        $memberInfo['orderTotalStatus0'] = OrderServiceFacade::getTotal(['mid' => $this->userId, 'service_type' => 1, 'deleted' => 0, 'status' => 0]);
        $memberInfo['orderTotalStatus2'] = OrderServiceFacade::getTotal(['mid' => $this->userId, 'service_type' => 1, 'deleted' => 0, 'status' => 2]);
        $memberInfo['orderTotalStatus3'] = OrderServiceFacade::getTotal(['mid' => $this->userId, 'service_type' => 1, 'deleted' => 0, 'status' => 3]);
        $memberInfo['orderTotalStatus4'] = OrderServiceFacade::getTotal(['mid' => $this->userId, 'service_type' => 1, 'deleted' => 0, 'status' => 4]);

        $result = [
            'memberInfo' => $memberInfo
        ];
        return $this->template('pc/user/index', $result);
    }

    public function info()
    {
        $memberInfo           = $this->memberInfo;
        $memberInfo['id']     = $memberInfo['id'] + 50000;
        $memberInfo['mobile'] = substr_replace($memberInfo['mobile'], '****', 3, 4);;

        $result = [
            'memberInfo' => $memberInfo
        ];

        return $this->template('pc/user/info', $result);
    }

    public function update()
    {
        $nickname = $this->params['nickname'] ?? '';
        $realname = $this->params['realname'] ?? '';
        $gender   = $this->params['gender'] ?? '';

        $updateData = [];
        if (!empty($nickname)) {
            $updateData['nickname'] = $nickname;
        }
        if (!empty($realname)) {
            $updateData['realname'] = $realname;
        }
        if (!empty($gender)) {
            $updateData['gender'] = $gender;
        }

        if (!empty($updateData)) {
            MemberServiceFacade::updateInfo($updateData, ['id' => $this->userId]);
        }

        return $this->success();
    }

    public function address()
    {
        return $this->template('pc/user/address');
    }

    public function favorite()
    {
        return $this->template('pc/user/favorite');
    }

    public function order()
    {
        return $this->template('pc/user/order');
    }

    public function orderDetail()
    {
        $ordersn = $this->params['ordersn'];
        $result  = [
            'ordersn' => $ordersn
        ];
        return $this->template('pc/user/orderDetail', $result);
    }

    public function course()
    {
        return $this->template('pc/user/course');
    }

    public function history()
    {
        return $this->template('pc/user/history');
    }

    public function coupon()
    {
        return $this->template('pc/user/coupon');
    }

    public function couponGet()
    {
        return $this->template('pc/user/couponGet');
    }

    public function balance()
    {
        $sumRecharge = UserRecordServiceFacade::getValue(['mid' => $this->userId, 'type' => 1], "sum(fee)");
        $sumPay      = UserRecordServiceFacade::getValue(['mid' => $this->userId, 'type' => -1], "sum(fee)");

        $result = [
            'sumRecharge' => $sumRecharge,
            'sumPay'      => $sumPay,
        ];
        return $this->template('pc/user/balance', $result);
    }
}