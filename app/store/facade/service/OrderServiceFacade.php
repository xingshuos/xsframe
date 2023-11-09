<?php

namespace app\store\facade\service;

use app\store\service\OrderService;
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
 * @method static create($userId, int $id, int $type, int $total, int $addressId, int $cartIds, string $remark = '')
 * @method static getOrderList(array $condition)
 * @method static getOrderInfo($userId, $ordersn)
 * @method static payResult(int $id, int $serviceType = 1)
 */
class OrderServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return OrderService::class;
    }
}