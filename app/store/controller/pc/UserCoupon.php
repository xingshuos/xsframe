<?php

namespace app\store\controller\pc;

class UserCoupon extends Base
{

    public function index()
    {
        $result = [
        ];

        return $this->template('pc/user/coupon/list', $result);
    }

    public function get()
    {
        $result = [
        ];

        return $this->template('pc/user/coupon/get', $result);
    }

}