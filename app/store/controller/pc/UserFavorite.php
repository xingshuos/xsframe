<?php


namespace app\store\controller\pc;

use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\UserFavoriteServiceFacade;
use xsframe\exception\ApiException;

class UserFavorite extends Base
{
    public function index()
    {
        $favoriteList = UserFavoriteServiceFacade::getAll(['mid' => $this->userId, 'deleted' => 0], "id,goodsid", "createtime desc");
        $favoriteList = GoodsServiceFacade::listMergeGoodsInfo($favoriteList, 'goodsid');

        $result = [
            'favoriteList' => $favoriteList,
        ];
        return $this->success($result);
    }

    /**
     * 添加收藏
     * @return array
     * @throws ApiException
     */
    public function add()
    {
        $userId  = $this->userId;
        $goodsId = $this->params['goodsid'] ?? 0;

        $isExitGoods = GoodsServiceFacade::getTotal(['id' => $goodsId, 'status' => 1, 'deleted' => 0]);

        if (empty($isExitGoods)) {
            throw new ApiException("商品未找到");
        }

        $insertId = UserFavoriteServiceFacade::addFavorite($userId, $goodsId);
        return $this->success(['isAdd' => $insertId]);
    }

    public function delete()
    {
        $id        = $this->params['id'] ?? 0;
        $isDeleted = UserFavoriteServiceFacade::updateInfo(['deleted' => 1], ['id' => $id, 'mid' => $this->userId]);
        $result    = ['isDeleted' => $isDeleted];
        return $this->success($result);
    }
}