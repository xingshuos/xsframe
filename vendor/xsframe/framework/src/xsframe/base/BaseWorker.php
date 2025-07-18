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

namespace xsframe\base;

use Workerman\Worker;
use think\facade\Config;

abstract class BaseWorker extends Worker
{
    protected static $heartbeatTime = 50; // 心跳时间

    // 应用配置模板
    protected $appConfig = [
        'port'     => 2345,
        'protocol' => 'websocket',
    ];

    public function __construct()
    {
        // 读取应用配置
        $config = array_merge(
            $this->appConfig,
            Config::get('websocket.' . static::class)
        );

        $host = empty($config['host']) ? "0.0.0.0" : $config['host'];
        $port = $config['port'];

        // 构造监听地址
        $socket = "{$config['protocol']}://{$host}:{$port}";
        parent::__construct($socket, $config['context']);
        $this->initWorker();
    }

    abstract protected function initWorker();
}