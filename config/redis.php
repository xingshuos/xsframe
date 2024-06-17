<?php
/**
 * Created by PhpStorm.
 * info: luoqingqin
 * Date: 2018/10/10
 * Time: 4:01 PM
 */
// +----------------------------------------------------------------------
// | Redis配置 用户自定义配置
// +----------------------------------------------------------------------
use think\facade\Env;

return [
    'host'       => env('redis.host', '127.0.0.1'),
    'port'       => env('redis.port', '6379'),
    'password'   => env('redis.password', ''),
    'select'     => env('redis.select', '0'),
    'timeout'    => 0,
    'expire'     => 0,
    'persistent' => false,
    'prefix'     => env('redis.prefix', ''),
];