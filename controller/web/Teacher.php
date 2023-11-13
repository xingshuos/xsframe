<?php

namespace app\xs_cloud\controller\web;

use app\xs_cloud\facade\service\GoodsCategoryServiceFacade;
use app\xs_cloud\facade\service\GoodsServiceFacade;
use app\xs_cloud\facade\service\TeacherServiceFacade;
use xsframe\base\AdminBaseController;
use xsframe\util\ExcelUtil;
use think\facade\Db;

class Teacher extends AdminBaseController
{
    protected $tableName = 'store_teacher';

    public function index()
    {
        return redirect("/{$this->module}/web.teacher/main");
    }

    public function main()
    {
        $keyword    = $this->params['keyword'];
        $searchTime = trim($this->params["searchtime"]);
        $export     = $this->params['export'];

        $startTime = strtotime("-1 month");
        $endTime   = time();

        $condition = [
            'uniacid' => $this->uniacid,
        ];

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, array("create"))) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime   = strtotime($this->params["time"]["end"]);

            $condition[$searchTime . "time"] = Db::raw("between {$startTime} and {$endTime} ");
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" name like '%" . trim($keyword) . "%' or sub_name like '%" . trim($keyword) . "%' or mobile like '%" . trim($keyword) . "%' ");
        }

        $list  = TeacherServiceFacade::getList($condition, "*", "id desc", $this->pIndex, $this->pSize);
        $total = TeacherServiceFacade::getTotal($condition);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item) {
            $item['course_count'] = GoodsServiceFacade::getTotal(['teacherid' => $item['id'], 'deleted' => 0]);
            $item['category_name'] = GoodsCategoryServiceFacade::getValue(['id' => $item['pcateid']],'name');
        }
        unset($item);

        # 导出Excel数据
        if (!empty($export)) {
            $list = TeacherServiceFacade::getAll($condition);
            foreach ($list as &$item) {
                $item['create_time'] = date("Y-m-d H:i:s", $item['create_time']);
                $item['mobile']      = $item['mobile'] . "\t";
            }
            unset($item);
            $this->exportExcelData($list);
        }

        $result = [
            'list'  => $list,
            'pager' => $pager,
            'total' => $total,

            'starttime' => $startTime,
            'endtime'   => $endTime,
        ];
        return $this->template('list', $result);
    }

    public function post()
    {
        $id = $this->params['id'];

        if ($this->request->isPost()) {
            $data = array(
                'uniacid'     => $this->uniacid,
                'pcateid'     => trim($this->params['cateid']),
                'name'        => trim($this->params['name']),
                'sub_name'    => trim($this->params['sub_name']),
                'mobile'      => trim($this->params['mobile']),
                'avatar'      => trim($this->params['avatar']),
                'thumb'       => trim($this->params['thumb']),
                'description' => trim($this->params['description']),
                'content'     => htmlspecialchars_decode($this->params['content']),
            );
            if (!empty($id)) {
                $data['update_time'] = time();
                TeacherServiceFacade::updateInfo($data, ['id' => $id]);
            } else {
                $data['create_time'] = time();
                $id                  = TeacherServiceFacade::insertInfo($data);
            }
            show_json(1, array('url' => webUrl('web.teacher/edit', array('id' => $id))));
        }

        $condition = [
            'parentid' => 0,
            'uniacid'  => $this->uniacid,
            'deleted'  => 0,
        ];

        $categorys = GoodsCategoryServiceFacade::getAll($condition, "id,name", 'displayorder desc,id asc');

        $item = TeacherServiceFacade::getInfo(['id' => $id]);
        return $this->template('post', ['item' => $item, 'categorys' => $categorys]);
    }

    private function exportExcelData($list)
    {
        $title    = '导师信息';
        $column   = ['ID', '姓名', '手机号', '副标题', '简介'];
        $setWidh  = ['15', '15', '20', '30', '100'];
        $keys     = ['id', 'name', 'mobile', 'sub_name', 'description'];
        $last     = [];
        $filename = $title;
        ExcelUtil::export($title, $column, $setWidh, $list, $keys, $last, $filename);
    }
}
