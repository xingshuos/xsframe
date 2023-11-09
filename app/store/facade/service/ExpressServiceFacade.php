<?php

namespace app\store\facade\service;

use app\store\service\ExpressService;
use think\Facade;

/**
 * @method static getInfo(array $condition, string $field = '*')
 * @method static getList(array $condition, string $field = "*", string $order = "", int $pIndex = 1, int $pSize = 10)
 * @method static getAll(array $condition, string $field = "*", string $order = "", $keyField = '')
 * @method static getTotal(array $condition)
 * @method static deleteInfo(array $condition)
 * @method static insertInfo(array $data)
 * @method static insertAll(array $data)
 * @method static updateInfo(array $updateData, array $condition)
 * @method static getValue(array $condition, string $field = "id", string $order = "")
 */
class ExpressServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return ExpressService::class;
    }
}