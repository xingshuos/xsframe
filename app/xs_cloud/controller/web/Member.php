<?php

namespace app\xs_cloud\controller\web;

use app\xs_cloud\facade\service\MemberAppServiceFacade;
use app\xs_cloud\facade\service\MemberServiceFacade;
use xsframe\base\AdminBaseController;

class Member extends AdminBaseController
{
    public function index()
    {
        return $this->main();
    }

    public function main()
    {
        $keyword = trim($this->params['keyword']);
        $status = $this->params['status'];
        $searchTime = trim($this->params["searchtime"]);

        $startTime = strtotime("-1 month");
        $endTime = time();

        $condition = " deleted = 0 ";
        $params = [];

        if (!empty($type)) {
            $status = $type;
        }

        if (is_numeric($status)) {
            $condition .= " and `status` = " . intval($status);
        }

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, array("create"))) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime = strtotime($this->params["time"]["end"]);
            $condition .= " and `" . $searchTime . "time" . "` between " . $startTime . " and " . $endTime;
        }

        if (!empty($keyword)) {
            $condition .= " and ( username like :title or code like :code ) ";
            $params['username'] = "%{$keyword}%";
            $params['code'] = "%{$keyword}%";
        }

        $list = MemberServiceFacade::getList([$condition, $params], "*", "id desc");
        $total = MemberServiceFacade::getTotal([$condition, $params]);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item){
            $item['appTotal'] = MemberAppServiceFacade::getTotal(['mid' => $item['id']]);
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
                'version'    => trim($this->params['version']),
                'title'      => trim($this->params['title']),
                'content'    => htmlspecialchars_decode($this->params['content']),
                'status'     => intval($this->params['status']),
                'createtime' => time(),
            );

            if (!empty($id)) {
                unset($data['createtime']);
                MemberServiceFacade::updateInfo($data, ['id' => $id]);
            } else {
                $id = MemberServiceFacade::insertInfo($data);
            }
            show_json(1, array("url" => webUrl("frames/edit", array("id" => $id, "tab" => str_replace("#tab_", "", $this->params["tab"])))));
        }

        $item = MemberServiceFacade::getInfo(['id' => $id]);

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
        $items = MemberServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            MemberServiceFacade::updateInfo(['deleted' => 1], ['id' => $item['id']]);
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

        $items = MemberServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            MemberServiceFacade::updateInfo([$type => $value], ['id' => $item['id']]);
        }

        $this->success();
    }
}
