<?php

namespace app\xs_form\controller\web\form;

use xsframe\base\AdminBaseController;
use xsframe\facade\service\DbServiceFacade;

class Basic extends AdminBaseController
{
    protected $tableName = "xs_form_data_basic";

    public function beforeSetPostData(&$updateData = [])
    {
        $updateData['education'] = implode(",", $this->params['education']??[]);
    }

    // 引入模态框
    public function table()
    {
        return $this->template("table");
    }

    // 获取模态框数据
    public function getTableList()
    {
        $this->pSize = 3;
        $where = ['uniacid' => $this->uniacid];

        if (!empty($this->params['keywords'])) {
            $where[] = ['title', 'like', "%" . trim($this->params['keywords']) . "%"];
        }

        $list = DbServiceFacade::name($this->tableName)->getList($where, "*", "id desc", $this->pIndex, $this->pSize);
        $total = DbServiceFacade::name($this->tableName)->getTotal($where);
        $pager = pagination2($total, $this->pIndex, $this->pSize, "", ['isajax' => true]);

        $result = [
            'id'    => 1,
            'list'  => $list,
            'total' => $total,
            'pager' => $pager,
        ];

        return $this->template("tableListTpl", $result);
    }
}
