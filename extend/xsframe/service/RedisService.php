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

use think\facade\Cache;
use xsframe\base\CacheBaseController;
use xsframe\util\ArrayUtil;
use think\cache\driver\Redis;

class RedisService extends CacheBaseController
{
    private $redis;

    // 初始化
    public function _cache_initialize()
    {
        if (!$this->redis instanceof Redis) {
            $this->redis = Cache::store('redis')->handler();
        }
    }

    // 拼装键值
    public function getKey($key = "")
    {
        return $this->authkey . "cache_" . $this->uniacid . "_" . $this->module . "_" . $key;
    }

    // 获取
    public function get($key = "")
    {
        $value = $this->redis->get($this->getKey($key));
        if (empty($value)) {
            return false;
        }
        $prefix = "__iserializer__format__::"; // 根据数据前缀判断是否是序列化数据
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
    public function set($key = "", $value = NULL, $expire = null)
    {
        $prefix = "__iserializer__format__::"; // 根据数据前缀判断是否是序列化数据
        if (is_array($value)) {
            foreach ($value as $k => &$v) {
                if (ArrayUtil::isSerialized($v)) {
                    $v = ArrayUtil::iUnSerializer($v);
                }
            }
            unset($v);
            $value = $prefix . ArrayUtil::iSerializer($value);
        }
        return $this->redis->set($this->getKey($key), $value, $expire);
    }

    // 删除
    public function del($key)
    {
        return $this->redis->del($this->getKey($key));
    }

    // 累加
    public function increment($key, $step = 1) {
        return $this->redis->incrBy($this->getKey($key), $step);
    }

    // 递减
    public function decrement($key, $step = 1) {
        return $this->redis->decrBy($this->getKey($key), $step);
    }

    /**
     * 将任务放入列表形式的消息队列
     *
     * @param string $queueName 队列名称
     * @param mixed $job 任务数据
     * @param int|null $priority 优先级（可选，结合 LPUSH 或 RPUSH 使用）
     * @return int 在队列中的新元素位置
     */
    public function enqueue(string $queueName, $job, ?int $priority = null): int
    {
        if ($priority === null || $priority < 0) {
            return $this->redis->lpush($queueName, serialize($job));
        } elseif ($priority > 0) {
            return $this->redis->rpush($queueName, serialize($job));
        }
    }

    /**
     * 移除并返回列表的第一个元素（FIFO - 先进先出）
     *
     * @param string $queueName 队列名称
     * @return mixed|false 获取的任务数据，失败则返回 false
     */
    public function dequeue(string $queueName)
    {
        $jobSerialized = $this->redis->lpop($queueName);
        return $jobSerialized === false ? false : unserialize($jobSerialized);
    }

    /**
     * 移除并返回列表的最后一个元素（LIFO - 后进先出）
     *
     * @param string $queueName 队列名称
     * @return mixed|false 返回最后一个元素的值，若列表为空则返回 false
     */
    public function dequeueFromListLast(string $queueName)
    {
        $jobSerialized = $this->redis->rpop($queueName);
        return $jobSerialized === false ? false : unserialize($jobSerialized);
    }


    /**
     * 批量操作
     *
     * @param array $commands 多条命令组成的数组
     * @return array 返回执行结果
     */
    public function pipeline(array $commands): array
    {
        return $this->redis->pipeline(function ($pipe) use ($commands) {
            foreach ($commands as $command) {
                call_user_func_array([$pipe, $command[0]], $command[1]);
            }
        });
    }

    /**
     * 发布消息到频道
     *
     * @param string $channel 频道名称
     * @param mixed $message 消息内容
     * @return int 返回接收到信息的订阅者数量
     */
    public function publish(string $channel, $message): int
    {
        return $this->redis->publish($channel, serialize($message));
    }

    /**
     * 订阅频道并处理消息
     *
     * @param string|array $channels 频道名称或频道名称数组
     * @param callable $callback 接收到消息时调用的回调函数，接收两个参数：频道名和消息内容
     * @return bool 是否成功订阅
     */
    public function subscribe($channels, callable $callback): bool
    {
        return $this->redis->subscribe((array)$channels, function ($redis, $channel, $message) use ($callback) {
            call_user_func($callback, $channel, unserialize($message));
        });
    }
}