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
    protected $deleteField = "deleted"; // 软删除字段
    protected $template = null; // 设置模板名称
    protected $pageSize = 10; // 分页显示数量

    public function index()
    {
        return $this->main();
    }

    public function main()
    {
        if ($this->pSize != $this->pageSize) {
            $this->pSize = $this->pageSize;
        }
        if (!empty($this->tableName)) {
            $fieldList = $this->getFiledList();

            $keyword = trim($this->params['keyword']) ?? '';
            $kwFields = trim($this->params['kwFields']) ?? '';
            $field = trim($this->params['field']) ?? '';
            $status = trim($this->params['status']) ?? '';
            $enabled = trim($this->params['enabled']) ?? 0;
            $searchTime = trim($this->params["searchtime"]) ?? '';
            $sort = trim($this->params["sort"] ?? '');
            $order = trim($this->params["order"] ?? '');

            $export = trim($this->params['export']);
            $exportTitle = trim($this->params['export_title']);
            $exportColumns = trim($this->params['export_columns']);
            $exportKeys = trim($this->params['export_keys']);

            $startTime = strtotime("-1 month");
            $endTime = time();

            $condition = (array)$this->condition;
            $condition['uniacid'] = $this->uniacid;

            if (array_key_exists($this->deleteField, $fieldList)) {
                $condition[] = Db::raw($this->deleteField . " is null or " . $this->deleteField . " = '' or " . $this->deleteField . " = '0' ");
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

            if (is_array($this->params["time"])) {
                $startTime = strtotime($this->params["time"]["start"]);
                $endTime = strtotime($this->params["time"]["end"]);

                if (array_key_exists($searchTime . "time", $fieldList)) {
                    $condition[$searchTime . "time"] = Db::raw("between {$startTime} and {$endTime} ");
                } else {
                    if (array_key_exists($searchTime . "_time", $fieldList)) {
                        $condition[$searchTime . "_time"] = Db::raw("between {$startTime} and {$endTime} ");
                    }
                }
            }

            if (!empty($keyword) && !empty($kwFields)) {
                $field = $kwFields;
            }

            if (!empty($keyword) && !empty($field)) {
                $field = str_replace(" ", "|", $field);
                $field = str_replace("，", "|", $field);
                $field = str_replace(",", "|", $field);
                $condition[] = [$field, 'like', "%" . trim($keyword) . "%"];
            }

            foreach ($this->params as $field => $value) {
                if (array_key_exists($field, $fieldList) && (!empty($value) || is_numeric($value)) && !array_key_exists($field, $condition)) {
                    $condition[$field] = $value;
                }
            }
            unset($item);

            $this->setMainCondition($condition);

            $field = "*";

            if (empty($this->orderBy)) {
                if (array_key_exists('displayorder', $fieldList)) {
                    $this->orderBy = "displayorder desc, id desc";
                } else {
                    $this->orderBy = "id desc";
                }
            }

            if (!empty($sort) && !empty($order)) {
                $this->orderBy = "{$sort} {$order}";
            }

            if ($export) {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->select()->toArray();
            } else {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->page($this->pIndex, $this->pSize)->select()->toArray();
            }

            // 导出支持简单导出列表功能，复杂导出可以自行实现 exportExcelData
            foreach ($list as &$item) {
                $item = $this->listItemFormat($item);
            }
            unset($item);

            if ($export) {
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

        $this->afterMainResult($this->result);

        return $this->template($this->template ?: 'list', $this->result);
    }

    // 设置查询条件
    public function setMainCondition(&$condition)
    {
    }

    // 主题返回以后执行
    public function afterMainResult(&$result)
    {
    }

    // 导出列表
    private function exportExcelData($list = [], $column = null, $keys = null, $title = null, $last = null)
    {
        if (!empty($list)) {
            ini_set('memory_limit', '1024M'); // 根据需要调整内存大小
            set_time_limit(0); // 设置为0表示无限制，但注意服务器配置可能限制此设置

            $title = ($title ?? "数据列表") . "_" . date('YmdHi');
            if (!empty($column) && !empty($keys)) {
                $column = explode(",", $column);
                $keys = explode(",", $keys);
                $last = explode(",", $last);

                $setWidth = [];
                for ($i = 0; $i < count($column); $i++) {
                    $setWidth[$i] = 30;
                }

                ExcelUtil::export((string)$title, (array)$column, (array)$setWidth, (array)$list, (array)$keys, (array)$last, (string)$title);
            } else {
                $data = $this->setExportExcelData($list);
                if (!empty($data)) {
                    extract($data);

                    if (!empty($column) && !empty($keys)) {
                        ExcelUtil::export((string)$title, (array)$column, (array)$setWidth, (array)$list, (array)$keys, (array)$last, (string)$title);
                    }
                }
            }
        }
    }

    // 自定义导出数据格式
    public function setExportExcelData(&$list)
    {
        return [
            'list'     => $list,
            'column'   => [],
            'keys'     => [],
            'setWidth' => [],
            'last'     => [],
            'title'    => "",
        ];
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

                    if (!in_array($filed, ['uniacid', 'createtime', 'create_time', 'updatetime', 'update_time']) && !array_key_exists($filed, $this->params)) {
                        continue;
                    }

                    $updateData[$filed] = $this->params[$filed] ?? '';
                    if (!is_array($updateData[$filed])) {
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
                    }

                    if (empty($updateData[$filed])) {
                        switch ($filed) {
                            case 'uniacid':
                                $updateData[$filed] = $this->uniacid;
                                break;
                            case 'create_time':
                            case 'createtime':
                                if (empty($id)) {
                                    $updateData[$filed] = TIMESTAMP;
                                } else {
                                    unset($updateData[$filed]);
                                }
                                break;
                            case 'update_time':
                            case 'updatetime':
                                $updateData[$filed] = TIMESTAMP;
                                break;
                            case $this->deleteField:
                                $type = explode('(', $fieldItem['type'])[0];
                                if ($type == 'tinyint') {
                                    $updateData[$filed] = 0;
                                } else {
                                    $updateData[$filed] = TIMESTAMP;
                                }
                                break;
                        }
                    }
                }

                $this->beforeSetPostData($updateData);

                if (!empty($id)) {
                    Db::name($this->tableName)->where(['id' => $id])->update($updateData);
                } else {
                    $id = Db::name($this->tableName)->insertGetId($updateData);
                }

                $this->afterSetPostData($id);

                if ($this->params['isModel']) {
                    $this->success();
                } else {
                    if (empty($backUrl)) {
                        if (!empty($this->backUrl)) {
                            $backUrl = $this->backUrl;
                        } else {
                            if ($this->isBackMain) {
                                $backUrl = $this->request->controller() . "/main";
                            }
                        }
                        if (!empty($backUrl) && !StringUtil::strexists($backUrl, "web.")) {
                            $backUrl = "web." . $backUrl;
                        }
                    }

                    if (!empty($backUrl)) {
                        if (!StringUtil::strexists($backUrl, "http")) {
                            $backUrl = webUrl(rtrim($backUrl, ".html"));
                        }
                    } else {
                        $backUrl = referer();
                        $params = ['id' => $id, 'tab' => str_replace("#tab_", "", $this->params['tab'])];

                        $parsedUrl = parse_url($backUrl);
                        $query = $parsedUrl['query'];
                        parse_str($query, $queryParams);
                        $queryParams = array_merge($queryParams, $params);

                        $uniqueQueryParams = [];
                        foreach ($queryParams as $key => $value) {
                            $uniqueQueryParams[$key] = $value;
                        }
                        $newQuery = http_build_query($uniqueQueryParams);

                        $backUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '?' . $newQuery;
                    }
                    $this->success(["url" => $backUrl]);
                }
            }

            $field = "*";
            $condition = ['id' => $id];
            $item = Db::name($this->tableName)->field($field)->where($condition)->find();

            $this->result['item'] = $item;
        }

        $this->afterPostResult($this->result);
        return $this->template($this->template ?: 'post', $this->result);
    }

    // 保存数据之前处理
    public function beforeSetPostData(&$updateData)
    {
    }

    // 保存数据之后处理
    public function afterSetPostData($id)
    {
    }

    // 获取数据之后处理
    public function afterPostResult(&$result)
    {
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
            if (array_key_exists($this->deleteField, $fieldList)) {
                $updateData[$this->deleteField] = 1;

                $type = explode('(', $fieldList[$this->deleteField]['type'])[0];
                if ($type == 'tinyint') {
                    $updateData[$this->deleteField] = 1;
                } else {
                    $updateData[$this->deleteField] = TIMESTAMP;
                }

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

                $this->afterDeleteData($item);
            }
        }
        $this->success(["url" => referer()]);
    }

    public function afterDeleteData(&$item)
    {
    }

    public function update()
    {
        if (!empty($this->tableName)) {
            $id = intval($this->params["id"]);
            $updateData = $this->params["data"] ?? [];

            if (empty($id)) {
                $id = $this->params["ids"];
            }

            if (empty($id)) {
                $this->error("参数错误");
            }

            if (!empty($updateData)) {
                $items = Db::name($this->tableName)->where(['id' => $id])->select();
                foreach ($items as $item) {
                    Db::name($this->tableName)->where(['uniacid' => $this->uniacid, "id" => $item['id']])->update($updateData);
                }
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
            if (array_key_exists($this->deleteField, $fieldList)) {
                $updateData[$this->deleteField] = 0;
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
                'uniacid'          => $this->uniacid,
                $this->deleteField => 1,
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
    public function getFiledList($tableName = null): array
    {
        if (!empty($tableName)) {
            $this->fieldList = Db::name($tableName)->getFields();
        } else {
            if (!empty($this->tableName) && empty($this->fieldList)) {
                $this->fieldList = Db::name($this->tableName)->getFields();
            } else {
                $this->fieldList = [];
            }
        }
        return $this->fieldList;
    }

    // 自动格式化列表数据
    public function listItemFormat($item)
    {
        return $item;
    }
}
