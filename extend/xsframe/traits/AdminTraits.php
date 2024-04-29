<?php

namespace xsframe\traits;

use think\facade\Db;

trait AdminTraits
{
    protected $tableName = '';

    public function index()
    {
        return $this->main();
    }

    public function main()
    {
        $result = [];

        if (!empty($this->tableName)) {
            $keyword = $this->params['keyword'] ?? '';
            $kwFields = $this->params['kwFields'] ?? '';
            $field = $this->params['field'] ?? '';
            $status = $this->params['status'] ?? '';
            $enabled = $this->params['enabled'] ?? '';
            $searchTime = trim($this->params["searchtime"] ?? 0);

            $startTime = strtotime("-1 month");
            $endTime = time();

            $condition = [
                'uniacid' => $this->uniacid,
                'deleted' => 0,
            ];

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
                $kwFields = str_replace(",", "|", $kwFields);
                $condition[] = [$kwFields, 'like', "%" . trim($keyword) . "%"];
            }

            if (!empty($keyword) && !empty($field)) {
                $field = str_replace(",", "|", $field);
                $condition[] = [$field, 'like', "%" . trim($keyword) . "%"];
            }

            $field = "*";
            $order = "id desc";
            $list = Db::name($this->tableName)->field($field)->where($condition)->order($order)->page($this->pIndex, $this->pSize)->select()->toArray();
            $total = Db::name($this->tableName)->where($condition)->count();
            $pager = pagination2($total, $this->pIndex, $this->pSize);

            $result = [
                'list'  => $list,
                'pager' => $pager,
                'total' => $total,

                'starttime' => $startTime,
                'endtime'   => $endTime,
            ];
        }

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
        $result = [];

        if (!empty($this->tableName)) {
            $id = intval($this->params["id"] ?? 0);

            if ($this->request->isPost()) {
                $fieldList = Db::name($this->tableName)->getFields();
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

                $this->success(["url" => url("", ['id' => $id, 'tab' => str_replace("#tab_", "", $this->params['tab'])])]);
            }

            $field = "*";
            $condition = ['id' => $id];
            $item = Db::name($this->tableName)->field($field)->where($condition)->find();

            $result = [
                'item' => $item
            ];
        }

        return $this->template('post', $result);
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

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                if (!empty($item['is_default'])) {
                    $this->error("默认项不能被删除");
                }

                $updateData = [];
                if (array_key_exists('deleted', $item)) {
                    $updateData['deleted'] = 1;
                }
                if (array_key_exists('delete_time', $item)) {
                    $updateData['delete_time'] = TIMESTAMP;
                }
                if (array_key_exists('is_deleted', $item)) {
                    $updateData['is_deleted'] = 1;
                }
                Db::name($this->tableName)->where(["id" => $item['id']])->update($updateData);
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

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
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

            $items = Db::name($this->tableName)->where(['id' => $id])->select();
            foreach ($items as $item) {
                Db::name($this->tableName)->where(["id" => $item['id']])->update(['deleted' => 0]);
            }
        }
        $this->success(["url" => referer()]);
    }

    // 回收站
    public function recycle()
    {
        $result = [];

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

            $result = [
                'list'  => $list,
                'pager' => $pager,
                'total' => $total,
            ];
        }

        return $this->template('recycle', $result);
    }
}
