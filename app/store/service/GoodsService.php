<?php

namespace app\store\service;

use xsframe\base\BaseService;
use xsframe\exception\ApiException;
use xsframe\util\PriceUtil;

class GoodsService extends BaseService
{
    protected $tableName = "shop_goods";

    /**
     * 获取商品详情
     * @param $id
     * @param string $field
     * @return array|mixed|\think\facade\Db|\think\Model|null
     * @throws ApiException
     */
    public function getDetail($id, $field = "*")
    {
        $field .= ",updatetime";

        $goodsInfo = $this->getInfo(['id' => $id], $field);

        if (empty($goodsInfo)) {
            throw new ApiException("数据不存在或已下架");
        }

        $goodsInfo['thumb']       = tomedia($goodsInfo['thumb']);
        $goodsInfo['marketprice'] = PriceUtil::numberFormat($goodsInfo['marketprice']);
        $goodsInfo['url']         = strval(url('goods/' . $goodsInfo['id'], [], true, true));

        if (!empty($goodsInfo['thumb_url'])) {
            $thumbs = iunserializer($goodsInfo["thumb_url"]);
            if (empty($thumbs)) {
                $thumbs = array($goodsInfo["thumb"]);
                if (!empty($goodsInfo["thumb_first"]) && !empty($goodsInfo["thumb"])) {
                    $thumbs = array_merge(array($goodsInfo["thumb"]), $thumbs);
                }
                if (is_array($thumbs) && count($thumbs) == 2) {
                    $thumbs = array_unique($thumbs);
                }
                $thumbs = array_values($thumbs);
            } else {
                if (!empty($goodsInfo["thumb_first"]) && !empty($goodsInfo["thumb"])) {
                    $thumbs = array_merge(array($goodsInfo["thumb"]), $thumbs);
                }
                $thumbs = array_values($thumbs);
            }
            $goodsInfo["thumbs"] = set_medias($thumbs);
            unset($goodsInfo['thumb_url']);
        }

        if (isset($goodsInfo['isnew'])) { // # 新商品（超过7天后取消显示）
            if ($goodsInfo['isnew'] == 1) {
                if (time() - intval($goodsInfo['updatetime']) > 7 * 76400) {
                    $goodsInfo['isnew'] = 0;
                }
            }
        }

        unset($goodsInfo['updatetime']);
        return $goodsInfo;
    }

    /**
     * 列表中合并商品数据
     * @param $list
     * @param string $column
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function listMergeGoodsInfo($list, $column = 'goodsid')
    {
        $goodsList = $this->getAll(['id' => array_column($list, $column)], "id,title,thumb,marketprice,unit,total,originalprice", "createtime desc", "id");
        $goodsList = set_medias($goodsList, ['thumb']);

        foreach ($list as $key => &$item) {
            $item['goods'] = $goodsList[$item['goodsid']];
            $item['url']   = strval(url('goods/' . $item['goodsid'], [], true, true));
        }
        unset($item);

        return $list;
    }
}