<?php

namespace app\xs_cloud\controller\web;

use app\xs_cloud\facade\service\AppServiceFacade;
use app\xs_cloud\facade\service\AppVersionServiceFacade;
use xsframe\base\AdminBaseController;
use xsframe\util\PinYinUtil;

class Apps extends AdminBaseController
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

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, array("create","update"))) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime = strtotime($this->params["time"]["end"]);
            $condition .= " and `" . $searchTime . "_time" . "` between " . $startTime . " and " . $endTime;
        }

        if (!empty($keyword)) {
            $condition .= " and ( name like :name or version like :version or identifier like :identifier ) ";
            $params['name'] = "%" . $keyword . "%";
            $params['version'] = $params['title'];
            $params['identifier'] = $params['title'];
        }

        $list = AppServiceFacade::getList([$condition, $params], "*", "id desc");
        $total = AppServiceFacade::getTotal([$condition, $params]);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item) {
            $item['updateTotal'] = AppVersionServiceFacade::getTotal(['identifier' => $item['identifier']]);
            $item['logo'] = tomedia($item['logo']);
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
                'name'         => trim($this->params['name']),
                'logo'         => trim($this->params['logo']),
                'version'      => trim($this->params['version']),
                'ability'      => trim($this->params['ability']),
                'description'  => trim($this->params['description']),
                'author'       => trim($this->params['author']),
                'status'       => intval($this->params['status']),
                'name_initial' => PinYinUtil::getFirstPinyin($this->params['name']),

                "wechat_support" => intval($this->params["wechat_support"]),
                "wxapp_support"  => intval($this->params["wxapp_support"]),
                "pc_support"     => intval($this->params["pc_support"]),
                "app_support"    => intval($this->params["app_support"]),
                "h5_support"     => intval($this->params["h5_support"]),
                "aliapp_support" => intval($this->params["aliapp_support"]),
                "bdapp_support"  => intval($this->params["bdapp_support"]),
                "uniapp_support" => intval($this->params["uniapp_support"]),

                'update_time' => time(),
            );

            if (!empty($id)) {
                unset($data['createtime']);
                AppServiceFacade::updateInfo($data, ['id' => $id]);
            } else {
                $id = AppServiceFacade::insertInfo($data);
            }
            show_json(1, array("url" => webUrl("apps/edit", array("id" => $id, "tab" => str_replace("#tab_", "", $this->params["tab"])))));
        }

        $item = AppServiceFacade::getInfo(['id' => $id]);

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
        $items = AppServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            AppServiceFacade::updateInfo(['deleted' => 1], ['id' => $item['id']]);
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

        $items = AppServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            AppServiceFacade::updateInfo([$type => $value], ['id' => $item['id']]);
        }

        $this->success();
    }
}
