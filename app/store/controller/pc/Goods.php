<?php


namespace app\store\controller\pc;

use app\store\facade\service\AdvServiceFacade;
use app\store\facade\service\ChaptersItemServiceFacade;
use app\store\facade\service\ChaptersServiceFacade;
use app\store\facade\service\GoodsCategoryServiceFacade;
use app\store\facade\service\GoodsHistoryServiceFacade;
use app\store\facade\service\GoodsParamServiceFacade;
use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\GoodsTagsServiceFacade;
use app\store\facade\service\TeacherServiceFacade;
use app\store\facade\service\UserCartServiceFacade;
use app\store\facade\service\UserCourseServiceFacade;
use app\store\facade\service\UserFavoriteServiceFacade;
use xsframe\exception\ApiException;
use think\facade\Db;

class Goods extends Base
{

    // 获取商品列表（根据条件）
    public function getGoodsList()
    {
        $tagsId    = $this->params['tagsid'] ?? 0;
        $teacherId = $this->params['teacherid'] ?? 0;
        $isHistory = $this->params['ishistory'] ?? 0;

        $keyword = $this->params['keyword'] ?? '';
        $orderBy = $this->params['order_by'] ?? '';

        $goodsWhere = [
            'uniacid' => $this->uniacid,
            'status'  => 1,
            'deleted' => 0,
        ];

        if (!empty($tagsId)) {
            $goodsWhere['tags_id'] = $tagsId;
        }

        if (!empty($teacherId)) {
            $goodsWhere['teacherid'] = $teacherId;
        }

        if (!empty($keyword)) {
            $goodsWhere[''] = Db::raw(" ( title like '%" . trim($keyword) . "%' or goodssn like '%" . trim($keyword) . "%' ) ");
        }

        if (!empty($isHistory)) {
            $goodsIds = GoodsHistoryServiceFacade::getAll(['mid' => $this->userId], "", "", 'goodsid');
            $goodsWhere[''] = Db::raw(" id in (" . implode(",",array_column($goodsIds, 'goodsid')) . ") ");
        }

        $orderBySql = "";
        if (!empty($orderBy)) {
            if ($orderBy == 'hot') {
                $orderBySql = "ishot desc,";
            } else if ($orderBy == 'create') {
                $orderBySql = "createtime desc,";
            } else if ($orderBy == 'update') {
                $orderBySql = "updatetime desc,";
            } else if ($orderBy == 'price') {
                $orderBySql = "marketprice desc,";
            }
        }

        $orderBySql .= "displayorder desc,updatetime desc,id asc";
        $goodsList  = GoodsServiceFacade::getList($goodsWhere, "id,title,subtitle,thumb,thumb_size,marketprice,goodssn,viewcount,teacherid", $orderBySql, $this->pIndex, $this->pSize);
        $goodsList  = set_medias($goodsList, ['thumb']);
        $goodsTotal = GoodsServiceFacade::getTotal($goodsWhere);

        foreach ($goodsList as &$item) {
            $item['url']         = strval(url("view/" . $item['id'], [], true, true));
            $teacherInfo         = TeacherServiceFacade::getInfo(['id' => $item['teacherid']], "id,name");
            $item['teacherInfo'] = $teacherInfo;

            $item['thumb_size'] = json_decode($item['thumb_size'], true);
        }

        // dump($goodsList);
        // die;

        $result = [
            'goodsList'  => $goodsList,
            'goodsTotal' => $goodsTotal,
        ];

        return $this->success($result);
    }

    // 商品详情
    public function index()
    {
        $id = $this->params['id'];

        $goodsInfo = GoodsServiceFacade::getDetail($id);
        GoodsServiceFacade::updateInfo(['viewcount' => Db::raw('viewcount + 1')], ['id' => $id]);

        $categoryInfo = GoodsCategoryServiceFacade::getInfo(['id' => $goodsInfo['pcate'], 'enabled' => 1], "id,name,style_type");

        $contact           = $this->moduleSetting['contact'];
        $contact['qrcode'] = tomedia($contact['qrcode']);

        $cartTotal  = UserCartServiceFacade::getValue(['mid' => $this->userId, 'deleted' => 0], "sum(total) total");
        $isFavorite = UserFavoriteServiceFacade::getTotal(['mid' => $this->userId, 'goodsid' => $id, 'deleted' => 0]);

        $isPlay = 0;
        if ($goodsInfo['type'] == 2) {
            $isPlay = UserCourseServiceFacade::checkIsPlay($this->userId, $id);
        }

        $result = [
            'goodsInfo'    => $goodsInfo,
            'categoryInfo' => $categoryInfo,
            'contact'      => $contact,
            'cartTotal'    => $cartTotal,
            'isFavorite'   => $isFavorite,
            'isPlay'       => $isPlay,
        ];

        return $this->template('pc/goods/detail', $result);
    }

