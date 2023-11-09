<?php


namespace app\store\controller\pc;

use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\UserCartServiceFacade;
use xsframe\util\PriceUtil;

class Cart extends Base
{

    public function index()
    {
        $userId = $this->userId;

        $cartList = UserCartServiceFacade::getAll(['mid' => $userId, 'deleted' => 0], "id,goodsid,optionid,total", "createtime desc");
        $cartList = GoodsServiceFacade::listMergeGoodsInfo($cartList, 'goodsid');

        $total    = 0;
        $sumPrice = 0;

        if (!empty($cartList)) {
            foreach ($cartList as &$item) {
                $total    = $total + $item['total'];
                $sumPrice = $sumPrice + PriceUtil::numberFormat($item['total'] * $item['goods']['marketprice']);
            }
            unset($item);
        }

        $result = [
            'cartList' => $cartList,
            'sumTotal' => $total,
            'sumPrice' => PriceUtil::numberFormat($sumPrice),
        ];

        if ($this->request->isAjax()) {
            return $this->success($result);
        }

        return $this->template('pc/cart/list');
    }

    public function delete()
    {
        $id        = $this->params['id'] ?? 0;
        $isDeleted = UserCartServiceFacade::updateInfo(['deleted' => 1, 'total' => 0], ['id' => $id, 'mid' => $this->userId]);
        $result    = ['isDeleted' => $isDeleted];
        return $this->success($result);
    }

    public function edit()
    {
        $userId = $this->userId;
        $id     = $this->params['id'] ?? 0;
        $total  = $this->params['total'] ?? 1;

        $isUpdate = UserCartServiceFacade::updateInfo(['total' => $total], ['id' => $id, 'mid' => $userId]);
        $result   = ['isUpdate' => $isUpdate];
        return $this->success($result);
    }

    public function add()
    {
        $userId   = $this->userId;
        $goodsId  = $this->params['goodsid'] ?? 0;
        $optionId = $this->params['optionid'] ?? 0;
        $total    = $this->params['total'] ?? 1;

        $insertId = UserCartServiceFacade::addCart($userId, $goodsId, $optionId, $total);
        return $this->success(['isAdd' => $insertId]);
    }

}