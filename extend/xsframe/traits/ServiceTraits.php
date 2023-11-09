<?php

namespace xsframe\traits;

use think\facade\Db;

trait ServiceTraits
{
    protected $tableName = "";

    /**
     * 获取数据基本信息
     * @param $where
     * @param string $field
     * @return array|mixed|Db|\think\Model|null
     */
    public function getInfo($where, $field = "*")
    {
        try {
            $info = Db::name($this->tableName)->field($field)->where($where)->find();
        } catch (\Exception $exception) {
            $info = [];
        }
        return $info;
    }

    /**
     * 分页获取数据
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param int $pIndex
     * @param int $pSize
     * @return array
     */
    public function getList($condition = array(), $field = "*", $order = "", $pIndex = 1, $pSize = 10)
    {
        try {
            $list = Db::name($this->tableName)->field($field)->where($condition)->order($order)->page($pIndex, $pSize)->select()->toArray();
        } catch (\Exception $exception) {
            $list = [];
        }
        return $list;
    }

    /**
     * 获取全部数据
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param string $keyField
     * @return array
     */
    public function getAll($condition = array(), $field = "*", $order = "", $keyField = '')
    {
        try {
            if (empty($keyField)) {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($order)->select()->toArray();
            } else {
                $temp = Db::name($this->tableName)->field($field)->where($condition)->order($order)->select()->toArray();
                $rs   = array();
                if (!empty($temp)) {
                    foreach ($temp as $key => &$row) {
                        if (isset($row[$keyField])) {
                            $rs[$row[$keyField]] = $row;
                        } else {
                            $rs[] = $row;
                        }
                    }
                }
                $list = $rs;
            }
        } catch (\Exception $exception) {
            $list = [];
        }
        return $list;
    }

    /**
     * 获取数据数量
     * @param $condition
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getTotal($condition)
    {
        $total = Db::name($this->tableName)->where($condition)->count();
        return $total ? $total : 0;
    }

    /**
     * 获取单个字段
     * @param $condition
     * @param $field
     * @param string $orderBy
     * @return int|mixed
     */
    public function getValue($condition, $field, $orderBy = 'id desc')
    {
        $total = Db::name($this->tableName)->where($condition)->order($orderBy)->value($field);
        return $total ? $total : 0;
    }

    /**
     * 更新数据
     * @param array $updateData
     * @param array $condition
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function updateInfo($updateData, $condition = [])
    {
        $isUpdate = Db::name($this->tableName)->where($condition)->update($updateData);
        return $isUpdate ? true : false;
    }

    /**
     * 删除数据
     * @param $condition
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function deleteInfo($condition)
    {
        $isDelete = Db::name($this->tableName)->where($condition)->delete();
        return $isDelete ? true : false;
    }

    /**
     * 单行插入
     * @param $data
     * @return int|string
     */
    public function insertInfo($data)
    {
        $isInsert = Db::name($this->tableName)->insertGetId($data);
        return $isInsert;
    }

    /**
     * 批量插入数据
     * @param $data
     * @return int
     */
    public function insertAll($data)
    {
        $isInsert = Db::name($this->tableName)->insertAll($data);
        return $isInsert;
    }
}
