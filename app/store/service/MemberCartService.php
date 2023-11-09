<?php

namespace app\store\service;

use app\store\facade\service\GoodsServiceFacade;
use xsframe\base\BaseService;
use xsframe\exception\ApiException;
use xsframe\util\PriceUtil;
use think\facade\Db;

class UserCartService extends BaseService
{
    protected $tableName = "shop_member_cart";
    protected $goodsTableName = "shop_goods";

    /**
     * 加入购物车
     * @param $userId
     * @param $goodsId
     * @param $optionId
     * @param int $total
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addCart($userId, $goodsId, $optionId, $total = 1)
    {
        $data = [
            'uniacid'    => $this->uniacid,
            'mid'        => $userId,
            'goodsid'    => $goodsId,
            'optionid'   => $optionId,
            'total'      => $total,
            'createtime' => time(),
            'deleted'    => 0,
        ];

        if (empty($goodsId)) {
            throw new ApiException("参数错误");
        }

        $goodsInfo = GoodsServiceFacade::getInfo(['id' => $goodsId, 'status' => 1, 'deleted' => 0], 'id,title,total');
        if (empty($goodsInfo)) {
            throw new ApiException("产品已下架");
        }

        $cartInfo = $this->getInfo(['mid' => $userId, 'goodsid' => $goodsId, 'optionid' => $optionId]);

        $total = $total + ((empty($cartInfo) || $cartInfo['deleted'] == 1) ? 0 : intval($cartInfo['total']));

        if ($goodsInfo['total'] >= 0) {
            if ($goodsInfo['total'] < $total) {
                throw new ApiException("“{$goodsInfo['title']}”库存不足");
            }
        }

        if ($cartInfo) {
            $this->updateInfo(['total' => $total, 'deleted' => 0], ['id' => $cartInfo['id']]);
        } else {
            $this->insertInfo($data);
        }

        return true;
    }
}