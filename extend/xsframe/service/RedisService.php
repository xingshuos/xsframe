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

namespace xsframe\service;

use xsframe\util\ArrayUtil;
use think\cache\driver\Redis;

class RedisService
{
    private $redis;

    // 初始化
    public function __construct()
    {
        if (!$this->redis instanceof Redis) {
            $config      = config('redis.');
            $this->redis = new Redis($config);
        }
    }

    // 拼装键值
    public function getKey($key = "", $uniacid = "")
    {
        return "xsframe_cache_" . $uniacid . "_" . $key;
    }

    // 获取数组
    public function getArray($key = '', $uniacid = '')
    {
        return $this->get($this->getKey($key, $uniacid));
    }

    // 获取字符串
    public function getString($key = "", $uniacid = "")
    {
        return $this->get($key, $uniacid);
    }

    // 获取
    public function get($key = "", $uniacid = "")
    {
        $prefix = "__iserializer__format__::";
        $value  = $this->redis->get($this->getKey($key, $uniacid));
        if (empty($value)) {
            return false;
        }
        if (stripos($value, $prefix) === 0) {
            $ret = ArrayUtil::iUnSerializer(substr($value, strlen($prefix)));
            foreach ($ret as $k => &$v) {
                if (ArrayUtil::isSerialized($v)) {
                    $v = ArrayUtil::iUnSerializer($v);
                }
                if (is_array($v)) {
                    foreach ($v as $k1 => &$v1) {
                        if (ArrayUtil::isSerialized($v1)) {
                            $v1 = ArrayUtil::iUnSerializer($v1);
                        }
                    }
                    unset($v1);
                }
            }
            return $ret;
        } else {
            return $value;
        }
    }

    // 设置
    public function set($key = "", $value = NULL, $uniacid = "", $expire = null)
    {
        $prefix = "__iserializer__format__::";
        if (is_array($value)) {
            foreach ($value as $k => &$v) {
                if (ArrayUtil::isSerialized($v)) {
                    $v = ArrayUtil::iUnSerializer($v);
                }
            }
            unset($v);
            $value = $prefix . ArrayUtil::iSerializer($value);
        }
        $this->redis->set($this->getKey($key, $uniacid), $value, $expire);
        return NULL;
    }

    // 删除
    public function del($key, $uniacid = "")
    {
        $this->redis->rm($this->getKey($key, $uniacid));
        return NULL;
    }


}