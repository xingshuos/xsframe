<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use xsframe\base\AdminBaseController;
use xsframe\enum\UserRoleKeyEnum;
use xsframe\wrapper\FileWrapper;
use xsframe\util\ErrorUtil;
use xsframe\util\FileUtil;
use xsframe\util\RequestUtil;
use think\App;
use think\facade\Db;

class File extends AdminBaseController
{
    private $fileController;
    private $curUniacid;
    private $curModule;

    public function _admin_initialize()
    {
        parent::_admin_initialize();

        $this->curUniacid = $this->params['uniacid'] ?? $this->uniacid;
        $this->curModule = $this->params['module'] ?? $this->module;
    }

    // 上传
    public function upload()
    {
        $type = $this->params['upload_type'];
        $type = in_array($type, ['image', 'audio', 'video']) ? $type : 'image';

        $attachmentPath = IA_ROOT . "/public/attachment/";

        $file = request()->file('file');

        if (empty($file)) {
            $result['message'] = '请选择上传文件';
            die(json_encode($result));
        }

        $folder = $this->getFolder($type);
        $originName = $file->getOriginalName();
        $ext = strtolower($file->extension());

        if (($type == 'image' && !in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) || ($type == 'audio' && !in_array($ext, ['mp3'])) || ($type == 'video' && !in_array($ext, ['mp4']))) {
            $result['message'] = '文件格式错误' . $ext;
            die(json_encode($result));
        }

        $filename = FileUtil::fileRandomName($attachmentPath . $folder, $ext);

        # 上传本地
        $fileInfo = $file->move($attachmentPath . $folder, $filename);

        if (!$fileInfo) {
            $result['message'] = '本地文件上传失败';
            die(json_encode($result));
        }

        $this->fileController = new FileWrapper();

        $result = $this->fileController->fileUpload($this->curUniacid, $this->curModule, $this->userId, $type, $folder, $originName, $filename, $ext);
        if (ErrorUtil::isError($result)) {
            $result['message'] = $result['msg'];
            die(json_encode($result));
        }
        die(json_encode($result));
    }

    // 获取分组
    public function group_list()
    {
        $uniacid = $this->curUniacid;
        $type = $this->params['type'] ?? 0;
        $clientName = $this->params['client'] ?? 'web';

        $list = Db::name('sys_attachment_group')->where(['uniacid' => $uniacid, 'type' => $type,'client_name' => $clientName])->order("displayorder desc,id desc")->select();
        $list = $list->toArray();

        $this->returnData($list);
    }

    // 添加分组
    public function add_group()
    {
        $uniacid = $this->curUniacid;
        $type = $this->params['type'] ?? 0;
        $clientName = $this->params['client'] ?? 'web';

        $data = ([
            'uid'         => $this->userId,
            'uniacid'     => $uniacid,
            'name'        => trim($this->params['name'] ?? ''),
            'type'        => $type,
            'client_name' => $clientName,
        ]);

        $groupId = Db::name('sys_attachment_group')->insertGetId($data);

        $this->returnData(['id' => $groupId]);
    }

    // 更新分组
    public function change_group()
    {
        $name = trim($this->params['name'] ?? '');
        $id = intval($this->params['id'] ?? 0);
        $clientName = $this->params['client'] ?? 'web';

        if (!empty($name)) {
            Db::name('sys_attachment_group')->where(['id' => $id, 'uniacid' => $this->curUniacid, 'client_name' => $clientName])->update(['name' => $name]);
        }

        $this->returnData("更新成功");
    }

    // 删除分组
    public function del_group()
    {
        $id = intval($this->params['id'] ?? 0);

        if (!empty($id)) {
            Db::name('sys_attachment_group')->where(['id' => $id, 'uniacid' => $this->curUniacid])->delete();
        }

        $this->returnData("删除成功");
    }

    // 更新文件分组
    public function move_to_group()
    {
        $group_id = intval($this->params['id'] ?? 0);
        $ids = $this->params['keys'] ?? '';
        $ids = safe_gpc_array($ids);

        Db::name('sys_attachment')->where(['id' => $ids])->update(['group_id' => $group_id]);
        $this->returnData("更新成功");
    }

