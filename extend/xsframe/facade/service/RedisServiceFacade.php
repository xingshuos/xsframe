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

namespace xsframe\facade\service;

use xsframe\service\RedisService;
use think\Facade;

/**
 * @method static set(string $key, $value, $expire = null)
 * @method static get(string $key)
 * @method static del(string $key)
 * @method static enqueue(string $queueName, array $job)
 * @method static dequeue(string $queueName)
 * @method static increment(string $key, int $step = 1)
 */
class RedisServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return RedisService::class;
    }
}