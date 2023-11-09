<?php


namespace app\store\enum;

use xsframe\base\BaseEnum;

class OrderSnKeyEnum extends BaseEnum
{
    # 商品编码
    const GOODS_CODE = 'GC';

    # 课程编码
    const COURSE_CODE = 'CC';

    # 套餐编码
    const PACKAGE_CODE = 'PC';

    # 充值编码
    const RECHARGE_CODE = 'RC';

    # 升级编码
    const UPGRADE_CODE = 'UC';
}