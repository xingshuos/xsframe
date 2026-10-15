<?php

namespace app\admin\model;

use think\Model;

class PermRole extends Model
{
    protected $name = 'sys_account_perm_role';
    protected $autoWriteTimestamp = false;

    // 状态常量
    const STATUS_NORMAL = 1;
    const STATUS_DISABLED = 0;

    /**
     * 获取所有角色列表
     */
    public static function getRoleList($uniacid = 0)
    {
        $where = [
            'status' => self::STATUS_NORMAL,
            'deleted' => 0
        ];
        if ($uniacid > 0) {
            $where['uniacid'] = $uniacid;
        }

        return self::where($where)
            ->order('displayorder asc, id asc')
            ->column('rolename', 'id');
    }

    /**
     * 根据ID获取角色名称
     */
    public static function getRoleName($id)
    {
        $role = self::find($id);
        return $role ? $role->rolename : '';
    }
}