    /**
     * 获取商品详情
     * @return array
     * @throws ApiException
     */
    public function detail()
    {
        $id        = intval($this->params['id'] ?? 0);
        $goodsInfo = GoodsServiceFacade::getDetail($id, "id,title,thumb,marketprice,pcate,isnew,type,teacherid");

        $categoryInfo        = GoodsCategoryServiceFacade::getInfo(['id' => $goodsInfo['pcate'], 'enabled' => 1], "id,name");
        $categoryInfo['url'] = strval(url('gcate/' . $categoryInfo['id'], [], true, true));

        if ($goodsInfo['type'] == 2) {
            $goodsInfo['isPlay'] = UserCourseServiceFacade::checkIsPlay($this->userId, $id);
        }

        $result = [
            'goodsInfo'    => $goodsInfo,
            'categoryInfo' => $categoryInfo,
        ];
        return $this->success($result);
    }

    // 商品标签组
    public function tags()
    {
        $id      = $this->params['id'] ?? 0;
        $pcateid = $this->params['pcateid'] ?? 0;

        $advList = AdvServiceFacade::getList(['uniacid' => $this->uniacid, 'enabled' => 1, 'deleted' => 0, 'tagsid' => $id, 'type' => 3], "id,title,thumb,link", "displayorder desc,id desc");
        $advList = set_medias($advList, ['thumb']);

        $tagsInfo = GoodsTagsServiceFacade::getInfo(['id' => $id]);

        $teacherTotal = TeacherServiceFacade::getTotal(['is_deleted' => 0]);
        $teacherThumb = TeacherServiceFacade::getValue(['is_deleted' => 0], "avatar");

        $categoryList = GoodsCategoryServiceFacade::getAll(['enabled' => 1, 'parentid' => 0, 'deleted' => 0], "id,name", "displayorder desc,id asc", 'name');
        foreach ($categoryList as &$item) {
            $item['total'] = GoodsServiceFacade::getTotal(['tags_id' => $id, 'pcate' => $item['id'], 'status' => 1, 'deleted' => 0]);
            $item['thumb'] = GoodsServiceFacade::getValue(['tags_id' => $id, 'pcate' => $item['id'], 'status' => 1, 'deleted' => 0], "thumb");
            $item['thumb'] = tomedia($item['thumb']);
        }
        unset($item);

        $result = [
            'id'           => $id,
            'pcateid'      => $pcateid,
            'categoryList' => $categoryList,

            'advList'      => $advList,
            'tagsInfo'     => $tagsInfo,
            'teacherTotal' => $teacherTotal,
            'teacherThumb' => tomedia($teacherThumb),
        ];

        return $this->template('pc/goods/tags', $result);
    }

    // 商品试图
    public function view()
    {
        $id = $this->params['id'] ?? 0;

        $goodsInfo = GoodsServiceFacade::getInfo(['id' => $id]);

        $thumbOriginal = [];
        if (!empty($goodsInfo["thumb_original"])) {
            $thumbOriginal          = iunserializer($goodsInfo["thumb_original"]);
            $thumbOriginal['thumb'] = tomedia($thumbOriginal['thumb']) . "/";
        }

        # 更新浏览记录
        GoodsHistoryServiceFacade::updateHistory($this->userId, $id);

        $result = [
            'id'            => $id,
            'goodsInfo'     => $goodsInfo,
            'thumbOriginal' => $thumbOriginal,
        ];

        return $this->template('pc/goods/view', $result);
    }

