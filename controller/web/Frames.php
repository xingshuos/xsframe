<?php

namespace app\xs_cloud\controller\web;

use app\xs_cloud\facade\service\FrameLogServiceFacade;
use app\xs_cloud\facade\service\FrameVersionServiceFacade;
use app\xs_cloud\service\FrameVersionService;
use xsframe\base\AdminBaseController;
use xsframe\util\FileUtil;
use xsframe\util\StringUtil;

class Frames extends AdminBaseController
{
    public function index()
    {
        return $this->main();
    }

    public function main()
    {
        $keyword = $this->params['keyword'];
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

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, array("create", "update"))) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime = strtotime($this->params["time"]["end"]);
            $condition .= " and `" . $searchTime . "time" . "` between " . $startTime . " and " . $endTime;
        }

        if (!empty($keyword)) {
            $condition .= " and ( title like :title or version like :version ) ";
            $params['title'] = "%" . trim($keyword) . "%";
            $params['version'] = $params['title'];
        }

        $list = FrameVersionServiceFacade::getList([$condition, $params], "*", "id desc");
        $total = FrameVersionServiceFacade::getTotal([$condition, $params]);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item) {
            $item['downloadTotal'] = FrameLogServiceFacade::getTotal(['version' => $item['version']]);
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
                'updatetime' => time(),
            );

            if (!empty($id)) {
                unset($data['createtime']);
                FrameVersionServiceFacade::updateInfo($data, ['id' => $id]);
            } else {
                $id = FrameVersionServiceFacade::insertInfo($data);
            }

            # 创建框架版本文件夹 是否存在最新版本 如果存在不允许再次发布到旧版本中
            $upgradeInfo = FrameVersionServiceFacade::getInfo(['status' => 1, 'deleted' => 0], "version,title,updatetime", "id desc");
            if (!version_compare($upgradeInfo['version'], $data['version'], '>')) {
                $this->createFramePackage($data['version']);
            }

            show_json(1, array("url" => webUrl("frames/edit", array("id" => $id, "tab" => str_replace("#tab_", "", $this->params["tab"])))));
        }

        $item = FrameVersionServiceFacade::getInfo(['id' => $id]);
        if (empty($item)) {
            $lastVersion = FrameVersionServiceFacade::getValue(['deleted' => 0], "version", "id desc");
            $item['version'] = StringUtil::incrementVersion($lastVersion);
        }

        return $this->template('post', ['item' => $item]);
    }

    // 创建软件包
    private function createFramePackage($version): bool
    {
        # 1.创建目录
        $framesPath = IA_ROOT . "/storage/releases/frames/{$version}";
        FileUtil::mkDirs($framesPath);

        # 2.主目录
        $mainDirectory = array(
            IA_ROOT . '/app/admin',
            IA_ROOT . '/config',
            IA_ROOT . '/extend',
            IA_ROOT . '/public/app/admin',
            IA_ROOT . '/vendor',
            IA_ROOT . '/public/index.php',
            IA_ROOT . '/public/router.php',
        );

        # 3.设置同步文件的类型
        $syncTypes = ['php', 'html', 'js', 'xml', 'css', 'png', 'jpg', 'jpeg', 'gif'];

        $data = [];
        foreach ($mainDirectory as $item) {
            FileUtil::oldDirToNewDir($item, $framesPath . str_replace(IA_ROOT, "", $item));
            $getDataItem = FileUtil::getDir($item, $syncTypes);

            foreach ($getDataItem as $i => $d) {
                $getDataItem[$i]['path'] = str_replace(IA_ROOT, "", $d['path']);
            }

            $data = array_merge($data, $getDataItem);
        }

        # 3.写入对比日志
        file_put_contents($framesPath . "/upgrade.log", json_encode($data));
        return true;
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
        $items = FrameVersionServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            FrameVersionServiceFacade::updateInfo(['deleted' => 1], ['id' => $item['id']]);
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

        $items = FrameVersionServiceFacade::getAll(['id' => $id]);
        foreach ($items as $item) {
            FrameVersionServiceFacade::updateInfo([$type => $value], ['id' => $item['id']]);
        }

        $this->success();
    }
}