    // 获取音频列表
    public function voice()
    {
        $uniacid = $this->curUniacid;
        $condition = ['uniacid' => $uniacid, 'type' => 3];

        $page = max(1, $this->params['page'] ?? 1);
        $pageSize = 20;

        $fields = "s.*,s.fileurl attachment";
        $list = Db::name('sys_attachment')->alias("s")->field($fields)->where($condition)->order("id desc")->page($this->pIndex, $pageSize)->select();
        $list = $list->toArray();

        foreach ($list as &$item) {
            $item['url'] = tomedia($item['attachment']);
        }

        $total = Db::name('sys_attachment')->where($condition)->count();

        $pager = pagination2($total, $page, $pageSize, '', $context = ['before' => 5, 'after' => 4, 'isajax' => $this->request->isAjax()]);

        $result = ['items' => $list, 'pager' => $pager];
        $this->returnData($result);
    }

    // 获取视频列表
    public function video()
    {
        $uniacid = $this->curUniacid;
        $condition = ['uniacid' => $uniacid, 'type' => 2];

        $page = intval($this->params['page'] ?? 1);
        $page = max(1, $page);
        $pageSize = 20;

        $fields = "s.*,s.fileurl attachment";
        $list = Db::name('sys_attachment')->alias("s")->field($fields)->where($condition)->order("id desc")->page($this->pIndex, $pageSize)->select();
        $list = $list->toArray();

        foreach ($list as &$item) {
            $item['url'] = tomedia($item['attachment']);
        }

        $total = Db::name('sys_attachment')->where($condition)->count();

        $pager = pagination2($total, $page, $pageSize, '', $context = ['before' => 5, 'after' => 4, 'isajax' => $this->request->isAjax()]);

        $result = ['items' => $list, 'pager' => $pager];
        $this->returnData($result);
    }


    // 获取图片列表
    public function image()
    {
        $uniacid = $this->curUniacid;
        $isLocal = $this->params['local'] == 'local';

        $year = $this->params['year'] ?? '';
        $month = $this->params['month'] ?? '';
        $page = intval($this->params['page'] ?? 1);
        $groupId = intval($this->params['groupid'] ?? 0);
        $module = $this->params['module'] ?? '';
        $clientName = $this->params['client'] ?? 'web';

        $pageSize = 20;
        $page = max(1, $page);

        $condition = ['uniacid' => $uniacid, 'type' => 1];

        if (empty($uniacid)) {
            $condition['uid'] = $this->userId;
        }

        if ($groupId >= 0) {
            $condition['group_id'] = $groupId;
        }

        if (!empty($module)) {
            $condition['module'] = $module;
        }

        if (!empty($clientName)) {
            $condition['client_name'] = $clientName;
            if ($clientName != 'web') {
                $condition['uid'] = $this->userId;
            }
        }

        if ($year && $month) {
            $start_time = strtotime("{$year}-{$month}-01");
            $end_time = strtotime('+1 month', $start_time);

            $condition['createtime'] = Db::raw("between {$start_time} and {$end_time} ");
        }

        $fields = "s.*,s.fileurl attachment";
        $list = Db::name('sys_attachment')->alias("s")->field($fields)->where($condition)->order("id desc")->page($this->pIndex, $pageSize)->select();
        $list = $list->toArray();

        foreach ($list as &$item) {
            $item['url'] = tomedia($item['attachment']);
        }

        $total = Db::name('sys_attachment')->where($condition)->count();

        if (!empty($list)) {
            foreach ($list as &$meterial) {
                if ($isLocal) {
                    // 增加缩略图 zhaoxin 2021-12-14
                    $meterial['url'] = tomedia($meterial['fileurl'], null, $uniacid);
                    // $meterial['small_url'] = tomedia($meterial['fileurl'], "?x-oss-process=image/resize,w_200,m_lfit", $uniacid);
                    $meterial['small_url'] = tomedia($meterial['fileurl'], null, $uniacid);
                    unset($meterial['uid']);
                } else {
                    $meterial['attach'] = tomedia($meterial['fileurl'], null, $uniacid);
                    $meterial['url'] = $meterial['attach'];
                }
            }
        }

        $pager = pagination2($total, $page, $pageSize, '', $context = ['before' => 5, 'after' => 4, 'isajax' => $this->request->isAjax()]);

        $result = [
            'items' => $list,
            'pager' => $pager,
        ];
        $this->returnData($result);
    }

    // 提取 目前仅支持图片
    public function fetch()
    {
        $url = trim($this->params['url']);
        $resp = RequestUtil::httpGet($url);

        $type = 'image';

        $ext = substr($url, strrpos($url, '.') + 1);

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $result['message'] = '提取资源失败, 仅支持图片提取（检查地址后缀是否正确）！';
            die(json_encode($result));
        }

        $folder = $this->getFolder($type);
        $attachmentPath = IA_ROOT . "/public/attachment/";
        $originName = pathinfo($url, PATHINFO_BASENAME);

