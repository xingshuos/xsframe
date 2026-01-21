<?php

namespace xsframe\traits;

use think\facade\Db;
use xsframe\facade\service\DbServiceFacade;
use xsframe\util\ExcelUtil;
use xsframe\util\StringUtil;

trait AdminTraits
{
    protected $tableName = ''; // 表名
    protected $fieldList = []; // 当前表字段
    protected $condition = []; // 查询条件
    protected $orderBy = ""; // 列表排序
    protected $result = []; // 可以自定义返回多个值到前端页面
    protected $backUrl = null; // post提交后返回的url
    protected $backData = []; // post提交后返回的数据
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
        // 权限检查 - 查看列表
        if (!$this->checkPermission('main')) {
            return $this->errorMsg('您没有权限查看此页面', 403);
        }

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

            $this->beforeMainResult();

            if ($export) {

                // 导出权限检查
                if (!$this->checkPermission('export')) {
                    return $this->errorMsg('您没有权限导出数据', 403);
                }

                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->select()->toArray();
            } else {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($this->orderBy)->page($this->pIndex, $this->pSize)->select()->toArray();
            }

            // 导出支持简单导出列表功能，复杂导出可以自行实现 exportExcelData
            foreach ($list as &$item) {
                $item = $this->listItemFormat($item);
            }
            unset($item);

            $total = Db::name($this->tableName)->where($condition)->count();
            $pager = pagination2($total, $this->pIndex, $this->pSize);

