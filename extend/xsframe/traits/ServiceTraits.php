<?php

namespace xsframe\traits;

use think\db\exception\DbException;
use think\Exception;
use think\facade\Db;
use think\Model;

trait ServiceTraits
{
    protected $tableName = "";

    /**
     * 获取数据基本信息
     * @param $where
     * @param string $field
     * @return array|mixed|Db|Model|null
     */
    public function getInfo($where, string $field = "*")
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
    public function getList(array $condition = array(), string $field = "*", string $order = "", int $pIndex = 1, int $pSize = 10): array
    {
        try {
            if ($pIndex == 1 && !empty($this->params['page']) && $this->params['page'] > 1) $pIndex = $this->params['page'];
            if ($pSize == 10 && !empty($this->params['size']) && $this->params['size'] != 10) $pSize = $this->params['size'];
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
    public function getAll(array $condition = array(), string $field = "*", string $order = "", string $keyField = ''): array
    {
        try {
            if (empty($keyField)) {
                $list = Db::name($this->tableName)->field($field)->where($condition)->order($order)->select()->toArray();
            } else {
                $temp = Db::name($this->tableName)->field($field)->where($condition)->order($order)->select()->toArray();
                $rs = array();
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
     * @param string $field
     * @return int
     * @throws DbException
     */
    public function getTotal($condition, string $field = "*"): int
    {
        $total = Db::name($this->tableName)->where($condition)->count($field);
        return intval($total);
    }

    /**
     * 获取单个字段
     * @param $condition
     * @param $field
     * @param string $orderBy
     * @return int|mixed
     */
    public function getValue($condition, $field, string $orderBy = 'id desc')
    {
        $total = Db::name($this->tableName)->where($condition)->order($orderBy)->value($field);
        return $total ? $total : 0;
    }

    /**
     * 更新数据
     * @param array $updateData
     * @param array $condition
     * @return bool
     * @throws DbException
     */
    public function updateInfo(array $updateData, array $condition = []): bool
    {
        $isUpdate = Db::name($this->tableName)->where($condition)->update($updateData);
        return (bool)$isUpdate;
    }

    /**
     * 删除数据
     * @param $condition
     * @return bool
     * @throws DbException
     */
    public function deleteInfo($condition): bool
    {
        $isDelete = Db::name($this->tableName)->where($condition)->delete();
        return (bool)$isDelete;
    }

    /**
     * 单行插入
     * @param $data
     * @return int|string
     */
    public function insertInfo($data)
    {
        return Db::name($this->tableName)->insertGetId($data);
    }

    /**
     * 批量插入数据
     * @param $data
     * @return int
     */
    public function insertAll($data): int
    {
        return Db::name($this->tableName)->insertAll($data);
    }
}
