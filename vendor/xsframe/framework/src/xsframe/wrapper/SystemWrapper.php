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

namespace xsframe\wrapper;

use think\facade\Cache;
use think\facade\Db;
use xsframe\enum\CacheKeyEnum;

class SystemWrapper
{
    protected $tableName = "sys_account";

    // 获取商户列表
    public function reloadUniacidList()
    {
        return self::getUniacidList(true);
    }

    // 获取商户列表
    public function getUniacidList($reload = false)
    {
        $key = CacheKeyEnum::SYSTEM_UNIACID_LIST_KEY;
        $uniacidList = Cache::get($key);

        if (empty($uniacidList) || $reload) {
            $uniacidList = Db::name('sys_account')->where(['status' => 1, 'deleted' => 0])->column('uniacid');
            Cache::set($key, $uniacidList);
        }

        return $uniacidList;
    }

    // 获取商户列表
    public function reloadDisabledUniacidList()
    {
        return self::getDisabledUniacidList(true);
    }

    // 获取商户列表
    public function getDisabledUniacidList($reload = false)
    {
        $key = CacheKeyEnum::SYSTEM_DISABLED_UNIACID_LIST_KEY;
        $disabledUniacidList = Cache::get($key);

        if (empty($disabledUniacidList) || $reload) {
            $disabledUniacidList = Db::name('sys_account')->whereOr(['status' => 0, 'deleted' => 1])->column('uniacid');
            Cache::set($key, $disabledUniacidList);
        }

        return $disabledUniacidList;
    }

    // 获取商户应用列表
    public function reloadAllModuleList()
    {
        return self::getAllModuleList(true);
    }

    // 获取商户应用列表
    public function getAllModuleList($reload = false)
    {
        $key = CacheKeyEnum::SYSTEM_MODULE_LIST_KEY;
        $moduleList = Cache::get($key);

        if (empty($moduleList) || $reload) {
            $moduleList = Db::name('sys_modules')->where(['status' => 1, 'is_install' => 1, 'is_deleted' => 0])->column('identifie');
            Cache::set($key, $moduleList);
        }

        return $moduleList;
    }

    // 获取商户应用列表
    public function reloadAccountModuleList($uniacid)
    {
        return self::getAccountModuleList($uniacid, true);
    }

    // 获取商户应用列表
    public function getAccountModuleList($uniacid, $reload = false)
    {
        $key = CacheKeyEnum::UNIACID_MODULE_LIST_KEY . "_{$uniacid}";
        $uniacidModuleList = Cache::get($key);

        if (empty($uniacidModuleList) || $reload) {
            $uniacidModuleList = Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'deleted' => 0])->column('module');
            Cache::set($key, $uniacidModuleList);
        }

        return $uniacidModuleList;
    }

}