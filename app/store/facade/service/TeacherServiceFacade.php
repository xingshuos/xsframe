<?php

namespace app\store\facade\service;

use app\store\service\TeacherService;
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
 * @method static login(string $username, string $password)
 * @method static register(string $username, string $mobile, string $password)
 * @method static forget(string $mobile, string $password)
 * @method static checkLogin()
 * @method static checkMember()
 * @method static logout()
 */
class TeacherServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return TeacherService::class;
    }
}