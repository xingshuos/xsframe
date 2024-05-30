<?php

namespace app\xs_cloud\controller\web;

use app\xs_cloud\facade\service\FrameLogServiceFacade;
use app\xs_cloud\facade\service\FrameVersionServiceFacade;
use xsframe\base\AdminBaseController;
use xsframe\util\FileUtil;
use xsframe\util\LoggerUtil;
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

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, ["create", "update"])) {
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

    public function log()
    {
        $keyword = trim($this->params['keyword']);
        $searchTime = trim($this->params["searchtime"]);

        $startTime = strtotime("-1 month");
        $endTime = time();

        $condition = " deleted=0 ";
        $params = [];

        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, ["create", "update"])) {
            $startTime = strtotime($this->params["time"]["start"]);
            $endTime = strtotime($this->params["time"]["end"]);
            $condition .= " and `" . $searchTime . "time" . "` between " . $startTime . " and " . $endTime;
        }
        if (!empty($keyword)) {
            $condition .= " and ( version like :version or host_url = :host_url or host_ip = :host_ip or php_version = :php_version ) ";
            $params['version'] = $keyword . "%";
            $params['host_url'] = $keyword;
            $params['host_ip'] = $keyword;
            $params['php_version'] = $keyword;
        }

        $list = FrameLogServiceFacade::getList([$condition, $params], "*", "id desc");
        $total = FrameLogServiceFacade::getTotal([$condition, $params]);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $result = [
            'list'      => $list,
            'pager'     => $pager,
            'total'     => $total,
            'starttime' => $startTime,
            'endtime'   => $endTime,
        ];

        return $this->template('log', $result);
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
            $data = [
                'version'    => trim($this->params['version']),
                'title'      => trim($this->params['title']),
                'content'    => htmlspecialchars_decode($this->params['content']),
                'status'     => intval($this->params['status']),
                'createtime' => time(),
                'updatetime' => time(),
            ];

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

            show_json(1, ["url" => webUrl("frames/edit", ["id" => $id, "tab" => str_replace("#tab_", "", $this->params["tab"])])]);
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
        # 更新系统代码不必考虑是否成功
        $command = "cd " . IA_ROOT . " && chown www:www * && chmod -R 777 * && git checkout . && git pull && git fetch origin && git reset --hard origin/master && git pull ";
        $resultMsg = @shell_exec($command);
        if (!empty($resultMsg) && !StringUtil::strexists($resultMsg, 'Already up-to-date')) {
            LoggerUtil::error($resultMsg);
        }

        # 1.创建目录
        $framesPath = IA_ROOT . "/storage/releases/frames/{$version}";
        FileUtil::mkDirs($framesPath);

        # 2.同步主目录
        $mainDirectory = [
            IA_ROOT . '/app/admin',
            IA_ROOT . '/config',
            IA_ROOT . '/extend',
            IA_ROOT . '/public/app/admin',
            IA_ROOT . '/vendor',
            IA_ROOT . '/public/index.php',
            IA_ROOT . '/public/router.php',
        ];

        # 2-2.非同步文件
        $unSyncFiles = [
            IA_ROOT . '/config/appMap.php',
            IA_ROOT . '/app/admin/view/sysset/static.html',
        ];

        # 3.设置同步文件的类型
        $syncTypes = ['php', 'html', 'js', 'xml', 'css', 'png', 'jpg', 'jpeg', 'gif'];

        $data = [];
        foreach ($mainDirectory as $item) {
            FileUtil::oldDirToNewDir($item, $framesPath . str_replace(IA_ROOT, "", $item), '', $unSyncFiles);
            $getDataItem = FileUtil::getDir($item, $syncTypes);

            foreach ($getDataItem as $i => $d) {
                if (!in_array($d['path'], $unSyncFiles)) {
                    $getDataItem[$i]['path'] = str_replace(IA_ROOT, "", $d['path']);
                } else {
                    unset($getDataItem[$i]);
                }
            }

            $data = array_merge($data, $getDataItem);
        }

        # 3.写入对比日志
        file_put_contents($framesPath . "/upgrade.log", json_encode($data));
        @chmod($framesPath . "/upgrade.log", 0777);

        return true;
    }

    public function change()
    {
        $table = $this->params["table"] ?? '';
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, ["message" => "参数错误"]);
        }

        $type = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        if (empty($table)) {
            $items = FrameVersionServiceFacade::getAll(['id' => $id]);
            foreach ($items as $item) {
                FrameVersionServiceFacade::updateInfo([$type => $value], ['id' => $item['id']]);
            }
        } else {
            if ($table == 'log') {
                $items = FrameLogServiceFacade::getAll(['id' => $id]);
                foreach ($items as $item) {
                    FrameLogServiceFacade::updateInfo([$type => $value], ['id' => $item['id']]);
                }
            }
        }


        $this->success();
    }
}
