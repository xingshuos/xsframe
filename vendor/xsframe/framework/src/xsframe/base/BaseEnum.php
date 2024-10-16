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

abstract class BaseEnum
{

    /**
     * Store existing constants in a static cache per object.
     *
     * @var array
     */
    private static $constantsCache = [];

    /**
     * 获取枚举数值的文字解释
     *
     * @param string $type
     * @return string
     */
    public static function getText(string $type): string
    {
        return '';
    }

    /**
     *
     * @param string $enum
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getValue(string $enum)
    {
        $calledClass = get_called_class();
        self::setConstantsCache($calledClass);
        return self::$constantsCache[$calledClass][$enum];
    }

    /**
     * Returns all possible values as an array.
     *
     * @return array Constant name in key, constant value in value
     * @throws \ReflectionException
     */
    public static function toArray()
    {
        $calledClass = get_called_class();
        self::setConstantsCache($calledClass);
        return self::$constantsCache[$calledClass];
    }

    /**
     * Get enums.
     *
     * @param bool $format
     *
     * @return array|mixed
     * @throws \ReflectionException
     */
    protected static function getEnums(bool $format = false)
    {
        $calledClass = get_called_class();
        self::setConstantsCache($calledClass);
        $result = self::$constantsCache[$calledClass];
        $key = array_keys($result);
        $value = array_values($result);
        $key = array_map('strtolower', $key);
        $result = $format ? $value : array_combine($key, $value);
        return $result;
    }

    /**
     *
     * @param bool $format
     *
     * @return array|mixed
     * @throws \ReflectionException
     */
    protected static function getEnumsValues(bool $format = true)
    {
        $calledClass = get_called_class();
        self::setConstantsCache($calledClass);
        $result = self::$constantsCache[$calledClass];
        $key = array_keys($result);
        $value = array_values($result);
        $value = array_map('strtolower', $value);
        $result = $format ? $value : array_combine($key, $value);
        return $result;
    }

    /**
     * Set property constantsCache.
     *
     * @param
     *            $calledClass
     *
     * @throws \ReflectionException
     */
    protected static function setConstantsCache($calledClass)
    {
        if (!array_key_exists($calledClass, self::$constantsCache)) {
            $reflection = new \ReflectionClass($calledClass);
            self::$constantsCache[$calledClass] = $reflection->getConstants();
        }
    }

    /**
     * Returns true if the enum exists, otherwise returns false.
     *
     * @param
     *            $enum
     * @param
     *            $strtolower
     *
     * @return bool
     * @throws \ReflectionException
     */
    public static function has($enum, $strtolower = true)
    {
        $enum = $strtolower ? strtolower($enum) : $enum;
        if (in_array($enum, array_values(self::getEnums(true)))) {
            return true;
        }
        return false;
    }

    /**
     * Returns true if the enum exists, otherwise returns false.
     *
     * @param string $enum
     * @param bool $strtolower
     *
     * @return bool
     * @throws \ReflectionException
     */
    public static function hasValue(string $enum, bool $strtolower = true)
    {
        $enum = $strtolower ? strtolower($enum) : $enum;
        if (in_array($enum, array_values(self::getEnumsValues(true)))) {
            return true;
        }
        return false;
    }

    /**
     * Returns a value of enum if the enum exists, otherwise returns false.
     *
     * @param string $enum
     *
     * @return bool|mixed
     * @throws \ReflectionException
     */
    public static function get(string $enum)
    {
        $enum = strtolower($enum);
        if (in_array($enum, array_values(self::getEnums(true)))) {
            return $enum;
        }
        return false;
    }

    /**
     * Returns a value of enum if the enum exists, otherwise returns false.
     *
     * @param string $enumValue
     *
     * @return bool|mixed
     * @throws \ReflectionException
     */
    public static function getStrToLower(string $enumValue)
    {
        $enum = strtolower($enumValue);
        if (in_array($enum, array_values(self::getEnumsValues(true)))) {
            return $enumValue;
        }
        return false;
    }

    /**
     * 获取文本列表
     * @param string $typeString
     * @return array
     */
    public static function getTextArray(string $typeString): array
    {
        $appTypes = explode(",", $typeString);
        $newAppTypes = [];
        foreach ($appTypes as $type) {
            $newAppTypes[] = self::getText($type);
        }
        return $newAppTypes;
    }

    /**
     * 获取文字列表
     * @return array
     */
    public static function getEnumsList(): array
    {
        $newEnumsList = [];

        try {
            $calledClass = get_called_class();
            $enumsValuesList = self::getEnumsValues();
            foreach ($enumsValuesList as $key => $item) {
                $newEnumsList[$item] = (new $calledClass)->getText($item);
            }
        } catch (\ReflectionException $e) {
            return [];
        }

        return $newEnumsList;
    }
}
