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
     * 执行sql
     * @param $sql
     * @return mixed
     */
    public function execute($sql)
    {
        return $sql ? Db::execute($sql) : "";
    }

    /**
     * 执行sql
     * @param $sql
     * @return mixed
     */
    public function query($sql = null)
    {
        return $sql ? Db::query($sql) : "";
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
     * @param string|array $order
     * @param int $pIndex
     * @param int $pSize
     * @return array
     */
    public function getList(array $where = [], string $field = "*", $order = "", int $pIndex = 0, int $pSize = 10)
    {
        try {
            extract(self::getWhere($where));
            if ($pIndex == 0 && !empty($this->params['page']) && $this->params['page'] > 1)
                $pIndex = $this->params['page'];
            if ($pSize == 10 && !empty($this->params['size']) && $this->params['size'] != 10)
                $pSize = $this->params['size'];
            if ($pIndex == 0)
                $pIndex = 1;
            $list = Db::name($this->tableName)->field($field)->where($where, $op, $condition)->order($order)->page(intval($pIndex), intval($pSize))->select()->toArray();
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
    public function getAll(array $where = [], string $field = "*", string $order = "", string $keyField = '')
    {
        try {
            extract(self::getWhere($where));
            if (empty($keyField)) {
                $list = Db::name($this->tableName)->field($field)->where($where, $op, $condition)->order($order)->select()->toArray();
            } else {
                $temp = Db::name($this->tableName)->field($field)->where($where, $op, $condition)->order($order)->select()->toArray();
                $rs = [];
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
     * @return int|mixed
     */
    public function getTotal($where, string $field = "*")
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
     * 获取数据数量
     * @param $where
     * @param string $field
     * @return int|mixed
     */
    public function getCount($where, string $field = "*")
    {
        return $this->getTotal($where, $field);
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

        try {
            $value = Db::name($this->tableName)->where($where, $op, $condition)->order($orderBy)->value($field);
        } catch (\Exception $exception) {
            $value = null;
        }

        return $value;
    }

    /**
     * 更新数据
     * @param array $updateData
     * @param array $condition
     * @return int|mixed
     */
    public function updateInfo(array $updateData, array $where = [])
    {
        extract(self::getWhere($where));

        try {
            $isUpdate = Db::name($this->tableName)->where($where, $op, $condition)->update($updateData);
        } catch (\Exception $exception) {
            $isUpdate = 0;
        }

        return (int)$isUpdate;
    }

    /**
     * 删除数据
     * @param $condition
     * @return int|mixed
     */
    public function deleteInfo($where)
    {
        extract(self::getWhere($where));
        try {
            $isDelete = Db::name($this->tableName)->where($where, $op, $condition)->delete();
        } catch (\Exception $exception) {
            $isDelete = 0;
        }
        return (int)$isDelete;
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
    public function insertAll($data)
    {
        return Db::name($this->tableName)->insertAll($data);
    }

    // 获取查询条件
    private function getWhere($where = null): array
    {
        $op = null;
        $condition = null;
        if (is_array($where) && is_string($where[0]) && is_array($where[1])) {
            $op = $where[1];
            $where = $where[0];
        } else {
            if (is_numeric($where)) {
                $condition['id'] = $where;
                $where = ['id' => $where];
            }
        }
        return ['where' => $where, 'op' => $op, 'condition' => $condition];
    }

    // 表是否存在
    public function hasTable($tableName = null)
    {
        if (empty($tableName)) {
            $tables = Db::query("SHOW TABLES LIKE '" . tablename($this->tableName, false) . "'");
        } else {
            $tables = Db::query("SHOW TABLES LIKE '" . tablename($tableName, false) . "'");
        }
        return !empty($tables);
    }

    // 字段是否存在
    public function hasField($field)
    {
        $fields = Db::name($this->tableName)->getTableFields();
        return in_array($field, $fields);
    }

    // 添加字段
    public function addField($field, $type = 'varchar', $length = 255, $default = '', $isNull = 1, $comment = '')
    {
        if ($this->hasField($field)) {
            return true;
        }
        $sql = "ALTER TABLE " . tablename($this->tableName) . " ADD COLUMN `{$field}` {$type}({$length}) " . ($default ? "DEFAULT '{$default}'" : '') . ($isNull ? 'NULL' : 'NOT NULL') . " COMMENT '{$comment}' ";
        return Db::execute($sql);
    }

    // 删除字段
    public function delField($field)
    {
        if (!$this->hasField($field)) {
            return true;
        }
        $sql = "ALTER TABLE " . tablename($this->tableName) . " DROP COLUMN `{$field}`";
        return Db::execute($sql);
    }

    // 修改字段
    public function updateField($oldFiled, $field, $type = 'varchar', $length = 255, $default = '', $isNull = 1, $comment = '')
    {
        if (!$this->hasField($field)) {
            return true;
        }
        $sql = "ALTER TABLE " . tablename($this->tableName) . " CHANGE `{$oldFiled}` `{$field}` {$type}({$length}) " . ($default ? "DEFAULT '{$default}'" : '') . ($isNull ? 'NULL' : 'NOT NULL') . " COMMENT '{$comment}' ";
        return Db::execute($sql);
    }

    /**
     * 分割sql语句
     * @param string $content sql内容
     * @param bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
     * @param array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀my_替换成me_
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    public function sqlParse($content = '', $string = false, $replace = [])
    {
        // 被替换的前缀
        $from = '';
        // 要替换的前缀
        $to = '';

        // 替换表前缀
        if (!empty($replace)) {
            $to = current($replace);
            $from = current(array_flip($replace));
        }

        if ($content != '') {
            // 纯sql内容
            $pure_sql = [];

            // 多行注释标记
            $comment = false;

            // 按行分割，兼容多个平台
            $content = str_replace(["\r\n", "\r"], "\n", $content);
            $content = explode("\n", trim($content));

            // 循环处理每一行
            foreach ($content as $key => $line) {
                // 跳过空行
                if ($line == '') {
                    continue;
                }

                // 跳过以#或者--开头的单行注释
                if (preg_match("/^(#|--)/", $line)) {
                    continue;
                }

                // 跳过以/**/包裹起来的单行注释
                if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                    continue;
                }

                // 多行注释开始
                if (substr($line, 0, 2) == '/*') {
                    $comment = true;
                    continue;
                }

                // 多行注释结束
                if (substr($line, -2) == '*/') {
                    $comment = false;
                    continue;
                }

                // 多行注释没有结束，继续跳过
                if ($comment) {
                    continue;
                }

                // 替换表前缀
                if ($from != '') {
                    $line = str_replace('`' . $from, '`' . $to, $line);
                }

                // sql语句
                array_push($pure_sql, $line);
            }

            // 只返回一条语句
            if ($string) {
                return implode($pure_sql, "");
            }
            // 以数组形式返回sql语句
            $pure_sql = implode("\n", $pure_sql);
            $pure_sql = explode(";\n", $pure_sql);
            return $pure_sql;
        } else {
            return $string == true ? '' : [];
        }
    }
}
