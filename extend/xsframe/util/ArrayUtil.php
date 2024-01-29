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

namespace xsframe\util;

class ArrayUtil
{
    /**
     * @var string
     */
    private static $type = 'Object';

    /**
     * 获取列表内某一列的数据(有重复可能性 客户端自行处理).
     *
     * @param array $list 数组数据
     * @param string $keyField 键名 array_colum
     *
     * @return array
     * @throws \Exception
     */
    public static function getColumn(array $list, $keyField)
    {
        if (empty($list)) {
            return $list;
        }
        self::validateData($list, $keyField);
        $data = [];
        $list = array_filter($list);
        if (self::$type == 'Object') {
            foreach ($list as $item) {
                $getFunc = 'get' . ucfirst($keyField);
                if ($getFunc == 'getUid' && is_array($item)) {
                    $data[] = $item['uid'];
                } else {
                    $data[] = $item->$getFunc();
                }
            }
        } elseif (self::$type == 'Array') {
            $data = array_filter(array_column($list, $keyField));
        }
        return $data;
    }

    /**
     * 获取列表内某一列的数据(有重复可能性 客户端自行处理).
     *
     * @param array $list 数组数据
     * @param string $keyField 键名 array_colum
     *
     * @return array
     * @throws \Exception
     */
    public static function getColumnInfo(array $list, $keyField)
    {
        if (empty($list)) {
            return $list;
        }
        self::validateData($list, $keyField);
        $data = [];
        $list = array_filter($list);
        if (self::$type == 'Object') {
            $objectData = [];
            foreach ($list as $item) {
                $getFunc = 'get' . ucfirst($keyField);
                if ($getFunc == 'getUid' && is_array($item)) {
                    $objectData[$item['uid']] = $item;
                } else {
                    $objectData[$item->$getFunc()] = $item;
                }
            }
            return $objectData;
        } elseif (self::$type == 'Array') {
            $data = array_column($list, $keyField);
        }
        return $data;
    }

    /**
     * hash一个数组.
     *
     * @param array $list 数组数据
     * @param string $keyField 键名
     * @param mixed $valueField 键值
     *
     * @return array
     * @throws \Exception
     */
    public static function toHash(array $list, $keyField, $valueField = null)
    {
        if (empty($list)) {
            return $list;
        }
        self::validateData($list, $keyField);
        $data = [];
        $getKeyField = 'get' . ucfirst($keyField);
        $getValueField = 'get' . ucfirst($valueField);
        if (self::$type == 'Object') {
            foreach ($list as $item) {
                if ($valueField) {
                    $data[$item->$getKeyField()] = $item->$getValueField();
                } else {
                    $data[$item->$getKeyField()] = $item;
                }
            }
        } elseif (self::$type == 'Array') {
            foreach ($list as $item) {
                if ($valueField) {
                    $data[$item[$keyField]] = $item[$valueField];
                } else {
                    $data[$item[$keyField]] = $item;
                }
            }
        }
        return $data;
    }

    /**
     * 验证数据.
     *
     * @param $list
     * @param $field
     *
     * @throws \Exception
     */
    protected static function validateData(array $list, $field)
    {
        $list = array_values($list);
        $first = $list [0];
        if (is_object($first)) {
            self::$type = 'Object';
            $className = get_class($first);
            $class = new  \ReflectionClass($className);
            if (!$class->hasProperty($field)) {
                throw new \Exception("Object has not property {$field}");
            }
        } elseif (is_array($first)) {
            self::$type = 'Array';
            if (!array_key_exists($field, $first)) {
                throw new \Exception("Array has not property {$field}");
            }
        }
    }

    /**
     * 二维数组排序.
     *
     * @param array $data 原始数据
     * @param string $fieldName1 排序字段1
     * @param string $sort1 顺序 asc/desc
     * @param string $filedName2 排序字段2
     * @param string $sort2 顺序 asc/desc
     *
     * @return array
     * @throws \Exception
     */
    public static function sortBy(array $data, $fieldName1, $sort1, $filedName2 = '', $sort2 = '')
    {
        if (empty($data)) {
            return [];
        }
        $column1 = self::getColumn($data, $fieldName1);
        $sort1 = ($sort1 === 'asc') ? SORT_ASC : SORT_DESC;
        $column2 = [];
        if ($filedName2 && $sort2) {
            $column2 = self::getColumn($data, $filedName2);
            $sort2 = ($sort2 === 'asc') ? SORT_ASC : SORT_DESC;
        }
        if ($column2) {
            array_multisort($column1, $sort1, $column2, $sort2, $data);
        } else {
            array_multisort($column1, $sort1, $data);
        }
        return $data;
    }