        $filename = FileUtil::fileRandomName($attachmentPath . $folder, $ext);
        $fullName = $attachmentPath . $folder . $filename;

        if (file_put_contents($fullName, $resp) == false) {
            $result['message'] = '提取失败,文件权限不足！';
            die(json_encode($result));
        }

        $this->fileController = new FileWrapper();

        $result = $this->fileController->fileUpload($this->curUniacid, $this->curModule, $this->userId, $type, $folder, $originName, $filename, $ext);
        if (ErrorUtil::isError($result)) {
            $result['message'] = $result['msg'];
            die(json_encode($result));
        }
        die(json_encode($result));
    }

    // 记录
    public function browser()
    {
        $uniacid = $this->curUniacid;
        $type = strval($this->params['type']);
        $path = strval($this->params['path']);

        switch ($type) {
            case 'image':
                $type = 1;
                break;
            case 'video':
                $type = 2;
                break;
            case 'audio':
                $type = 3;
                break;
        }

        $where = [
            'uniacid' => $uniacid,
            'type'    => $type
        ];

        $list = Db::name('sys_attachment')->where($where)->order("id desc")->page($this->pIndex, 20)->select();
        $list = $list->toArray();

        foreach ($list as &$row) {
            $row['url'] = tomedia($row['fileurl']);
        }

        $result = [
            'list'      => $list,
            'canDelete' => true, // 是否可以删除 0否 1是
        ];
        die(json_encode($result));
    }

    // 删除文件
    public function delete()
    {
        if (!empty($this->params['material_id'])) {
            $id = intval($this->params['material_id'] ?? '');
        } else {
            $id = $this->params['id'] ?? 0;
        }

        if (!is_array($id)) {
            $id = [intval($id)];
        }
        $id = safe_gpc_array($id);

        $role = $this->adminSession['role'] ?? '';
        if ($role != UserRoleKeyEnum::OWNER_KEY && $role != UserRoleKeyEnum::MANAGER_KEY) {
            $this->returnData("您没有权限删除文件", 1);
        }

        $list = Db::name('sys_attachment')->where(['id' => $id, 'uniacid' => $this->curUniacid])->select()->toArray();

        $this->fileController = new FileWrapper();
        foreach ($list as $item) {
            $file = strval($item['fileurl']);
            $this->fileController->fileDelete($this->curUniacid, $this->curModule, $this->userId, $file);
        }

        $this->returnData("删除成功");
    }

    // 提取网络图片
    public function networktowechat()
    {
        $url = $this->params['url'];
        $type = $this->params['type'];

        if (!in_array($type, ['image', 'video'])) {
            $type = 'image';
        }

        $url_host = parse_url($url, PHP_URL_HOST);
        $is_ip = preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $url_host);
        if ($is_ip) {
            $this->returnData('网络链接不支持IP地址！', 1);
        }

        $ext = substr($url, strrpos($url, '.') + 1);

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $this->returnData('提取资源失败, 仅支持图片提取（检查地址后缀是否正确）！', 1);
        }

        $folder = $this->getFolder($type);
        $attachmentPath = IA_ROOT . "/public/attachment/";
        $originName = pathinfo($url, PATHINFO_BASENAME);

        $filename = FileUtil::fileRandomName($attachmentPath . $folder, $ext);
        $fullName = $attachmentPath . $folder . $filename;

        $resp = RequestUtil::httpGet($url);
        if (file_put_contents($fullName, $resp) == false) {
            $this->returnData('提取失败,文件权限不足！', 1);
        }

        $this->fileController = new FileWrapper();

        $result = $this->fileController->fileUpload($this->curUniacid, $this->curModule, $this->userId, $type, $folder, $originName, $filename, $ext);
        if (ErrorUtil::isError($result)) {
            $this->returnData($result['msg'], 1);
        }
        $this->returnData($result);
    }

    // 获取目录
    private function getFolder($type)
    {
        $folder = "{$type}s/";

        $getModule = $this->params['module'] ?? '';

        if (!empty($this->isSystem) && (empty($getModule) || $getModule == 'admin')) {
            $folder .= "global/";
        } else {
            $folder .= "{$this->curUniacid}/{$getModule}/";
        }
        $folder .= date('Y/m/');
        return $folder;
    }

    // 数据返回
    private function returnData($data = [], $errno = 0)
    {
        $vars = [];

        $vars['message'] = [
            'errno'   => $errno,
            'message' => $data,
        ];
        $vars['redirect'] = "";
        $vars['type'] = 'ajax';

        exit(json_encode($vars));
    }
}