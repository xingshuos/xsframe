<?php

namespace xsframe\traits;

use think\facade\Db;

trait AdminTraits
{
    protected $tableName = '';

    // 回收站
    public function index()
    {
        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
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

        return $this->template('list', $result);
    }

    public function main()
    {
        return $this->template("list", []);
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
        return [];
    }

    public function change()
    {
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

        $this->success();
    }

    public function delete()
    {
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
            Db::name($this->tableName)->where(["id" => $item['id']])->update(['deleted' => 1]);
        }
        $this->success(array("url" => referer()));
    }

    // 真实删除
    public function destroy()
    {
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
        $this->success(array("url" => referer()));
    }

    // 还原数据
    public function restore()
    {
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
        $this->success(array("url" => referer()));
    }

    // 回收站
    public function recycle()
    {
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

        return $this->template('recycle', $result);
    }
}
