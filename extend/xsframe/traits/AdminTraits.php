<?php

namespace xsframe\traits;

use think\facade\Db;
use xsframe\util\ExcelUtil;
use xsframe\util\StringUtil;

trait AdminTraits
{
    protected $tableName = ''; // 表名
    private $fieldList = []; // 当前表字段
    protected $condition = []; // 查询条件
    protected $orderBy = ""; // 列表排序
    protected $result = []; // 可以自定义返回多个值到前端页面
    protected $backUrl = null; // post提交后返回的url
    protected $isBackMain = true; // post提交后是否返回到列表页 默认返回到列表页

    public function index()
    {
        return $this->main();
    }

    public function main()
    {
        if (!empty($this->tableName)) {
            $fieldList = $this->getFiledList();

            $keyword = trim($this->params['keyword']) ?? '';
            $kwFields = trim($this->params['kwFields']) ?? '';
            $field = trim($this->params['field']) ?? '';
            $status = trim($this->params['status']) ?? '';
            $enabled = trim($this->params['enabled']) ?? 0;
            $searchTime = trim($this->params["searchtime"]) ?? '';

            $export = trim($this->params['export']);
            $exportTitle = trim($this->params['export_title']);
            $exportColumns = trim($this->params['export_columns']);
            $exportKeys = trim($this->params['export_keys']);

            $startTime = strtotime("-1 month");
            $endTime = time();

            $condition = (array)$this->condition;
            $condition['uniacid'] = $this->uniacid;

            if (array_key_exists('deleted', $fieldList)) {
                $condition['deleted'] = 0;
            }

            if (array_key_exists('is_deleted', $fieldList)) {
                $condition['is_deleted'] = 0;
            }

            if (is_numeric($status)) {
                $condition['status'] = $status;
            }

            if (is_numeric($enabled)) {
                $condition['enabled'] = $enabled;
            }

            if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, ["create"])) {
                $startTime = strtotime($this->params["time"]["start"]);
                $endTime = strtotime($this->params["time"]["end"]);

                $condition[$searchTime . "time"] = Db::raw("between {$startTime} and {$endTime} ");
            }

            if (!empty($keyword) && !empty($kwFields)) {
                $kwFields = str_replace(" ", "|", $kwFields);
                $kwFields = str_replace("，", "|", $kwFields);
                $kwFields = str_replace(",", "|", $kwFields);
                $condition[] = [$kwFields, 'like', "%" . trim($keyword) . "%"];
            }

            if (!empty($keyword) && !empty($field)) {
                $field = str_replace(" ", "|", $field);
                $field = str_replace("，", "|", $field);
                $field = str_replace(",", "|", $field);
                $condition[] = [$field, 'like', "%" . trim($keyword) . "%"];
            }

            $field = "*";

            if (empty($this->orderBy)) {
                if (array_key_exists('displayorder', $fieldList)) {
                    $this->orderBy = "displayorder desc, id desc";
                } else {
                    $this->orderBy = "id desc";
                }
            }

            if ($export) {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->select()->toArray();
            } else {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->page($this->pIndex, $this->pSize)->select()->toArray();
            }

            if ($export) {
                // 导出支持简单导出列表功能，复杂导出可以自行实现 exportExcelData
                foreach ($list as &$item) {
                    if (array_key_exists('createtime', $item)) {
                        $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
                    }
                    if (array_key_exists('create_time', $item)) {
                        $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                    }
                    if (array_key_exists('updatetime', $item)) {
                        $item['updatetime'] = date('Y-m-d H:i:s', $item['updatetime']);
                    }
                    if (array_key_exists('update_time', $item)) {
                        $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                    }
                    if (array_key_exists('finishtime', $item)) {
                        $item['finishtime'] = date('Y-m-d H:i:s', $item['finishtime']);
                    }
                    if (array_key_exists('finish_time', $item)) {
                        $item['finish_time'] = date('Y-m-d H:i:s', $item['finish_time']);
                    }
                    if (array_key_exists('canceltime', $item)) {
                        $item['canceltime'] = date('Y-m-d H:i:s', $item['canceltime']);
                    }
                    if (array_key_exists('cancel_time', $item)) {
                        $item['cancel_time'] = date('Y-m-d H:i:s', $item['cancel_time']);
                    }
                }
                unset($item);
                $this->exportExcelData($list, $exportColumns, $exportKeys, $exportTitle);
            }

