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

use xsframe\enum\SysSettingsKeyEnum;
use think\facade\Cache;
use think\facade\Db;

class SettingsWrapper
{
    // 获取系统配置信息
    public function getSysSettings($key, $reload = false)
    {
        $sysSettings = Cache::get($key);
        if (empty($sysSettings) || $reload) {
            $sysSettings = Db::name('sys_settings')->where(['key' => $key])->value('value');
            $sysSettings = unserialize($sysSettings);
            Cache::set($key, $sysSettings);
        }

        if ($key == SysSettingsKeyEnum::WEBSITE_KEY && !empty($sysSettings['uniacid'])) {
            $sysSettings = self::getAccountSettings($sysSettings['uniacid']);
            unset($sysSettings['settings']);
        }

        return empty($sysSettings) ? [] : $sysSettings;
    }

    // 设置系统配置信息
    public function setSysSettings($key, $data = [])
    {
        $isExit = Db::name('sys_settings')->where(['key' => $key])->value('key');
        $value  = serialize($data);

        if (empty($isExit)) {
            Db::name('sys_settings')->insert(['key' => $key, 'value' => $value]);
        } else {
            Db::name('sys_settings')->where(['key' => $key])->update(['value' => $value]);
        }
        $sysSettings = $this->getSysSettings($key, true);
        return $sysSettings;
    }

    // 获取项目配置信息
    public function getAccountSettings($uniacid = 0, $key = null, $reload = false)
    {
        $accountInfo = [];
        if (!empty($uniacid)) {
            $accountInfoKey = SysSettingsKeyEnum::ACCOUNT_INFO_KEY . $uniacid;
            $accountInfo    = Cache::get($accountInfoKey);
            if (empty($accountInfo) || $reload) {
                $accountInfo = Db::name('sys_account')->where(['uniacid' => $uniacid])->find();
                if (!empty($accountInfo)) {
                    $accountInfo['settings'] = unserialize($accountInfo['settings']);
                }
                Cache::set($accountInfoKey, $accountInfo);
            }
        }

        return empty($key) ? (array)$accountInfo : (array)$accountInfo[$key];
    }

    // 重新加载项目配置信息
    public function reloadAccountSettings($uniacid)
    {
        $accountSettings = $this->getAccountSettings($uniacid, null, true);
        return $accountSettings;
    }

    // 获取模块配置信息
    public function getModuleSettings($key = null, $module = null, $uniacid = 0, $reload = false)
    {
        $moduleSetsKey  = SysSettingsKeyEnum::MODULE_SETS_KEY . $uniacid . "_" . $module;
        $moduleSettings = Cache::get($moduleSetsKey);
        if (empty($moduleSettings) || $reload) {
            $moduleSettings = Db::name('sys_account_modules')->where(['uniacid' => $uniacid, 'module' => $module])->value('settings');
            $moduleSettings = unserialize($moduleSettings);
            Cache::set($moduleSetsKey, $moduleSettings);
        }

        $result = empty($key) ? $moduleSettings : $moduleSettings[$key];
        return empty($result) ? array() : $result;
    }

    // 重新加载模块配置信息
    public function reloadModuleSettings($module = null, $uniacid = 0)
    {
        $moduleSettings = $this->getModuleSettings(null, $module, $uniacid, true);
        return $moduleSettings;
    }

    // 获取模块基本信息
    public function getModuleInfo($module, $uniacid = 0, $reload = false)
    {
        $moduleInfo = [];
        if (!in_array($module, ['admin'])) {
            $moduleInfoKey = SysSettingsKeyEnum::MODULE_INFO_KEY . $module;
            $moduleInfo    = Cache::get($moduleInfoKey);
            if (empty($moduleInfo) || $reload) {
                $moduleInfo = Db::name('sys_modules')->where(['identifie' => $module])->find();
                Cache::set($moduleInfoKey, $moduleInfo);
            }
            if ($uniacid > 0) {
                $moduleInfo['settings'] = $this->getModuleSettings(null, $module, $uniacid);
            }
        }
        return $moduleInfo;
    }

    // 重新加载模块基本信息
    public function reloadModuleInfo($module)
    {
        return $this->getModuleInfo($module, 0, true);
    }
}