            $this->result['list'] = $list;
            $this->result['pager'] = $pager;
            $this->result['total'] = $total;
            $this->result['starttime'] = $startTime;
            $this->result['endtime'] = $endTime;
        }

        $this->afterMainResult($this->result);

        if ($export) {
            $this->exportExcelData($this->result['list'], $exportColumns, $exportKeys, $exportTitle);
        }

        return $this->template($this->template ?: 'list', $this->result);
    }

    // 设置列表页查询条件
    public function setMainCondition(&$condition)
    {
    }

    // 列表返回以前执行
    public function beforeMainResult()
    {
    }

    // 列表返回以后执行
    public function afterMainResult(&$result)
    {
    }

    // 列表页导出Excel
    public function exportExcelData($list = [], $column = null, $keys = null, $title = null, $last = null)
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

    // 编辑数据
    public function edit()
    {
        // 权限检查 - 编辑
        if (!$this->checkPermission('edit')) {
            return $this->errorMsg('您没有权限编辑数据', 403);
        }

        return $this->post();
    }

    // 添加数据
    public function add()
    {
        // 权限检查 - 添加
        if (!$this->checkPermission('add')) {
            return $this->errorMsg('您没有权限添加数据', 403);
        }

        return $this->post();
    }

    // 更新数据
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
                    $beforeData = Db::name($this->tableName)->where(['id' => $id])->find();
                    Db::name($this->tableName)->where(['id' => $id])->update($updateData);

                    // 记录编辑日志
                    $this->recordLog('edit', [
                        'table'    => $this->tableName,
                        'id'       => $id,
                        'before'   => $beforeData,
                        'after'    => $updateData,
                        'user_id'  => $this->userId,
                        'username' => $this->adminSession['username'] ?? ''
                    ]);
                } else {
                    $id = Db::name($this->tableName)->insertGetId($updateData);

                    // 记录添加日志
                    $this->recordLog('add', [
                        'table'    => $this->tableName,
                        'id'       => $id,
                        'data'     => $updateData,
                        'user_id'  => $this->userId,
                        'username' => $this->adminSession['username'] ?? ''
                    ]);
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
                        if (!empty($backUrl) && !StringUtil::strexists($backUrl, "http") && !StringUtil::strexists($backUrl, "web.")) {
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
                    $this->backData['url'] = $backUrl;
                    $this->success($this->backData);
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

    // 渲染视图页参数
    public function afterPostResult(&$result)
    {
    }

    // 改变字段数据
    public function change()
    {
        // 权限检查 - 修改
        if (!$this->checkPermission('edit')) {
            return $this->errorMsg('您没有权限修改数据', 403);
        }

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
                $this->beforeChangeData($item);
                $beforeValue = $item[$type] ?? '';
                Db::name($this->tableName)->where("id", '=', $item['id'])->update([$type => $value]);
                $this->afterChangeData($item);

                // 记录修改字段日志
                $this->recordLog('change_field', [
                    'table'    => $this->tableName,
                    'id'       => $item['id'],
                    'field'    => $type,
                    'before'   => $beforeValue,
                    'after'    => $value,
                    'user_id'  => $this->userId,
                    'username' => $this->adminSession['username'] ?? ''
                ]);
            }
        }

        $this->success();
    }

    // 修改数据之前处理
    public function beforeChangeData(&$item)
    {
    }

    // 修改数据之后处理
    public function afterChangeData(&$item)
    {
    }

    // 删除数据
    public function delete()
    {
        // 权限检查 - 删除
        if (!$this->checkPermission('delete')) {
            return $this->errorMsg('您没有权限删除数据', 403);
        }

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
                if ($type == 'tinyint' || $type == 'int') {
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

                $this->beforeDeleteData($item);

                // 记录删除前的数据
                $deletedData = $item;

                Db::name($this->tableName)->where(['uniacid' => $this->uniacid, "id" => $item['id']])->update($updateData);

                $this->afterDeleteData($item);

                // 记录删除日志
                $this->recordLog('delete', [
                    'table'    => $this->tableName,
                    'id'       => $item['id'],
                    'data'     => $deletedData,
                    'user_id'  => $this->userId,
                    'username' => $this->adminSession['username'] ?? ''
                ]);
            }
        }
        $this->success(["url" => referer()]);
    }

    // 删除数据之前处理
    public function beforeDeleteData(&$item)
    {
    }

    // 删除数据之后处理
    public function afterDeleteData(&$item)
    {
    }

    // 更新数据
    public function update()
    {
        // 权限检查 - 编辑
        if (!$this->checkPermission('edit')) {
            return $this->errorMsg('您没有权限更新数据', 403);
        }

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
                    $beforeData = $item;
                    Db::name($this->tableName)->where(['uniacid' => $this->uniacid, "id" => $item['id']])->update($updateData);

                    // 记录批量更新日志
                    $this->recordLog('batch_update', [
                        'table'    => $this->tableName,
                        'id'       => $item['id'],
                        'before'   => $beforeData,
                        'after'    => $updateData,
                        'user_id'  => $this->userId,
                        'username' => $this->adminSession['username'] ?? ''
                    ]);
                }
            }
        }
        $this->success(["url" => referer()]);
    }

    // 真实删除
    public function destroy()
    {
        // 权限检查 - 删除（真实删除需要更高权限）
        if (!$this->checkPermission('delete') || !$this->checkPermission('force_delete')) {
            return $this->errorMsg('您没有权限永久删除数据', 403);
        }

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

                // 记录真实删除前的数据
                $deletedData = $item;

                Db::name($this->tableName)->where(["id" => $item['id']])->delete();

                // 记录真实删除日志
                $this->recordLog('force_delete', [
                    'table'    => $this->tableName,
                    'id'       => $item['id'],
                    'data'     => $deletedData,
                    'user_id'  => $this->userId,
                    'username' => $this->adminSession['username'] ?? ''
                ]);
            }
        }
        $this->success(["url" => referer()]);
    }

    // 还原数据
    public function restore()
    {
        // 权限检查 - 还原
        if (!$this->checkPermission('restore')) {
            return $this->errorMsg('您没有权限还原数据', 403);
        }

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

                // 记录还原日志
                $this->recordLog('restore', [
                    'table'    => $this->tableName,
                    'id'       => $item['id'],
                    'user_id'  => $this->userId,
                    'username' => $this->adminSession['username'] ?? ''
                ]);

            }
        }
        $this->success(["url" => referer()]);
    }

    // 回收站
    public function recycle()
    {
        // 权限检查 - 查看回收站
        if (!$this->checkPermission('recycle')) {
            return $this->errorMsg('您没有权限查看回收站', 403);
        }

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
        // 权限检查 - 访问入口
        if (!$this->checkPermission('cover')) {
            return $this->errorMsg('您没有权限访问此入口', 403);
        }

        $moduleName = realModuleName($this->module);
        $coverUrl = $this->siteRoot . "/{$moduleName}.html?i=" . $this->uniacid;
        $mobileUrl = $this->siteRoot . "/{$moduleName}/mobile.html?i=" . $this->uniacid;
        $pcUrl = $this->siteRoot . "/{$moduleName}/pc.html?i=" . $this->uniacid;
        return $this->template('cover', ['coverUrl' => $coverUrl, 'mobileUrl' => $mobileUrl, 'pcUrl' => $pcUrl]);
    }

    // 设置项目应用配置信息
    public function moduleSettings()
    {
        // 权限检查 - 模块设置
        if (!$this->checkPermission('module_settings')) {
            return $this->errorMsg('您没有权限进行模块设置', 403);
        }

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

                // 记录模块设置日志
                $this->recordLog('module_settings', [
                    'module'   => $this->module,
                    'uniacid'  => $this->uniacid,
                    'settings' => $settingsData,
                    'user_id'  => $this->userId,
                    'username' => $this->adminSession['username'] ?? ''
                ]);
            }

            $moduleSettings = $settingsData;
        }
        return $moduleSettings;
    }

    // 当前项目应用配置信息
    public function module()
    {
        // 权限检查 - 模块配置
        if (!$this->checkPermission('module_config')) {
            return $this->errorMsg('您没有权限配置模块', 403);
        }

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

    // ============ 新增的日志记录和权限检查方法 ============

    /**
     * 记录操作日志
     * @param string $action 操作类型：add, edit, delete, change_field, etc.
     * @param array $data 相关数据
     * @return bool
     */
    protected function recordLog($action, $data = [])
    {
        try {
            // 操作类型映射
            $actionMap = [
                'add'             => '添加',
                'edit'            => '编辑',
                'delete'          => '删除',
                'force_delete'    => '永久删除',
                'restore'         => '还原',
                'change_field'    => '修改字段',
                'batch_update'    => '批量更新',
                'module_settings' => '模块设置',
                'export'          => '导出'
            ];

            $actionName = $actionMap[$action] ?? $action;
            $tableName = $data['table'] ?? $this->tableName ?? 'unknown';
            $recordId = $data['id'] ?? 0;

            // 构建日志数据
            $logData = [
                'uniacid'     => $this->uniacid,
                'user_id'     => $data['user_id'] ?? $this->userId ?? 0,
                'username'    => $data['username'] ?? $this->adminSession['username'] ?? '',
                'path'        => $this->request->pathinfo(),
                'page_name'   => $actionName . ' - ' . $tableName . ($recordId ? ' (ID: ' . $recordId . ')' : ''),
                'module'      => $this->module,
                'ip'          => $this->request->ip(),
                'create_time' => time()
            ];

            // 写入日志表
            Db::name('sys_log')->insert($logData);

            return true;
        } catch (\Exception $e) {
            // 记录日志失败时不要影响主流程
            error_log('记录操作日志失败: ' . $e->getMessage());

            # 验证日志表是否存在
            if (!DbServiceFacade::hasTable('sys_log')) {
                try {
                    DbServiceFacade::execute("
                        CREATE TABLE " . tablename('sys_log') . " (
                          `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
                          `uniacid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商户id',
                          `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工id',
                          `username` varchar(64) NOT NULL DEFAULT '' COMMENT '操作账号',
                          `path` varchar(128) NOT NULL DEFAULT '' COMMENT '操作连接',
                          `page_name` varchar(64) NOT NULL DEFAULT '' COMMENT '页面名称',
                          `module` varchar(50) NOT NULL DEFAULT '' COMMENT '模块标识',
                          `ip` varchar(16) NOT NULL DEFAULT '' COMMENT '登录IP',
                          `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
                          PRIMARY KEY (`id`) USING BTREE,
                          KEY `user_id` (`user_id`) USING BTREE,
                          KEY `create_time` (`create_time`) USING BTREE
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='员工操作记录表';
                    ");
                    $this->recordLog($action, $data);
                } catch (\Exception $e) {
                }
            }

            return false;
        }
    }

    /**
     * 检查权限
     * @param string $permission 权限标识
     * @return bool
     */
    protected function checkPermission($permission)
    {
        try {
            // 如果用户是超级管理员（owner），拥有所有权限
            if (!empty($this->adminSession['role'])) {
                if ($this->adminSession['role'] === 'owner') {
                    return true;
                }

                if ($this->adminSession['role'] === 'manager') {
                    return true;
                }
            }

            // 获取当前用户的权限列表
            $userPermissions = $this->getUserPermissions();

            if (empty($userPermissions)) {
                return false;
            }

            // 检查是否有应用权限
            if (in_array($this->module, $userPermissions['app_perms'])) {
                return true;
            }

            $permission = $this->module . "." . $this->controller . "." . $permission;

            // 检查是否有操作权限
            $hasPermission = in_array($permission, $userPermissions['perms']);
            return $hasPermission;
        } catch (\Exception $e) {
            // 权限检查失败时默认返回无权限
            error_log('权限检查失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取当前用户权限列表
     * @return array
     */
    protected function getUserPermissions()
    {
        // 从缓存或数据库中获取用户权限
        $userId = $this->userId ?? 0;
        $uniacid = $this->uniacid ?? 0;

        if (!$userId || !$uniacid) {
            return [];
        }

        try {
            $operator = Db::name('sys_account_perm_user')->field('perms,app_perms')->where(['uid' => $userId, 'uniacid' => $uniacid])->find();
            $operatorPerms = (array)explode(',', $operator['perms']);
            $operatorAppPerms = (array)explode(',', $operator['app_perms']);

            $permissions = [
                'perms'     => $operatorPerms,
                'app_perms' => $operatorAppPerms,
            ];

            return $permissions ?: [];
        } catch (\Exception $e) {
            error_log('获取用户权限失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取权限操作映射
     * @return array
     */
    protected function getPermissionActions()
    {
        if (empty($this->permissionActions)) {
            // 默认的权限操作映射
            $this->permissionActions = [
                'main'           => 'main',           // 查看列表
                'index'          => 'main',          // 查看列表
                'detail'         => 'detail',          // 查看详情
                'add'            => 'add',             // 添加
                'edit'           => 'edit',           // 编辑
                'post'           => ['add', 'edit'],  // 添加/编辑
                'change'         => 'edit',         // 修改字段
                'delete'         => 'delete',       // 删除
                'destroy'        => 'force_delete', // 永久删除
                'restore'        => 'restore',     // 还原
                'recycle'        => 'recycle',     // 回收站
                'cover'          => 'cover',         // 访问入口
                'module'         => 'module_config', // 模块配置
                'moduleSettings' => 'module_settings', // 模块设置
                'export'         => 'export',       // 导出
                'update'         => 'edit',         // 批量更新
            ];
        }

        return $this->permissionActions;
    }

    /**
     * 在控制器初始化时检查权限（可以在AdminBaseController中调用）
     */
    protected function checkActionPermission()
    {
        $action = $this->action;
        $permissionActions = $this->getPermissionActions();

        if (isset($permissionActions[$action])) {
            $permissions = (array)$permissionActions[$action];
            foreach ($permissions as $permission) {
                if (!$this->checkPermission($permission)) {
                    if ($this->request->isAjax()) {
                        $this->errorMsg('您没有权限执行此操作', 403);
                    } else {
                        exit('权限不足');
                    }
                }
            }
        }
    }

}
