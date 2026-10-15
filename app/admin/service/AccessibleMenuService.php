<?php

namespace app\admin\service;

use app\common\service\UserPermService;
use think\facade\Db;

/**
 * 后台可访问菜单解析服务。
 *
 * 登录入口和后台入口拦截都使用本服务，确保默认页面一定对应当前账号实际拥有的菜单查看权限。
 */
class AccessibleMenuService
{
    /**
     * 获取当前账号在指定应用中的第一个可访问页面。
     *
     * @param string $moduleName 应用目录标识
     * @param int $uniacid 商户ID
     * @param int $userId 用户ID
     * @param string $role 系统角色
     * @return string|null 可访问页面地址；没有可访问页面或菜单不存在时返回空值
     */
    public function firstUrl(string $moduleName, int $uniacid, int $userId, string $role): ?string
    {
        $moduleName = trim($moduleName);
        if ($moduleName === '' || $uniacid <= 0 || $userId <= 0) {
            return null;
        }

        $entries = $this->configuredEntries($moduleName, $uniacid);
        if (empty($entries)) {
            return null;
        }

        $merged = UserPermService::getMergedPerms($userId, $uniacid);
        $isLimited = $this->isLimitedUser($userId, $uniacid, $role);
        $appPerms = array_map('strval', (array)($merged['app_perms'] ?? []));
        $permissions = array_fill_keys(array_map('strval', (array)($merged['perms'] ?? [])), true);
        $hasApplicationPermission = !$isLimited || in_array($moduleName, $appPerms, true);

        foreach ($entries as $entry) {
            // 应用级权限直接允许进入该应用的第一个菜单页面。
            if ($hasApplicationPermission || isset($permissions[$entry['permission']])) {
                return $entry['url'];
            }
        }

        return null;
    }

    /**
     * 获取应用菜单配置中的第一个入口，不进行账号权限判断。
     *
     * 管理员兼容入口和受限账号入口都必须复用同一套路由解析规则，避免出现不同的地址拼接结果。
     */
    public function firstConfiguredUrl(string $moduleName, int $uniacid): ?string
    {
        $entries = $this->configuredEntries(trim($moduleName), $uniacid);
        return $entries[0]['url'] ?? null;
    }

    /**
     * 加载菜单配置并转换为权限键、页面地址候选项。
     *
     * @return array<int, array{permission:string,url:string}>
     */
    private function configuredEntries(string $moduleName, int $uniacid): array
    {
        if ($moduleName === '' || $uniacid <= 0) {
            return [];
        }

        $menuConfigFile = IA_ROOT . '/app/' . $moduleName . '/config/menu.php';
        if (!is_file($menuConfigFile)) {
            return [];
        }

        $menuConfig = include $menuConfigFile;
        if (!is_array($menuConfig) || empty($menuConfig)) {
            return [];
        }

        return $this->buildEntries($moduleName, $uniacid, $menuConfig);
    }

    /**
     * 判断账号是否启用了权限限制。
     *
     * owner、manager 保持后台原有全量访问行为；operator 以商户权限表中的 is_limit 为准。
     */
    private function isLimitedUser(int $userId, int $uniacid, string $role): bool
    {
        if (in_array($role, ['owner', 'manager', 'founder'], true)) {
            return false;
        }

        $permUser = Db::name('sys_account_perm_user')
            ->where('uid', $userId)
            ->where('uniacid', $uniacid)
            ->where('status', 1)
            ->find();

        // 找不到权限记录时不能放开菜单，避免登录跳转绕过权限限制。
        return !$permUser || (int)$permUser['is_limit'] === 1;
    }

