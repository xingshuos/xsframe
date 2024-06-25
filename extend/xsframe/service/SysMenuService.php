<?php

namespace xsframe\service;

use think\facade\Cache;
use xsframe\base\BaseService;
use xsframe\enum\SysSettingsKeyEnum;

class SysMenuService extends BaseService
{
    // 设置后台菜单通知点
    public function setMenuNoticePointRoute(string $type = 'one', string $route = '')
    {
        return $this->setCacheMenuNoticePointRoute($type, $route, false);
    }

    // 清除后台菜单通知点
    public function clearMenuNoticePointRoute(string $type = 'one', string $route = '')
    {
        return $this->setCacheMenuNoticePointRoute($type, $route, true);
    }

    private function setCacheMenuNoticePointRoute(string $type = 'one', string $route = '', $isClear = false)
    {
        $route = strval($route);
        $key = $this->module . "_" . SysSettingsKeyEnum::ADMIN_ONE_MENU_NOTICE_POINT;
        if ($type == 'two') {
            $key = $this->module . "_" . SysSettingsKeyEnum::ADMIN_TWO_MENU_NOTICE_POINT;
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