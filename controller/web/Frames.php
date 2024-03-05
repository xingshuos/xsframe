<?php

namespace app\xs_cloud\controller\web;

use app\xs_cloud\facade\service\FramesVersionServiceFacade;
use xsframe\base\AdminBaseController;
use think\facade\Db;

class Frames extends AdminBaseController
{
    public function index()
    {
        return redirect("/{$this->module}/web.frames/main");
    }

    public function main()
    {
        $keyword = $this->params['keyword'];
        $status = $this->params['status'];
        $searchTime = trim($this->params["searchtime"]);
        $export = $this->params['export'];

        $startTime = strtotime("-1 month");
        $endTime = time();

        $condition = [
            'deleted' => 0,
        ];

        if (!empty($type)) {
            $status = $type;
        }

        if (is_numeric($status)) {
            $condition['status'] = $status;
        }

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, array("create"))) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime = strtotime($this->params["time"]["end"]);

            $condition[$searchTime . "time"] = Db::raw("between {$startTime} and {$endTime} ");
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" nickname like '%" . trim($keyword) . "%' or realname like '%" . trim($keyword) . "%' or mobile like '%" . trim($keyword) . "%' ");
        }

        $list = FramesVersionServiceFacade::getList($condition, "*", "id desc", $this->pIndex, $this->pSize);
        $total = FramesVersionServiceFacade::getTotal($condition);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $result = [
            'list'  => $list,
            'pager' => $pager,
            'total' => $total,

            'starttime' => $startTime,
            'endtime'   => $endTime,
        ];

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

            if (!empty($id)) {
                unset($data['createtime']);
                FramesVersionServiceFacade::updateInfo($data, ['id' => $id]);
            } else {
                $id = FramesVersionServiceFacade::insertInfo($data);
            }

            show_json(1, array("url" => webUrl("web.activity.frame/edit", array("id" => $id, "tab" => str_replace("#tab_", "", $this->params["tab"])))));
        }

        $item = FramesVersionServiceFacade::getInfo(['id' => $id]);

        return $this->template('post', ['item' => $item]);
    }

    public function delete()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, array("message" => "参数错误"));
        }
        $items = FramesVersionServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            FramesVersionServiceFacade::updateInfo(['deleted' => 1], ['id' => $item['id']]);
        }
        $this->success(array("url" => referer()));
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

        $type = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $items = FramesVersionServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            FramesVersionServiceFacade::updateInfo([$type => $value], ['id' => $item['id']]);
        }

        $this->success();
    }
}