    /**
     * 按菜单配置顺序生成可访问页面候选项。
     *
     * @param array<string, mixed> $menuConfig 应用菜单配置
     * @return array<int, array{permission:string,url:string}>
     */
    private function buildEntries(string $moduleName, int $uniacid, array $menuConfig): array
    {
        $entries = [];
        foreach ($menuConfig as $parentKey => $menu) {
            if (!is_array($menu) || ($menu['perm'] ?? null) === false) {
                continue;
            }

            // 没有子菜单的顶级菜单也要区分 route 和 url，route=index 代表当前菜单控制器下的 index 方法。
            if (empty($menu['items'])) {
                $parentKey = (string)$parentKey;
                if (array_key_exists('route', $menu)) {
                    $route = (string)$menu['route'];
                    $routeKey = $this->routeKey($route);
                    $permissionKey = $routeKey === '' ? $parentKey : $parentKey . '.' . $routeKey;
                    $target = $this->routeTarget($parentKey, $route);
                } else {
                    $route = (string)($menu['url'] ?? '');
                    $permissionKey = $parentKey;
                    $target = $this->urlTarget($route);
                }
                $this->appendEntry($entries, $moduleName, $uniacid, $permissionKey, $target);
                continue;
            }

            foreach ((array)$menu['items'] as $item) {
                if (!is_array($item) || ($item['perm'] ?? null) === false) {
                    continue;
                }

                if (!empty($item['items'])) {
                    foreach ((array)$item['items'] as $child) {
                        if (!is_array($child) || ($child['perm'] ?? null) === false) {
                            continue;
                        }
                        $parentKey = (string)$parentKey;
                        if (array_key_exists('route', $child)) {
                            $route = (string)$child['route'];
                            $target = $this->routeTarget($parentKey, $route);
                        } else {
                            $route = (string)($child['url'] ?? '');
                            $target = $this->urlTarget($route);
                        }
                        $routeKey = $this->routeKey($route);
                        $permissionKey = $routeKey === '' ? $parentKey : $parentKey . '.' . $routeKey;
                        $this->appendEntry($entries, $moduleName, $uniacid, $permissionKey, $target);
                    }
                    continue;
                }

                $parentKey = (string)$parentKey;
                if (array_key_exists('route', $item)) {
                    $route = (string)$item['route'];
                    $target = $this->routeTarget($parentKey, $route);
                } else {
                    $route = (string)($item['url'] ?? '');
                    $target = $this->urlTarget($route);
                }
                $routeKey = $this->routeKey($route);
                $permissionKey = $routeKey === '' ? $parentKey : $parentKey . '.' . $routeKey;
                $this->appendEntry($entries, $moduleName, $uniacid, $permissionKey, $target);
            }
        }
        return $entries;
    }

    /**
     * 将菜单地址转换为权限键和后台页面地址。
     *
     * @param array<int, array{permission:string,url:string}> $entries 候选项
     */
    private function appendEntry(array &$entries, string $moduleName, int $uniacid, string $permissionKey, string $target): void
    {
        $target = ltrim(trim($target), '/');
        if ($target === '') {
            return;
        }

        $entries[] = [
            'permission' => $moduleName . '.web.' . trim($permissionKey, '.') . '.main',
            'url' => webUrl('/' . $moduleName . '/' . $target, ['i' => $uniacid]),
        ];
    }

    /**
     * 将菜单 route 转为 ThinkPHP 多应用控制器/方法路径。
     *
     * route=index 对应 web.dashboard/index；route=notice/main 对应 web.apply.notice/main。
     */
    private function routeTarget(string $parentKey, string $route): string
    {
        $route = trim($route, " /\t\r\n");
        if ($route === '') {
            return '';
        }
        if (strpos($route, 'web.') === 0) {
            return $route;
        }

        $parentKey = trim($parentKey, " .\t\r\n");
        if (strpos($route, '/') === false) {
            return 'web.' . $parentKey . '/' . $route;
        }
        return 'web.' . $parentKey . '.' . $route;
    }

    /** 将菜单 url 转为带 web 前缀的控制器/方法路径。 */
    private function urlTarget(string $url): string
    {
        $url = ltrim(trim($url), '/');
        if ($url === '') {
            return '';
        }
        return strpos($url, 'web.') === 0 ? $url : 'web.' . $url;
    }

    /** 提取菜单地址中的控制器键，保留 invoice.rule 这类多级控制器标识。 */
    private function routeKey(string $route): string
    {
        $route = trim($route, " /\t\r\n");
        if (strpos($route, 'web.') === 0) {
            $route = substr($route, 4);
        }
        $parts = explode('/', $route, 2);
        return trim((string)($parts[0] ?? ''), '.');
    }
}
