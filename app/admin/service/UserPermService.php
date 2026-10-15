<?php
namespace app\admin\service;

use think\facade\Db;

/**
 * 用户权限合并服务（多角色支持）
 *
 * 汇总某用户在「主角色(sys_account_perm_user.roleid)」+「关联表所有角色(sys_account_user_role)」
 * +「用户额外权限(sys_account_perm_user.perms / app_perms)」三者并集后的操作权限与应用权限，
 * 供登录态下的各鉴权入口（切换应用、应用管理、操作权限校验等）统一调用，
 * 避免各入口只读取单一主角色，导致多角色用户的权限被截断。
 *
 * 向后兼容：当用户在 sys_account_user_role 中无记录时，结果与原来只读取主角色完全一致。
 */
class UserPermService
{
    /**
     * 获取用户合并后的权限
     * @param int      $uid
     * @param int|null $uniacid 不传时按 uid 取第一条员工记录
     * @return array ['is_limit'=>int,'roleid'=>int,'perms'=>string[],'app_perms'=>string[]]
     */
    public static function getMergedPerms($uid, $uniacid = null)
    {
        $permUser = Db::name('sys_account_perm_user')
            ->field('id,uid,uniacid,roleid,is_limit,perms,app_perms,status')
            ->where(['uid' => $uid])
            ->when($uniacid !== null, function ($query) use ($uniacid) {
                $query->where('uniacid', $uniacid);
            })
            ->find();

        if (empty($permUser)) {
            return ['is_limit' => 1, 'roleid' => 0, 'perms' => [], 'app_perms' => []];
        }

        // 角色 id 集合：主角色 + 关联表所有角色
        $roleIds = [];
        if (!empty($permUser['roleid'])) {
            $roleIds[] = (int)$permUser['roleid'];
        }
        $roleQueryUniacid = ($uniacid !== null) ? $uniacid : $permUser['uniacid'];
        $extraRoleIds = Db::name('sys_account_user_role')
            ->where(['uid' => $uid, 'uniacid' => $roleQueryUniacid])
            ->column('roleid');
        foreach ($extraRoleIds as $rid) {
            $roleIds[] = (int)$rid;
        }
        $roleIds = array_values(array_unique(array_filter($roleIds)));

        $rolePerms = [];
        $roleAppPerms = [];
        if (!empty($roleIds)) {
            $roles = Db::name('sys_account_perm_role')
                ->field('perms,app_perms')
                ->where('id', 'in', $roleIds)
                ->select()
                ->toArray();
            foreach ($roles as $role) {
                if (!empty($role['perms'])) {
                    $rolePerms = array_merge($rolePerms, array_filter(explode(',', $role['perms'])));
                }
                if (!empty($role['app_perms'])) {
                    $roleAppPerms = array_merge($roleAppPerms, array_filter(explode(',', $role['app_perms'])));
                }
            }
        }

        $userPerms = !empty($permUser['perms']) ? array_filter(explode(',', $permUser['perms'])) : [];
        $userAppPerms = !empty($permUser['app_perms']) ? array_filter(explode(',', $permUser['app_perms'])) : [];

        return [
            'is_limit'  => (int)$permUser['is_limit'],
            'roleid'    => (int)$permUser['roleid'],
            'perms'     => array_values(array_unique(array_merge($rolePerms, $userPerms))),
            'app_perms' => array_values(array_unique(array_merge($roleAppPerms, $userAppPerms))),
        ];
    }
}
