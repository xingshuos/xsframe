<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

// 使用每套系统独有的 AUTHKEY 生成缓存命名空间，避免多套系统共用 Redis 时发生键冲突。
$authKey = trim(strval(env('authkey', '')));
$cacheNamespaceSource = $authKey !== '' ? $authKey : trim(strval(env('database.database', 'default')));
$cacheNamespace = substr(hash('sha256', $cacheNamespaceSource), 0, 16);
$cachePrefix = $cacheNamespace . '_';

return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file'  => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => env('cache.prefix', 'xsframe'),
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // 更多的缓存连接

        // redis缓存
        'redis' => [
            // 驱动方式
            'type'       => 'redis',
            // 缓存前缀
            'prefix'     => $cachePrefix,
            // 缓存有效期 0表示永久缓存
            'expire'     => '0',
            // 服务器信息
            'host'       => env('cache.host', '127.0.0.1'),
            'port'       => env('cache.port', '6379'),
            'password'   => env('cache.password', ''),
            // ThinkPHP Redis 驱动使用 select 参数选择数据库，db 参数不会生效。
            'select'     => intval(env('cache.select', '0')),
            'persistent' => env('redis.persistent', false),
            'timeout'    => 0,
        ],
    ],
];
