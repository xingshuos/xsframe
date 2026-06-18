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

namespace app\admin\controller;

use xsframe\base\AdminBaseController;
use xsframe\enum\UserRoleKeyEnum;
use xsframe\facade\service\DbServiceFacade;
use xsframe\facade\wrapper\PermFacade;
use xsframe\util\RandomUtil;
use think\facade\Db;

class Perm extends AdminBaseController
{
    protected $uniacid;

    // 用户列表
    public function user()
    {
        $keyword = $this->params['keyword'] ?? '';
        $status = $this->params['status'] ?? '';
        $roleid = $this->params['roleid'] ?? '';

        $condition = [
            'pu.uniacid' => $this->uniacid,
            'pu.deleted' => 0,
        ];

        if (is_numeric($status)) {
            $condition['u.status'] = $status;
        }

        if (is_numeric($roleid)) {
            $condition['pu.roleid'] = $roleid;
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" pu.realname like '%" . trim($keyword) . "%' or pu.mobile like '%" . trim($keyword) . "%' ");
        }

        $field = " pu.*,u.username,u.password,u.status ";
        $list = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where($condition)->order('pu.id desc')->page($this->pIndex, $this->pSize)->select()->toArray();
        $total = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as $key => $item) {
            $roleInfo = Db::name('sys_account_perm_role')->where(['id' => $item['roleid']])->find();
            $list[$key]['rolename'] = $roleInfo['rolename'] ?? '';
            $list[$key]['role_key'] = $roleInfo['role_key'] ?? '';
        }
        unset($item);

        $roles = Db::name("sys_account_perm_role")->field('id,rolename')->where(['uniacid' => $this->uniacid, 'deleted' => 0])->order('id desc')->select();

        $result = [
            'list'   => $list,
            'pager'  => $pager,
            'total'  => $total,
            'status' => $status,
            'roles'  => $roles,
        ];

        return $this->template('perm/user/list', $result);
    }

    public function userPost()
    {
        $id = intval($this->params['id']);

        $field = " pu.*,u.username,u.password,u.status ";
        $item = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where(['pu.id' => $id])->find();
        $role = Db::name("sys_account_perm_role")->where(['id' => $item['roleid']])->find();
        $memberInfo = Db::name('sys_member')->field("id,nickname,realname,realname realname1,avatar,mobile")->where(['id' => $item['mid']])->find();
        $memberInfo['avatar'] = tomedia($memberInfo['avatar']);
        if (!empty($item)) {
            $item['end_time'] = Db::name('sys_users')->where(['id' => $item['uid']])->value('end_time');
        }

        if (empty($memberInfo['realname1'])) {
            $memberInfo['realname1'] = $memberInfo['nickname'];
        }

        if ($this->request->isPost()) {
            $username = trim($this->params['username'] ?? '');
            $password = trim($this->params['password'] ?? '');
            $realname = trim($this->params['realname'] ?? '');
            $mobile = trim($this->params['mobile'] ?? '');
            $status = intval($this->params['status'] ?? 0);
            $roleId = trim($this->params['roleid'] ?? 0);
            $mid = intval($this->params['mid'] ?? 0);
            $isLimit = intval($this->params['is_limit'] ?? 0);
            $app_perms = $this->params['app_perms'] ?? [];
            $end_time = strval($this->params['end_time']);
            $limit_time = intval($this->params['limit_time']);

            if (empty($username)) {
                $this->error('登录账号不能为空');
            }

            $currentUser = $this->adminSession;
            if ($currentUser['role'] == UserRoleKeyEnum::OPERATOR_KEY) {
                $this->error('您暂时没有权限操作');
            }

            if (!empty($password) || empty($item['id'])) {
                if (strlen($password) < 8) {
                    show_json(0, '密码长度至少8位');
                }

                $score = 0;

                if (preg_match('/[0-9]+/', $password)) {
                    ++$score;
                }

                if (preg_match('/[a-z]+/', $password)) {
                    ++$score;
                }

                if (preg_match('/[A-Z]+/', $password)) {
                    ++$score;
                }

                if (preg_match('/[_|\\-|+|=|*|!|@|#|$|%|^|&|(|)]+/', $password)) {
                    ++$score;
                }

                if ($score < 2) {
                    show_json(0, '密码必须包含大小写字母、数字、标点符号的其中两项');
                }
            }

            $data = [
                'uniacid'   => $this->uniacid,
                'realname'  => $realname,
                'mobile'    => $mobile,
                'roleid'    => $roleId,
                'status'    => $status,
                'mid'       => $mid,
                'is_limit'  => $isLimit,
                'app_perms' => implode(",", $app_perms),
            ];

            $data['perms'] = trim($this->params['permsarray']);
            $permsArray = explode(",", $this->params['permsarray']);
            $permsArray = array_merge($app_perms, $permsArray);

            $salt = RandomUtil::random(6);

            if (!empty($item['id'])) {
                Db::name('sys_account_perm_user')->where(['id' => $item['id']])->update($data);

                $userUpdateData = ['status' => $data['status']];
                if (!empty($password)) {
                    $password = md5($password . $salt);
                    $userUpdateData['password'] = $password;
                    $userUpdateData['salt'] = $salt;
                }

                $userUpdateData['end_time'] = 0;
                if ($limit_time > 0) {
                    $userUpdateData['end_time'] = strtotime($end_time);
                }

                Db::name('sys_users')->where(['id' => $item['uid']])->update($userUpdateData);

                $accountUsersInfo = Db::name('sys_account_users')->where(['user_id' => $item['uid'], 'uniacid' => $this->uniacid])->find();
                $userAccountData = [
                    'uniacid' => $this->uniacid,
                    'user_id' => $item['uid'],
                ];
                if (!empty($permsArray[0])) {
                    $userAccountData['module'] = $permsArray[0];
                }
                if (!empty($accountUsersInfo)) {
                    Db::name('sys_account_users')->where(['user_id' => $item['uid'], 'uniacid' => $this->uniacid])->update(['module' => $permsArray[0]]);
                } else {
                    Db::name('sys_account_users')->insert($userAccountData);
                }
            } else {
                $data['createtime'] = time();

                $sysUserInfo = Db::name('sys_users')->where(['username' => $username])->find();
                if (!empty($sysUserInfo)) {
                    $this->error('此用户为系统存在用户，无法添加');
                } else {
                    $password = md5($password . $salt);
                    $userData = [
                        'username'   => $username,
                        'password'   => $password,
                        'salt'       => $salt,
                        'role'       => UserRoleKeyEnum::OPERATOR_KEY,
                        'status'     => $status,
                        'createtime' => time(),
                    ];

                    $userData['end_time'] = 0;
                    if ($limit_time > 0) {
                        $userData['end_time'] = strtotime($end_time);
                    }

                    $userId = Db::name('sys_users')->insertGetId($userData);
                    $data['uid'] = $userId;

                    $permsArray = explode(",", $this->params['permsarray']);

                    $userAccountData = [
                        'uniacid' => $this->uniacid,
                        'user_id' => $userId,
                    ];

                    if (!empty($permsArray[0])) {
                        $userAccountData['module'] = $permsArray[0];
                    } else {
                        if ($roleId > 0) {
                            $roleInfo = Db::name("sys_account_perm_role")->field("perms")->where(['id' => $roleId])->find();
                            $rolePerms = explode(',', $roleInfo['perms']);
                            $userAccountData = [
                                'user_id' => $userId,
                                'uniacid' => $this->uniacid,
                                'module'  => $rolePerms[0] ?? '',
                            ];
                            Db::name('sys_account_users')->insert($userAccountData);
                        }
                    }

                    Db::name('sys_account_users')->insert($userAccountData);
                }

                Db::name('sys_account_perm_user')->insert($data);
            }
            $this->success(['url' => webUrl('perm/userPost', ['id' => $id, 'module' => $this->params['module']])]);
        }

        $perms = PermFacade::formatPerms($this->uniacid);
        // dd($perms);

        $operatorPerms = []; // 当前用户权限
        $accountsPerms = []; // 排除系统应用

        if ($this->adminSession['role'] == 'operator') {
            $operator = Db::name('sys_account_perm_user')->field('perms')->where(['uid' => $this->userId, 'uniacid' => $this->uniacid])->find();
            $operatorPerms = explode(',', $operator['perms']);
        }

        $rolePerms = [];
        $roleAppPerms = [];
        $userPerms = [];
        $appPerms = [];

        if (!empty($item)) {
            if (!empty($item['roleid'])) {
                $roleInfo = Db::name('sys_account_perm_role')->field('perms,app_perms')->where(['id' => $item['roleid']])->find();
                $rolePerms = explode(',', $roleInfo['perms']);
                $roleAppPerms = explode(',', $roleInfo['app_perms']);
            }
            $userPerms = explode(',', $item['perms']);
            $appPerms = explode(',', $item['app_perms']);
        }

        // dump($rolePerms);
        // dump($userPerms);
        // dump($roleAppPerms);
        // die;

        return $this->template('perm/user/post', [
            'item'           => $item,
            'role'           => $role,
            'perms'          => $perms,
            'operator_perms' => $operatorPerms,
            'accounts_perms' => $accountsPerms,
            'role_perms'     => $rolePerms,
            'role_app_perms' => $roleAppPerms,
            'user_perms'     => $userPerms,
            'app_perms'      => $appPerms,
            'memberInfo'     => $memberInfo,
        ]);
    }

    public function userDelete()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, ["message" => "参数错误"]);
        }

        $items = Db::name("sys_account_perm_user")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_account_perm_user")->where("id", '=', $item['id'])->update(['deleted' => 1]);
            Db::name("sys_users")->where("id", '=', $item['uid'])->update(['status' => 0, 'deleted' => 1]);
        }
        $this->success(["url" => referer()]);
    }

    public function userChange()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, ["message" => "参数错误"]);
        }

        $type = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $items = Db::name("sys_account_perm_user")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_users")->where("id", '=', $item['uid'])->update([$type => $value]);
            if ($type == 'status') {
                Db::name("sys_account_perm_user")->where("id", '=', $item['id'])->update([$type => $value]);
            }
        }

        $this->success();
    }

    /*角色管理 - 树形列表*/
    public function role()
    {
        $keyword = $this->params['keyword'] ?? '';
        $status = $this->params['status'] ?? '';

        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        if (is_numeric($status)) {
            $condition['status'] = $status;
        }

        if (!empty($keyword)) {
            $condition[] = ['rolename', 'like', '%' . trim($keyword) . '%'];
        }

        // 获取所有角色数据（不分页，便于构建树形结构）
        $allRoles = Db::name("sys_account_perm_role")
            ->field('*')
            ->where($condition)
            ->order('displayorder desc, id desc')
            ->select()
            ->toArray();

        // 构建树形扁平列表（带层级和父级名称）
        $treeList = $this->buildRoleTree($allRoles);
        $total = count($allRoles);

        // 保留分页变量但不实际分页（角色数量通常较少）
        $pager = '';

        // 统计每个角色的员工数量
        foreach ($treeList as &$row) {
            $row['usercount'] = Db::name("sys_account_perm_user")
                ->where(['roleid' => $row['id'], 'deleted' => 0])
                ->count();
        }

        $result = [
            'list'   => $treeList,
            'pager'  => $pager,
            'total'  => $total,
            'status' => $status,
        ];

        return $this->template('perm/role/list', $result);
    }

    /**
     * 构建角色树形扁平数组（带层级和父级名称）
     * @param array $roles 所有角色
     * @param int $parentId 父级ID
     * @param int $level 层级深度
     * @return array
     */
    private function buildRoleTree($roles, $parentId = 0, $level = 0)
    {
        $tree = [];
        // 先按displayorder排序
        usort($roles, function ($a, $b) {
            if ($a['displayorder'] == $b['displayorder']) {
                return $a['id'] < $b['id'] ? 1 : -1;
            }
            return $a['displayorder'] < $b['displayorder'] ? 1 : -1;
        });

        foreach ($roles as $role) {
            if ($role['pid'] == $parentId) {
                $role['level'] = $level;
                // 获取父级角色名称
                if ($role['pid'] > 0) {
                    $parentRole = Db::name("sys_account_perm_role")
                        ->where(['id' => $role['pid'], 'deleted' => 0])
                        ->field('rolename')
                        ->find();
                    $role['parent_rolename'] = $parentRole ? $parentRole['rolename'] : '顶级角色';
                } else {
                    $role['parent_rolename'] = '顶级角色';
                }
                $tree[] = $role;
                // 递归获取子角色
                $children = $this->buildRoleTree($roles, $role['id'], $level + 1);
                $tree = array_merge($tree, $children);
            }
        }
        return $tree;
    }

    /**
     * 获取树形角色选项（用于下拉选择）
     * @param int $excludeId 排除的ID（编辑时排除自身及后代）
     * @return array
     */
    private function getRoleTreeOptions($excludeId = 0)
    {
        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];
        $allRoles = Db::name("sys_account_perm_role")
            ->field('id, pid, rolename')
            ->where($condition)
            ->order('displayorder desc, id desc')
            ->select()
            ->toArray();

        // 如果需要排除某个角色及其后代，先收集要排除的ID
        $excludeIds = [];
        if ($excludeId > 0) {
            $excludeIds = $this->getRoleDescendantIds($allRoles, $excludeId);
            $excludeIds[] = $excludeId;
        }

        // 构建树形选项
        $options = $this->buildRoleOptions($allRoles, 0, 0, $excludeIds);
        return $options;
    }

    /**
     * 获取角色的所有后代ID
     * @param array $roles 所有角色
     * @param int $parentId 父级ID
     * @return array
     */
    private function getRoleDescendantIds($roles, $parentId)
    {
        $ids = [];
        foreach ($roles as $role) {
            if ($role['pid'] == $parentId) {
                $ids[] = $role['id'];
                $ids = array_merge($ids, $this->getRoleDescendantIds($roles, $role['id']));
            }
        }
        return $ids;
    }

    /**
     * 构建下拉选项数组
     * @param array $roles 所有角色
     * @param int $parentId 父级ID
     * @param int $level 层级
     * @param array $excludeIds 排除的ID
     * @return array
     */
    private function buildRoleOptions($roles, $parentId = 0, $level = 0, $excludeIds = [])
    {
        $options = [];
        // 排序
        usort($roles, function ($a, $b) {
            if ($a['displayorder'] == $b['displayorder']) {
                return $a['id'] < $b['id'] ? 1 : -1;
            }
            return $a['displayorder'] < $b['displayorder'] ? 1 : -1;
        });

        foreach ($roles as $role) {
            if ($role['pid'] == $parentId && !in_array($role['id'], $excludeIds)) {
                $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $level) . ($level > 0 ? '├─ ' : '');
                $options[$role['id']] = $prefix . $role['rolename'];
                $children = $this->buildRoleOptions($roles, $role['id'], $level + 1, $excludeIds);
                foreach ($children as $childId => $childName) {
                    $options[$childId] = $childName;
                }
            }
        }
        return $options;
    }

    public function rolePost()
    {
        $id = $this->params['id'] ?? 0;
        $item = Db::name('sys_account_perm_role')->where(['id' => $id])->find();

        if ($this->request->isPost()) {
            $app_perms = $this->params['app_perms'] ?? [];
            $pid = intval($this->params['pid'] ?? 0);
            $rolename = trim($this->params['rolename']);
            $role_key = trim($this->params['role_key'] ?? '');
            $status = intval($this->params['status']);
            $permsarray = trim($this->params['permsarray'] ?? '');
            $app_perms_str = implode(",", $app_perms);

            // 角色标识校验
            if (empty($role_key)) {
                $this->error('角色标识不能为空');
            }
            if (strlen($role_key) > 64) {
                $this->error('角色标识长度不能超过64个字符');
            }
            // 唯一性校验：同一 uniacid 下，role_key 必须唯一（排除自身）
            $exists = Db::name('sys_account_perm_role')
                ->where('uniacid', $this->uniacid)
                ->where('role_key', $role_key)
                ->where('deleted', 0)
                ->when($id > 0, function ($query) use ($id) {
                    return $query->where('id', '<>', $id);
                })
                ->count();
            if ($exists) {
                $this->error('角色标识已存在，请使用其他标识');
            }

            // 校验上级角色合法性（不能是自身及后代）
            if ($pid > 0) {
                if ($id > 0 && $pid == $id) {
                    $this->error('上级角色不能是自身');
                }
                if ($id > 0) {
                    $allRoles = Db::name("sys_account_perm_role")
                        ->where(['uniacid' => $this->uniacid, 'deleted' => 0])
                        ->field('id, pid')
                        ->select()
                        ->toArray();
                    $descendantIds = $this->getRoleDescendantIds($allRoles, $id);
                    if (in_array($pid, $descendantIds)) {
                        $this->error('上级角色不能是当前角色的子角色');
                    }
                }
            }

            $data = [
                'uniacid'   => $this->uniacid,
                'pid'       => $pid,
                'rolename'  => $rolename,
                'role_key'  => $role_key,
                'status'    => $status,
                'perms'     => $permsarray,
                'app_perms' => $app_perms_str,
            ];

            if (!empty($id)) {
                Db::name('sys_account_perm_role')->where(['id' => $id])->update($data);
            } else {
                $id = Db::name('sys_account_perm_role')->insertGetId($data);
            }
            $this->success(['url' => webUrl('perm/rolePost', ['id' => $id, 'module' => $this->params['module']])]);
        }

        $perms = PermFacade::formatPerms($this->uniacid);

        $operatorPerms = []; // 当前用户权限
        $accountsPerms = []; // 排除系统应用

        if ($this->adminSession['role'] == 'operator') {
            $operator = Db::name('sys_account_perm_user')->field('perms')->where(['uid' => $this->userId, 'uniacid' => $this->uniacid])->find();
            $operatorPerms = explode(',', $operator['perms']);
        }

        $rolePerms = [];
        $roleAppPerms = [];
        $userPerms = [];
        $appPerms = [];

        if (!empty($item)) {
            $rolePerms = explode(',', $item['perms']);
            $roleAppPerms = explode(',', $item['app_perms']);
            $userPerms = $rolePerms;
            $appPerms = explode(',', $item['app_perms']);
            $item['is_limit'] = 1;
        }

        // 获取树形角色选项（编辑时排除自身及后代）
        $excludeId = $id ?: 0;
        $roleOptions = $this->getRoleTreeOptions($excludeId);
        // 添加顶级角色选项
        $roleOptions = [0 => '顶级角色'] + $roleOptions;

        return $this->template('perm/role/post', [
            'item'           => $item,
            'perms'          => $perms,
            'operator_perms' => $operatorPerms,
            'accounts_perms' => $accountsPerms,
            'role_perms'     => $rolePerms,
            'role_app_perms' => $roleAppPerms,
            'user_perms'     => $userPerms,
            'app_perms'      => $appPerms,
            'roleOptions'    => $roleOptions,
        ]);
    }

    public function roleDelete()
    {
        $id = intval($this->params["id"]);
        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, ["message" => "参数错误"]);
        }

        // 检查是否有子角色
        $hasChildren = Db::name("sys_account_perm_role")
            ->where(['pid' => $id, 'uniacid' => $this->uniacid, 'deleted' => 0])
            ->count();
        if ($hasChildren > 0) {
            show_json(0, ["message" => "该角色下存在子角色，请先删除子角色"]);
        }

        // 检查是否有员工使用该角色
        $hasUsers = Db::name("sys_account_perm_user")
            ->where(['roleid' => $id, 'uniacid' => $this->uniacid, 'deleted' => 0])
            ->count();
        if ($hasUsers > 0) {
            show_json(0, ["message" => "该角色下存在员工，请先移除员工或修改员工角色"]);
        }

        $items = Db::name("sys_account_perm_role")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_account_perm_role")->where("id", '=', $item['id'])->update(['deleted' => 1]);
        }
        $this->success(["url" => referer()]);
    }

    // 操作日志
    public function oplog(): \think\response\View
    {
        // 基础条件（只查当前商户）
        $condition = [
            'l.uniacid' => $this->uniacid,
        ];

        // 时间段搜索
        if (!empty($this->params['searchtime']) && is_array($this->params['time'])) {
            $startTime = strtotime($this->params['time']['start']);
            $endTime = strtotime($this->params['time']['end']);
            if ($startTime && $endTime) {
                $condition['l.create_time'] = Db::raw("between {$startTime} and {$endTime}");
            }
        }

        // 操作账号搜索（支持用户名）
        if (!empty($this->params['username'])) {
            $condition['u.username'] = ['like', '%' . trim($this->params['username']) . '%'];
        }

        // 操作路径搜索
        if (!empty($this->params['path'])) {
            $condition['l.path'] = ['like', '%' . trim($this->params['path']) . '%'];
        }

        // IP搜索
        if (!empty($this->params['ip'])) {
            $condition['l.ip'] = trim($this->params['ip']);
        }

        // 模块搜索
        if (!empty($this->params['module'])) {
            $condition['l.module'] = trim($this->params['module']);
        }

        // 联表查询（获取真实姓名和手机号）
        $list = Db::name('sys_log')->alias('l')
            ->leftJoin('sys_users u', 'l.user_id = u.id')
            ->leftJoin('sys_account_perm_user pu', 'u.id = pu.uid AND pu.uniacid = ' . $this->uniacid)
            ->field('l.*, u.username, pu.realname, pu.mobile')
            ->where($condition)
            ->order('l.id desc')
            ->page($this->pIndex, $this->pSize)
            ->select()
            ->toArray();

        // 总记录数
        $total = Db::name('sys_log')->alias('l')
            ->leftJoin('sys_users u', 'l.user_id = u.id')
            ->where($condition)
            ->count();

        $pager = pagination2($total, $this->pIndex, $this->pSize);

        // 获取模块列表（用于下拉搜索）
        $moduleList = Db::name("sys_account_modules")->alias('am')
            ->leftJoin('sys_modules m', 'am.module = m.identifie')
            ->where(['am.uniacid' => $this->uniacid])
            ->where(['m.status' => 1, 'm.is_install' => 1, 'm.is_deleted' => 0])
            ->select()->toArray();

        // 格式化数据
        if (!empty($list)) {
            foreach ($list as &$item) {
                // 路径截断
                $item['path_short'] = strlen($item['path']) > 50 ? substr($item['path'], 0, 50) . '...' : $item['path'];
                // 模块名称
                $item['module_name'] = Db::name('sys_modules')->where('identifie', $item['module'])->value('name');
                // 如果未关联到真实姓名，则用用户名替代
                if (empty($item['realname'])) {
                    $item['realname'] = $item['username'];
                }
            }
            unset($item);
        }

        // 默认时间范围（最近7天）
        $startTime = empty($this->params['time']) ? strtotime('-7 days') : strtotime($this->params['time']['start']);
        $endTime = empty($this->params['time']) ? time() : strtotime($this->params['time']['end']);

        $result = [
            'list'       => $list,
            'total'      => $total,
            'pager'      => $pager,
            'moduleList' => $moduleList,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        return $this->template('oplog', $result);
    }

    /**
     * 删除操作日志（支持按天数批量删除）
     */
    public function oplogDelete()
    {
        // 权限检查（仅允许管理员操作）
        if (!in_array($this->adminSession['role'], ['founder', 'manager', 'owner'])) {
            show_json(0, ['message' => '您没有权限执行此操作']);
        }

        $days = intval($this->params['days'] ?? 0);
        if ($days <= 0) {
            show_json(0, ['message' => '请选择有效的删除天数']);
        }

        // 计算删除截止时间戳
        $endTime = time() - ($days * 86400);

        // 删除指定时间之前的日志（仅限当前商户）
        $deletedCount = Db::name('sys_log')
            ->where('uniacid', $this->uniacid)
            ->where('create_time', '<', $endTime)
            ->delete();

        if ($deletedCount === false) {
            show_json(0, ['message' => '删除失败，请重试']);
        }

        show_json(1, [
            'message' => "成功删除 {$deletedCount} 条操作日志（{$days}天前）",
            'count'   => $deletedCount
        ]);
    }

    public function roleChange()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, ["message" => "参数错误"]);
        }

        $type = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $items = Db::name("sys_account_perm_role")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_account_perm_role")->where("id", '=', $item['id'])->update([$type => $value]);
        }

        $this->success();
    }

    public function roleQuery()
    {
        $keyword = trim($this->params['keyword']);

        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" rolename like '%" . trim($keyword) . "%' ");
        }

        $ds = Db::name("sys_account_perm_role")->where($condition)->select();

        $result = [
            'ds' => $ds
        ];
        return $this->template('perm/role/query', $result);
    }

    // 查询会员
    public function memberQuery(): \think\response\View
    {
        $kwd = trim($this->params['keyword']);

        $where = [
            'is_deleted' => 0
        ];

        if (!empty($kwd)) {
            $where[] = ['nickname|mobile|realname|username', 'like', '%' . $kwd . '%'];
        }

        $list = Db::name('sys_member')->field("id,avatar,nickname,mobile,realname,realname realname1,username")->where($where)->select()->toArray();
        $list = set_medias($list, ['avatar']);

        foreach ($list as &$row) {
            if (empty($row['realname1'])) {
                $row['realname1'] = $row['nickname'];
            }
        }

        $result = [
            'list' => $list
        ];

        return $this->template('perm/member/query', $result);
    }
}