            $total = Db::name($this->tableName)->where($condition)->count();
            $pager = pagination2($total, $this->pIndex, $this->pSize);

            $this->result['list'] = $list;
            $this->result['pager'] = $pager;
            $this->result['total'] = $total;
            $this->result['starttime'] = $startTime;
            $this->result['endtime'] = $endTime;
        }

        return $this->template('list', $this->result);
    }

    // 导出列表
    private function exportExcelData($list = [], $column = null, $keys = null, $title = null, $last = null)
    {
        if (!empty($list) && !empty($column) && !empty($keys)) {
            $title = ($title ?? "数据列表") . "_" . date('YmdHi');
            $column = explode(",", $column);
            $keys = explode(",", $keys);
            $last = explode(",", $last);

            $setWidth = [];
            for ($i = 0; $i < count($column); $i++) {
                $setWidth[$i] = 30;
            }

            $filename = $title;
            ExcelUtil::export($title, $column, $setWidth, $list, $keys, $last, $filename);
        }
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
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"] ?? 0);
            $backUrl = trim($this->params['backUrl'] ?? '');

            if ($this->request->isPost()) {
                $fieldList = $this->getFiledList();
                $updateData = [];
                foreach ($fieldList as $filed => $fieldItem) {
                    $updateData[$filed] = $this->params[$filed] ?? '';

                    switch ($fieldItem['type']) {
                        case 'text':
                            $updateData[$filed] = htmlspecialchars_decode($updateData[$filed]);
                            break;
                        case 'datetime':
                            $updateData[$filed] = strtotime($updateData[$filed]);
                            break;
                        case 'decimal':
                            $updateData[$filed] = floatval($updateData[$filed]);
                            break;
                        default:
                            $updateData[$filed] = trim($updateData[$filed]);
                    }

                    if (empty($updateData[$filed])) {
                        switch ($filed) {
                            case 'uniacid':
                                $updateData[$filed] = $this->uniacid;
                                break;
                            case 'create_time':
                            case 'createtime':
                                $updateData[$filed] = TIMESTAMP;
                                break;
                            case 'deleted':
                                $updateData[$filed] = 0;
                                break;
                        }
                    }
                }

                if (!empty($id)) {
                    Db::name($this->tableName)->where(['id' => $id])->update($updateData);
                } else {
                    $id = Db::name($this->tableName)->insertGetId($updateData);
                }
                if ($this->params['isModel']) {
                    $this->success();
                } else {
                    if (empty($backUrl)) {
                        if (!empty($this->backUrl)) {
                            $backUrl = $this->backUrl;
                        } else {
                            if ($this->isBackMain) {
                                $backUrl = $this->controller . "/main";
                            }
                        }
                        if (!StringUtil::strexists($backUrl, "web.")) {
                            $backUrl = "web." . $backUrl;
                        }
                    }

                    if (!empty($backUrl)) {
                        $this->success(["url" => webUrl(rtrim($backUrl, ".html"))]);
                    } else {
                        $this->success(["url" => webUrl("", ['id' => $id, 'tab' => str_replace("#tab_", "", $this->params['tab'])])]);
                    }

                }
            }

            $field = "*";
            $condition = ['id' => $id];
            $item = Db::name($this->tableName)->field($field)->where($condition)->find();

            $this->result['item'] = $item;
        }

        return $this->template('post', $this->result);
    }

    public function change()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $type = trim($this->params["type"]);
            $value = trim($this->params["value"]);

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                Db::name($this->tableName)->where("id", '=', $item['id'])->update([$type => $value]);
            }
        }

        $this->success();
    }

    public function delete()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $updateData = [];
            $fieldList = $this->getFiledList();
            if (array_key_exists('deleted', $fieldList)) {
                $updateData['deleted'] = 1;
            }
            if (array_key_exists('delete_time', $fieldList)) {
                $updateData['delete_time'] = TIMESTAMP;
            }
            if (array_key_exists('is_deleted', $fieldList)) {
                $updateData['is_deleted'] = 1;
            }

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                if (!empty($item['is_default'])) {
                    $this->error("默认项不能被删除");
                }
                Db::name($this->tableName)->where(['uniacid' => $this->uniacid, "id" => $item['id']])->update($updateData);
            }
        }
        $this->success(["url" => referer()]);
    }

    // 真实删除
    public function destroy()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $items = Db::name($this->tableName)->where(['uniacid' => $this->uniacid, 'id' => $id])->select();
            foreach ($items as $item) {
                if (!empty($item['is_default'])) {
                    $this->error("默认项不能被删除");
                }
                Db::name($this->tableName)->where(["id" => $item['id']])->delete();
            }
        }
        $this->success(["url" => referer()]);
    }

    // 还原数据
    public function restore()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            $updateData = [];
            $fieldList = $this->getFiledList();
            if (array_key_exists('deleted', $fieldList)) {
                $updateData['deleted'] = 0;
            }
            if (array_key_exists('is_deleted', $fieldList)) {
                $updateData['is_deleted'] = 0;
            }

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                Db::name($this->tableName)->where(["id" => $item['id']])->update($updateData);
            }
        }
        $this->success(["url" => referer()]);
    }

    // 回收站
    public function recycle()
    {
        if (!empty($this->tableName)) {
            $condition = [
                'uniacid' => $this->uniacid,
                'deleted' => 1,
            ];

            $field = "*";
            $order = "id desc";
            $list = Db::name($this->tableName)->field($field)->where($condition)->order($order)->page($this->pIndex, $this->pSize)->select()->toArray();
            $total = Db::name($this->tableName)->where($condition)->count();
            $pager = pagination2($total, $this->pIndex, $this->pSize);

            $this->result['list'] = $list;
            $this->result['pager'] = $pager;
            $this->result['total'] = $total;
        }

        return $this->template('recycle', $this->result);
    }

    // 访问入口
    public function cover()
    {
        $moduleName = realModuleName($this->module);
        $coverUrl = $this->siteRoot . "/{$moduleName}.html?i=" . $this->uniacid;
        $mobileUrl = $this->siteRoot . "/{$moduleName}/mobile.html?i=" . $this->uniacid;
        $pcUrl = $this->siteRoot . "/{$moduleName}/pc.html?i=" . $this->uniacid;
        return $this->template('cover', ['coverUrl' => $coverUrl, 'mobileUrl' => $mobileUrl, 'pcUrl' => $pcUrl]);
    }

    // 设置项目应用配置信息
    public function moduleSettings()
    {
        $moduleSettings = $this->settingsController->getModuleSettings(null, $this->module, $this->uniacid);
        if ($this->request->isPost()) {
            $settingsData = $this->params['data'] ?? [];

            if (!empty($settingsData['contact'])) {
                $settingsData['contact']['about'] = htmlspecialchars_decode($settingsData['contact']['about']);
            }
            if (!empty($settingsData['user'])) {
                $settingsData['user']['agreement'] = htmlspecialchars_decode($settingsData['user']['agreement']);
            }

            $settingsData = array_merge($moduleSettings, $settingsData);

            if (!empty($settingsData)) {
                $data['settings'] = serialize($settingsData);
                Db::name('sys_account_modules')->where(["uniacid" => $this->uniacid, 'module' => $this->module])->update($data);
                # 更新缓存
                $this->settingsController->reloadModuleSettings($this->module, $this->uniacid);
            }

            $moduleSettings = $settingsData;
        }
        return $moduleSettings;
    }

    // 当前项目应用配置信息
    public function module()
    {
        $moduleSettings = $this->moduleSettings();
        if ($this->request->isPost()) {
            $this->success(["url" => webUrl("sets/module", ['tab' => str_replace("#tab_", "", $this->params['tab'])])]);
        }

        $var = [
            'moduleSettings' => $moduleSettings
        ];
        return $this->template('module', $var);
    }

    // 获取字段列表
    private function getFiledList(): array
    {
        if (!empty($this->tableName) && empty($this->fieldList)) {
            $this->fieldList = Db::name($this->tableName)->getFields();
        } else {
            $this->fieldList = [];
        }
        return $this->fieldList;
    }
}
