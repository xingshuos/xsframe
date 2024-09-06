<?php

namespace xsframe\chinaums;

use Exception;

class Factory
{
    /**
     * @var array $config
     */
    protected static $config = [
        // 请求网关  https://api-mop.chinaums.com/v1
        'gateway' => 'https://test-api-open.chinaums.com/v1',
        // 商户号
        'mid' => '898201612345678',
        // 终端号
        'tid' => '88880001',
        // APPID
        'appid' => '10037e6f6823b20801682b6a5e5a0006',
        // KEY
        'appkey' => '1c4e3b16066244ae9b236a09e5b312e8',
    ];

    public static function app($name)
    {
        $class = 'xsframe\chinaums\\Provider\\' . $name;
        if (class_exists($class)) {
            $objcet = new $class(self::$config);
            return $objcet;
        } else {
            throw new Exception("err:{$class}类不存在");
        }
    }

    public static function config($config)
    {
        self::$config = $config;
    }

    public static function __callStatic(string $service, array $params)
    {
        if (!empty($params)) {
            $namespace = $params[0] ?? '';
            if (is_string($namespace)) {
                $service = $service . '\\' . $service;
            }
        }
        return self::app($service);
    }
}