    /**
     * 数组排序，元素为对象，根据对象的两个属性排序.
     *
     * @param array $data
     * @param       $orderFirst
     * @param       $orderSecond
     */
    public static function arraySortByAttribute(array $data, $orderFirst, $orderSecond)
    {
        $count = count($data);
        for ($i = 1; $i < $count; ++$i) {
            for ($j = $count - 1; $j >= $i; --$j) {
                if ($data[$j]->get . $orderFirst() > $data[$j - 1]->get . $orderFirst()) {
                    $temp = $data[$j - 1];
                    $data[$j - 1] = $data[$j];
                    $data[$j] = $temp;
                } elseif ($data[$j]->get . $orderFirst() == $data[$j - 1]->get . $orderFirst()) {
                    if ($data[$j]->get . $orderSecond() > $data[$j - 1]->get . $orderSecond()) {
                        $temp = $data[$j - 1];
                        $data[$j - 1] = $data[$j];
                        $data[$j] = $temp;
                    }
                }
            }
        }
    }

    /**
     *对元素为对象的数组唯一化处理.
     *
     * @param array $array
     *
     * @return array
     */
    public static function objectArrayUnique(array $array)
    {
        foreach ($array as $val) {
            $i = 0;
            foreach ($array as $k => $v) {
                $diff = $val == $v;
                if ($diff && $i == 0) {
                    ++$i;
                } elseif ($diff) {
                    unset($array[$k]);
                }
            }
        }
        return $array;
    }

    /**
     * 过滤空结构
     *
     * @param array $insertData
     * @param array $structData
     * @param bool $format
     *
     * @return array
     */
    public static function formatStruct(array $insertData, array $structData, $format = false)
    {
        foreach ($insertData as $key => $row) {
            if (!in_array($key, $structData) || is_null($row)) {
                unset($insertData[$key]);
            } elseif (is_string($row)) {
                $insertData[$key] = $row;
            }
            if (($format === true) && ($key == 'created' || $key == 'updated') && !empty($row) && is_string($row)) {
                $insertData[$key] = strtotime($row);
            }
        }
        return $insertData;
    }

    /**
     * 格式化json
     *
     * @param string $data
     *
     * @return string
     */
    public static function formatJsonToKeyValue(string $data)
    {
        $data = json_decode($data, true);
        if (!is_array($data)) {
            return '[]';
        }
        foreach ($data as $key => $row) {
            if (!is_string($row)) {
                $data[$key] = ['label' => $key, 'value' => $row];
            } else {
                unset($data[$key]);
            }
        }
        sort($data);
        return json_encode($data, true);
    }

