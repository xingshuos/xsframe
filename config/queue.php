<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    'default'     => 'redis',
    'connections' => [
        'sync'     => [
            'type' => 'sync',
        ],
        'database' => [
            'type'       => 'database',
            'queue'      => 'default',
            'table'      => 'jobs',
            'connection' => null,
        ],
        'redis'    => [
            'type'           => 'redis',
            'queue'          => 'default',
            'host'           => '127.0.0.1',
            'port'           => 6379,
            'password'       => '',
            'select'         => 0,
            'timeout'        => 0, // 连接超时时间(秒)
            'persistent'     => true, // 使用持久连接
            'retry_interval' => 1000, // 重试间隔(毫秒)
            'read_timeout'   => 0, // 读取超时时间(秒)
        ],
    ],
    'failed'      => [
        'type'  => 'none',
        'table' => 'failed_jobs',
    ],
];
