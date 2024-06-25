<?php

namespace xsframe\service;

use think\facade\Cache;
use xsframe\base\BaseService;
use xsframe\enum\SysSettingsKeyEnum;

class SysMenuService extends BaseService
{
    // 添加后台菜单通知点
    public function addMenuNoticePointRoute(string $type = 'one', string $route = '', string $module = null)
    {
        return $this->setMenuNoticePointRoute($type, $route, $module, false);
    }

    // 清除后台菜单通知点
    public function clearMenuNoticePointRoute(string $type = 'one', string $route = '', string $module = null)
    {
        return $this->setMenuNoticePointRoute($type, $route, $module, true);
    }

    // 设置缓存菜单通知点
    private function setMenuNoticePointRoute(string $type = 'one', string $route = '', string $module = null, $isClear = false)
    {
        $route = strval($route);
        $moduleName = $module ?? $this->module;
        $key = $moduleName . "_" . SysSettingsKeyEnum::ADMIN_ONE_MENU_NOTICE_POINT;
        if ($type == 'two') {
            $key = $moduleName . "_" . SysSettingsKeyEnum::ADMIN_TWO_MENU_NOTICE_POINT;
        }

        $routeArr = Cache::get($key) ?? [];
        $routeKey = array_search($route, $routeArr);
        if ($routeKey !== false) {
            unset($routeArr[$routeKey]);
        }
        if (!$isClear) {
            $routeArr[] = $route;
        }
        Cache::set($key, $routeArr);
        $type == 'one' ? $this->oneMenuNoticePoint = $routeArr : $this->twoMenuNoticePoint = $routeArr;
        return $routeArr;
    }
}