    // 商品分类
    public function category()
    {
        $id      = $this->params['id'] ?? 0;
        $ccateid = $this->params['ccateid'] ?? 0;
        $keyword = $this->params['keyword'] ?? '';
        $orderBy = $this->params['order_by'] ?? '';

        $advList = AdvServiceFacade::getList(['uniacid' => $this->uniacid, 'enabled' => 1, 'deleted' => 0, 'cateid' => $id, 'type' => 2], "id,title,thumb,link", "displayorder desc,id desc");
        $advList = set_medias($advList, ['thumb']);

        $category         = GoodsCategoryServiceFacade::getInfo(['id' => $id]);
        $childrenCategory = GoodsCategoryServiceFacade::getAll(['parentid' => $id], "id,name,thumb,thumb_sel", "displayorder desc,id asc");
        $childrenCategory = set_medias($childrenCategory, ['thumb', 'thumb_sel']);

        $goodsWhere    = [
            'uniacid' => $this->uniacid,
            'pcate'   => $id,
            'status'  => 1,
            'deleted' => 0,
        ];
        $categoryChild = [];
        if (!empty($ccateid)) {
            $goodsWhere[''] = Db::raw(" FIND_IN_SET(" . intval($ccateid) . ",cates)<>0 ");
            $categoryChild  = GoodsCategoryServiceFacade::getInfo(['id' => $ccateid], "id,name");
        }
        if (!empty($keyword)) {
            $goodsWhere[''] = Db::raw(" ( title like '%" . trim($keyword) . "%' or subtitle like '%" . trim($keyword) . "%' ) ");
        }
        $orderBySql = "";
        if (!empty($orderBy)) {
            if ($orderBy == 'hot') {
                $orderBySql = "ishot desc,";
            } else if ($orderBy == 'create') {
                $orderBySql = "createtime desc,";
            } else if ($orderBy == 'update') {
                $orderBySql = "updatetime desc,";
            } else if ($orderBy == 'price') {
                $orderBySql = "marketprice desc,";
            }
        }
        $orderBySql .= "displayorder desc,updatetime desc,id asc";
        $goodsList  = GoodsServiceFacade::getList($goodsWhere, "id,title,subtitle,thumb,marketprice", $orderBySql, $this->pIndex, $this->pSize);
        $goodsList  = set_medias($goodsList, ['thumb']);
        $goodsTotal = GoodsServiceFacade::getTotal($goodsWhere);
        $pager      = pagination($goodsTotal, $this->pIndex, $this->pSize);

        $result = [
            'id'      => $id,
            'ccateid' => $ccateid,
            'orderBy' => $orderBy,
            'keyword' => $keyword,

            'advList'          => $advList,
            'category'         => $category,
            'categoryChild'    => $categoryChild,
            'childrenCategory' => $childrenCategory,

            'goodsList' => $goodsList,
            'pager'     => $pager,
        ];

        return $this->template('pc/goods/category', $result);
    }

    // 课程章节
    public function chapters()
    {
        $goodsId = $this->params['id'];

        $chaptersList = ChaptersServiceFacade::getChaptersAll($goodsId);

        $chaptersItemList = ChaptersItemServiceFacade::getItemsAll($goodsId, 2);
        foreach ($chaptersList as $key => $item) {
            $chaptersList[$key]['items'] = isset($chaptersItemList[$item['id']]) ? $chaptersItemList[$item['id']] : [];
        }

        $result = [
            'chaptersList' => $chaptersList,
        ];
        return $this->success($result);
    }

    // api 获取商品基本信息
    public function getGoodsInfo()
    {
        $id = $this->params['id'] ?? 0;

        $goodsInfo   = GoodsServiceFacade::getInfo(['id' => $id], "id,title,marketprice,goodssn,teacherid");

        $teacherInfo = TeacherServiceFacade::getInfo(['id' => $goodsInfo['teacherid']], "id,name,avatar,description");
        $teacherInfo['avatar'] = tomedia($teacherInfo['avatar']);

        $params      = GoodsParamServiceFacade::getAll(['goodsid' => $id], "*", "displayorder asc");

        $isFavorite = UserFavoriteServiceFacade::getTotal(['mid' => $this->userId, 'goodsid' => $id, 'deleted' => 0]);

        $result = ['goodsInfo' => $goodsInfo, 'params' => $params, 'teacherInfo' => $teacherInfo, 'isFavorite' => $isFavorite];

        return $this->success($result);
    }
}