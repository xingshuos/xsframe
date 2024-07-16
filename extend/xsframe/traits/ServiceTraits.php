<?php

namespace xsframe\traits;

use think\db\exception\DbException;
use think\facade\Db;
use think\Model;

trait ServiceTraits
{
    protected $tableName = "";

    /**
     * 设置表名
     * @param $tableName
     * @return $this
     */
    public function name($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * 获取数据基本信息
     * @param $where
     * @param string $field
     * @param string $order
     * @return array|mixed|Db|Model|null
     */
    public function getInfo($where, string $field = "*", string $order = "")
    {
        try {
            extract(self::getWhere($where));
            $info = Db::name($this->tableName)->field($field)->where($where, $op, $condition)->order($order)->find();
        } catch (\Exception $exception) {
            $info = [];
        }
        return $info;
    }

    /**
     * 分页获取数据
     * @param array $where
     * @param string $field
     * @param string $order
     * @param int $pIndex
     * @param int $pSize
     * @return array
     */
    public function getList(array $where = [], string $field = "*", string $order = "", int $pIndex = 1, int $pSize = 10): array
    {
        try {
            extract(self::getWhere($where));
            if ($pIndex == 1 && !empty($this->params['page']) && $this->params['page'] > 1)
                $pIndex = $this->params['page'];
            if ($pSize == 10 && !empty($this->params['size']) && $this->params['size'] != 10)
                $pSize = $this->params['size'];
            $list = Db::name($this->tableName)->field($field)->where($where, $op, $condition)->order($order)->page($pIndex, $pSize)->select()->toArray();
        } catch (\Exception $exception) {
            $list = [];
        }
        return $list;
    }

    /**
     * 获取全部数据
     * @param array $where
     * @param string $field
     * @param string $order
     * @param string $keyField
     * @return array
     */
    public function getAll(array $where = [], string $field = "*", string $order = "", string $keyField = ''): array
    {
        try {
            extract(self::getWhere($where));
            if (empty($keyField)) {
                $list = Db::name($this->tableName)->field($field)->where($where, $op, $condition)->order($order)->select()->toArray();
            } else {
                $temp = Db::name($this->tableName)->field($field)->where($where, $op, $condition)->order($order)->select()->toArray();
                $rs   = [];
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
     * @param $where
     * @param string $field
     * @return int
     */
    public function getTotal($where, string $field = "*"): int
    {
        extract(self::getWhere($where));

        try {
            $total = Db::name($this->tableName)->where($where, $op, $condition)->count($field);
        } catch (\Exception $exception) {
            $total = 0;
        }

        return intval($total);
    }

    /**
     * 获取单个字段
     * @param $condition
     * @param $field
     * @param string $orderBy
     * @return int|mixed
     */
    public function getValue($where, $field, string $orderBy = 'id desc')
    {
        extract(self::getWhere($where));
        $total = Db::name($this->tableName)->where($where, $op, $condition)->order($orderBy)->value($field);
        return $total ? $total : 0;
    }

    /**
     * 更新数据
     * @param array $updateData
     * @param array $condition
     * @return bool
     * @throws DbException
     */
    public function updateInfo(array $updateData, array $where = []): bool
    {
        extract(self::getWhere($where));
        $isUpdate = Db::name($this->tableName)->where($where, $op, $condition)->update($updateData);
        return (bool)$isUpdate;
    }

    /**
     * 删除数据
     * @param $condition
     * @return bool
     * @throws DbException
     */
    public function deleteInfo($where): bool
    {
        extract(self::getWhere($where));
        $isDelete = Db::name($this->tableName)->where($where, $op, $condition)->delete();
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

    // 获取查询条件
    private function getWhere($where = null): array
    {
        $op        = null;
        $condition = null;
        if (is_array($where) && is_string($where[0]) && is_array($where[1])) {
            $op    = $where[1];
            $where = $where[0];
        }
        return ['where' => $where, 'op' => $op, 'condition' => $condition];
    }
}