    /**
     * 数组中的某个field作为新数组的key filed 应该唯一
     *
     * @param $array
     * @param $field
     *
     * @return array
     * @throws \Exception
     */
    public static function arrayKeyMap($array, $field): array
    {
        $arrayMap = [];
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if (isset($value[$field])) {
                    $arrayMap[$value[$field]] = $value;
                } else {
                    throw  new \Exception('filed not found in array ', 404);
                }
            }
        }
        return $arrayMap;
    }

    /**
     * 获取指定键值
     * @param array $array
     * @param string $key
     * @param string $defaultValue
     * @return mixed|string
     */
    public static function getVal(array $array, string $key, $defaultValue = "")
    {
        if (!empty($array) && isset($array[$key])) {
            return $array[$key];
        }
        return $defaultValue;
    }

    /**
     * 得到集合A和集合B的差集{x/x∈A,且x￠B}
     *
     * @param array $arrayA
     * @param array $arrayB
     *
     * @return array
     */
    public static function getDifferent($arrayA, $arrayB): array
    {
        // return array_diff($arrayA, array_intersect($arrayA, $arrayB));

        $arrayB = array_flip($arrayB); //将数组键值调换
        foreach ($arrayA as $key => $val) {
            if (isset($arrayB[$val])) {
                unset($arrayA[$key]);
            }
        }
        return $arrayA;
    }

    /**
     * 数组中的某个fieldA作为新数组的key ,将FieldB作为新数组FieldA的对应内容
     *
     * @param $array
     * @param $fieldA
     * @param $fieldB
     *
     * @return array
     * @throws \Exception
     */
    public static function arrayKeyFiledMap($array, $fieldA, $fieldB): array
    {
        $arrayMap = [];
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if (isset($value[$fieldA])) {
                    $arrayMap[$value[$fieldA]] = $value[$fieldB];
                } else {
                    throw  new \Exception('filed not found in array ', 404);
                }
            }
        }
        return $arrayMap;
    }

    /**
     * 数组 转 对象
     * @param $arr
     * @return object|void
     */
    public static function array2object($arr)
    {
        if (gettype($arr) != 'array') {
            return;
        }
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object') {
                $arr[$k] = (object)self::array2object($v);
            }
        }

        return (object)$arr;
    }

    /**
     * 对象 转 数组
     *
     * @param object $obj 对象
     * @return array
     */
    public static function object2array($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return [];
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)self::object2array($v);
            }
        }

        return $obj;
    }

    /**
     * 将一个数组转换为 XML 结构的字符串
     * @param array $arr 要转换的数组
     * @param int $level 节点层级, 1 为 Root.
     * @return string XML 结构的字符串
     */
    public static function array2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach ($arr as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if (!is_array($value)) {
                $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
            } else {
                $s .= "<{$tagname}>" . self::array2xml($value, $level + 1) . "</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</xml>" : $s;
    }

    /**
     * 将XML转为array
     * @param $xml
     * @return mixed
     */
    public static function xml2array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    /**
     * 将字符串参数变为数组
     * @param $query
     * @return array
     */
    public static function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            if (is_array($item)) {
                $params[$item[0]] = $item[1] ?? 0;
            }
        }
        return $params;
    }

    /**
     * 将参数变为字符串
     * @param $array_query
     * @return string
     */
    public static function getUrlQuery($array_query)
    {
        $tmp = array();
        foreach ($array_query as $k => $param) {
            $tmp[] = $k . '=' . $param;
        }
        $params = implode('&', $tmp);
        return $params;
    }

    /**
     * 将字符串或数组去换行符
     * @param $array
     * @return mixed
     */
    public static function replaceArrayNewLine($array)
    {
        $replaceStr = $array;
        if (is_array($array)) {
            $replaceStr = json_encode($array);
            $newStr = str_replace("\\n", "", $replaceStr);
            return json_decode($newStr, true);
        } else {
            $newStr = str_replace("\\n", "", $replaceStr);
            return $newStr;
        }
    }

    // 数组中int类型强制转换为字符串
    public static function arrayIntToString($item)
    {
        if (!is_array($item)) {
            $item = (string)$item;
        } else {
            if (count($item) == count($item, 1)) {
                $item = array_map([__CLASS__, "checkStrVal"], $item);
            } else {
                $item = array_map([__CLASS__, "arrayIntToString"], $item);
            }
        }
        return $item;
    }

    public static function checkStrVal($item)
    {
        if (is_array($item)) {
            return [];
        } else {
            return strval($item);
        }
    }

    public static function iSerializer($value)
    {
        return serialize($value);
    }

    public static function iUnSerializer($value)
    {
        if (empty($value)) {
            return array();
        }
        if (!self::isSerialized($value)) {
            return $value;
        }
        $result = unserialize($value);
        if ($result === false) {
            $temp = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs) {
                return 's:' . strlen($matchs[2]) . ':"' . $matchs[2] . '";';
            }, $value);
            return unserialize($temp);
        } else {
            return $result;
        }
    }

    public static function isSerialized($data, $strict = true)
    {
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            if (false === $semicolon && false === $brace)
                return false;
            if (false !== $semicolon && $semicolon < 3)
                return false;
            if (false !== $brace && $brace < 4)
                return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            case 'a' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'O' :
                return false;
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }

    /**
     * 获取数组中指定的列
     * @param $array
     * @param $fields
     * @return array
     */
    public static function getFieldsFromArray($array, string $fields = "*"): array
    {
        $result = [];
        if (!empty($array)) {
            if ($fields != '*') {
                if (is_string($fields)) {
                    $fields = explode(",", $fields);
                }
                foreach ($fields as $field) {
                    if (isset($array[$field])) {
                        $result[$field] = $array[$field];
                    }
                }
            } else {
                $result = $array;
            }
        }
        return $result;
    }
}