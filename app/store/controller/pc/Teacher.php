<?php


namespace app\store\controller\pc;

use app\store\facade\service\AdvServiceFacade;
use app\store\facade\service\GoodsCategoryServiceFacade;
use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\TeacherServiceFacade;
use think\facade\Db;

class Teacher extends Base
{

    public function index()
    {
        $id      = $this->params['id'] ?? 0;
        $keyword = $this->params['keyword'] ?? '';

        $advList = AdvServiceFacade::getList(['uniacid' => $this->uniacid, 'enabled' => 1, 'deleted' => 0, 'type' => 4], "id,title,thumb,link", "displayorder desc,id desc");
        $advList = set_medias($advList, ['thumb']);

        $pageTitle = "艺术家";
        switch ($id) {
            case 1:
                $pageTitle = "书法" . $pageTitle;
                break;
            case 2:
                $pageTitle = "国画" . $pageTitle;
                break;
            case 3:
                $pageTitle = "西画" . $pageTitle;
                break;
        }

        # 书法艺术家作品数量
        $total1 = TeacherServiceFacade::getTotal(['pcateid' => 1, 'enabled' => 1, 'is_deleted' => 0]);
        # 国画艺术家作品数量
        $total2 = TeacherServiceFacade::getTotal(['pcateid' => 2, 'enabled' => 1, 'is_deleted' => 0]);
        # 西画艺术家作品数量
        $total3 = TeacherServiceFacade::getTotal(['pcateid' => 3, 'enabled' => 1, 'is_deleted' => 0]);

        $list1 = [];
        $list2 = [];
        $list3 = [];

        $condition = [
            'enabled' => 1, 'is_deleted' => 0
        ];
        if (!empty($keyword)) {
            $condition[''] = Db::raw(" ( name like '%" . trim($keyword) . "%' or sub_name like '%" . trim($keyword) . "%' ) ");
        }

        # 书法艺术家列表
        if (empty($id) || $id == 1) {
            $condition['pcateid'] = 1;

            $list1 = TeacherServiceFacade::getList($condition, "id,name,avatar", "", $this->pIndex, 12);
            foreach ($list1 as &$item) {
                $item['works_total'] = GoodsServiceFacade::getTotal(['teacherid' => $item['id'], 'status' => 1]);
            }
            unset($item);
        }

        # 国画艺术家列表
        if (empty($id) || $id == 2) {
            $condition['pcateid'] = 2;

            $list2 = TeacherServiceFacade::getList($condition, "id,name,avatar", "", $this->pIndex, 12);
            foreach ($list2 as &$item) {
                $item['works_total'] = GoodsServiceFacade::getTotal(['teacherid' => $item['id'], 'status' => 1]);
            }
            unset($item);
        }

        # 西画艺术家列表
        if (empty($id) || $id == 3) {
            $condition['pcateid'] = 3;

            $list3 = TeacherServiceFacade::getList($condition, "id,name,avatar", "", $this->pIndex, 12);
            foreach ($list3 as &$item) {
                $item['works_total'] = GoodsServiceFacade::getTotal(['teacherid' => $item['id'], 'status' => 1]);
            }
            unset($item);
        }

        $list1 = set_medias($list1, 'avatar');
        $list2 = set_medias($list2, 'avatar');
        $list3 = set_medias($list3, 'avatar');

        $result = [
            'pageTitle' => $pageTitle,
            'keyword'   => $keyword,
            'id'        => $id,
            'advList'   => $advList,

            'total'  => intval($total1) + intval($total2) + intval($total3),
            'total1' => intval($total1),
            'total2' => intval($total2),
            'total3' => intval($total3),

            'list1' => $list1,
            'list2' => $list2,
            'list3' => $list3,
        ];

        return $this->template('pc/teacher/index', $result);
    }

    public function detail()
    {
        $id      = $this->params['id'] ?? 0;
        $advList = AdvServiceFacade::getList(['uniacid' => $this->uniacid, 'enabled' => 1, 'deleted' => 0, 'type' => 4], "id,title,thumb,link", "displayorder desc,id desc");
        $advList = set_medias($advList, ['thumb']);

        $condition = [
            'parentid' => 0,
            'uniacid'  => $this->uniacid,
            'deleted'  => 0,
        ];

        $category = GoodsCategoryServiceFacade::getAll($condition, "*", 'displayorder desc,id asc');

        $teacherInfo           = TeacherServiceFacade::getInfo(['id' => $id]);
        $teacherInfo['avatar'] = tomedia($teacherInfo['avatar']);

        $result = [
            'id'          => $id,
            'teacherInfo' => $teacherInfo,
            'category'    => $category,
            'advList'     => $advList,
        ];

        return $this->template('pc/teacher/detail', $result);
    }

    public function getTeacherList()
    {
        $keyword = $this->params['keyword'] ?? '';

        $where = [
            'uniacid'    => $this->uniacid,
            'enabled'    => 1,
            'is_deleted' => 0,
        ];

        if (!empty($keyword)) {
            $where[''] = Db::raw(" ( name like '%" . trim($keyword) . "%' or sub_name like '%" . trim($keyword) . "%' ) ");
        }

        $teacherList  = TeacherServiceFacade::getList($where, "id,name,avatar", "id desc", $this->pIndex, $this->pSize);
        $teacherList  = set_medias($teacherList, ['avatar']);
        $teacherTotal = TeacherServiceFacade::getTotal($where);

        foreach ($teacherList as &$item) {
            $item['works_total'] = GoodsServiceFacade::getTotal(['teacherid' => $item['id'], 'status' => 1]);
            $item['url']         = strval(url('artist/detail', ['id' => $item['id']]));
        }
        unset($item);

        $result = [
            'teacherList'  => $teacherList,
            'teacherTotal' => $teacherTotal,
        ];

        return $this->success($result);
    }
}