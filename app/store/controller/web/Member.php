<?php

namespace app\store\controller\web;

use app\store\facade\service\MemberServiceFacade;
use xsframe\base\AdminBaseController;
use xsframe\util\ExcelUtil;
use think\facade\Db;

class Member extends AdminBaseController
{
    private $memberTableName = 'store_member';

    public function index()
    {
        return redirect("/{$this->module}/web.member.wxapp/main");
    }

    public function main()
    {
        $keyword    = $this->params['keyword'];
        $status     = $this->params['status'];
        $searchTime = trim($this->params["searchtime"]);
        $export     = $this->params['export'];

        $startTime = strtotime("-1 month");
        $endTime   = time();

        $condition = [
            'uniacid' => $this->uniacid,
        ];

        if (!empty($type)) {
            $status = $type;
        }

        if (is_numeric($status)) {
            $condition['status'] = $status;
        }

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, array("create"))) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime   = strtotime($this->params["time"]["end"]);

            $condition[$searchTime . "time"] = Db::raw("between {$startTime} and {$endTime} ");
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" nickname like '%" . trim($keyword) . "%' or realname like '%" . trim($keyword) . "%' or mobile like '%" . trim($keyword) . "%' ");
        }

        $list  = MemberServiceFacade::getList($condition, "*", "id desc", $this->pIndex, $this->pSize);
        $total = MemberServiceFacade::getTotal($condition);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item) {
            $item['avatar'] = tomedia($item['avatar']);
        }
        unset($item);

        # 导出Excel数据
        if (!empty($export)) {
            $list = Db::name($this->memberTableName)->where($condition)->order('id desc')->select()->toArray();
            foreach ($list as &$item) {
                $item['createtime'] = date("Y-m-d H:i:s", $item['createtime']);
                $item['mobile']     = $item['mobile'] . "\t";
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
        // dump($result);die;
        return $this->template('list', $result);
    }

    public function edit()
    {
        return $this->post();
    }

    public function add()
    {
        return $this->post();
    }

    public function post()
    {
        $id = $this->params['id'];

        if ($this->request->isPost()) {
            $data = array(
                'uniacid'  => $this->uniacid,
                'nickname' => trim($this->params['nickname']),
                'avatar'   => trim($this->params['avatar']),
                'realname' => trim($this->params['realname']),
                'mobile'   => trim($this->params['mobile']),
                'gender'   => trim($this->params['gender']),
            );
            if (!empty($this->params['birthday_str'])) {
                $birthdayDate       = strtotime($this->params['birthday_str']);
                $data['birthyear']  = date('Y', $birthdayDate);
                $data['birthmonth'] = date('m', $birthdayDate);
                $data['birthday']   = date('d', $birthdayDate);
            }
            if (!empty($id)) {
                Db::name($this->memberTableName)->where(['id' => $id])->update($data);
            } else {
                $id = Db::name($this->memberTableName)->insertGetId($data);
            }
            show_json(1, array('url' => webUrl('web.member.wxapp/edit', array('id' => $id))));
        }

        $item = Db::name($this->memberTableName)->where(['id' => $id])->find();

        $item['birthday_str'] = $item['birthyear'] . '-' . $item['birthmonth'] . '-' . $item['birthday'];

        return $this->template('post', ['item' => $item]);
    }

    private function exportExcelData($list)
    {
        $title    = '微信用户信息';
        $column   = ['ID', '昵称', 'openid', '注册时间'];
        $setWidh  = ['15', '15', '50', '20'];
        $keys     = ['id', 'nickname', 'wxapp_openid', 'createtime'];
        $last     = [];
        $filename = $title;
        ExcelUtil::export($title, $column, $setWidh, $list, $keys, $last, $filename);
    }

    public function change()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, array("message" => "参数错误"));
        }

        $type  = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $items = Db::name($this->memberTableName)->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name($this->memberTableName)->where("id", '=', $item['id'])->update([$type => $value]);
        }

        $this->success();
    }

    public function query()
    {
        $keyword = trim($this->params['keyword']);

        $condition            = array();
        $condition['uniacid'] = $this->uniacid;

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" nickname like '%" . trim($keyword) . "%' ");
        }

        $memberList = Db::name($this->memberTableName)->where($condition)->select();

        foreach ($memberList as &$value) {
            $value['nickname'] = htmlspecialchars($value['nickname'], ENT_QUOTES);
        }
        unset($value);

        $result = [
            'memberList' => $memberList
        ];
        return $this->template('query', $result);
    }

}
