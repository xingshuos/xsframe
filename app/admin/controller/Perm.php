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
use xsframe\facade\wrapper\PermFacade;
use xsframe\util\RandomUtil;
use think\facade\Db;

class Perm extends AdminBaseController
{
    /**
     * 员工列表角色列默认展开显示的角色个数，超出部分折叠为「+N」
     * 取值依据：现网人均关联 2 个角色，2 个以内可完整平铺不撑破列宽
     */
    const ROLE_DISPLAY_LIMIT = 2;

    protected $uniacid;

    /**
     * 批量获取用户的全部关联角色
     * 关联表 sys_account_user_role.uid 语义 = sys_users.id（与 sys_account_perm_user.uid 一致）
     *
     * @param array $uids sys_users.id 集合
     * @return array uid => [ ['id'=>..,'rolename'=>..,'role_key'=>..], ... ]
     */
    private function getUserRolesMap(array $uids): array
    {
        $uids = array_values(array_unique(array_filter(array_map('intval', $uids))));
        if (empty($uids)) {
            return [];
        }

        $rows = Db::name('sys_account_user_role')->alias('ur')
            ->field('ur.uid, r.id, r.rolename, r.role_key')
            ->join('sys_account_perm_role r', 'r.id = ur.roleid AND r.uniacid = ur.uniacid')
            ->where('ur.uniacid', $this->uniacid)
            ->whereIn('ur.uid', $uids)
            ->where('r.deleted', 0)
            ->order('r.displayorder desc, r.id desc')
            ->select()
            ->toArray();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['uid']][] = [
                'id'       => $row['id'],
                'rolename' => $row['rolename'],
                'role_key' => $row['role_key'],
            ];
        }

        return $map;
    }

    /**
     * 获取单个用户已关联的角色ID集合（无关联记录时回退到主角色字段，兼容历史数据）
     *
     * @param int $uid           sys_users.id
     * @param int $primaryRoleId sys_account_perm_user.roleid
     * @return array
     */
    private function getUserRoleIds(int $uid, int $primaryRoleId = 0): array
    {
        $roleIds = [];
        if ($uid > 0) {
            $roleIds = Db::name('sys_account_user_role')
                ->where(['uniacid' => $this->uniacid, 'uid' => $uid])
                ->column('roleid');
            $roleIds = array_values(array_unique(array_filter(array_map('intval', $roleIds))));
        }

        if (empty($roleIds) && $primaryRoleId > 0) {
            $roleIds = [$primaryRoleId];
        }

        return $roleIds;
    }

    /**
     * 同步用户多角色关联（差异化增删，依赖唯一索引 uk_user_role 避免重复）
     *
     * @param int   $uid     sys_users.id
     * @param array $roleIds 本次提交的角色ID集合（已校验合法）
     * @return void
     */
    private function syncUserRoles(int $uid, array $roleIds): void
    {
        if ($uid <= 0) {
            return;
        }

        $roleIds = array_values(array_unique(array_filter(array_map('intval', $roleIds))));

        $oldRoleIds = Db::name('sys_account_user_role')
            ->where(['uniacid' => $this->uniacid, 'uid' => $uid])
            ->column('roleid');
        $oldRoleIds = array_values(array_unique(array_map('intval', $oldRoleIds)));

        // 移除本次未选中的角色
        $removeIds = array_diff($oldRoleIds, $roleIds);
        if (!empty($removeIds)) {
            Db::name('sys_account_user_role')
                ->where(['uniacid' => $this->uniacid, 'uid' => $uid])
                ->whereIn('roleid', $removeIds)
                ->delete();
        }

        // 新增本次新选中的角色
        $insertIds = array_diff($roleIds, $oldRoleIds);
        if (!empty($insertIds)) {
            $insertData = [];
            foreach ($insertIds as $roleId) {
                $insertData[] = [
                    'uniacid' => $this->uniacid,
                    'uid'     => $uid,
                    'roleid'  => $roleId,
                ];
            }
            Db::name('sys_account_user_role')->insertAll($insertData);
        }
    }

    /**
     * 过滤出当前商户下合法（未删除）的角色ID，并保持提交顺序
     *
     * @param array $roleIds
     * @return array
     */
    private function filterValidRoleIds(array $roleIds): array
    {
        $roleIds = array_values(array_unique(array_filter(array_map('intval', $roleIds))));
        if (empty($roleIds)) {
            return [];
        }

        $validIds = Db::name('sys_account_perm_role')
            ->where(['uniacid' => $this->uniacid, 'deleted' => 0, 'status' => 1])
            ->whereIn('id', $roleIds)
            ->column('id');
        $validIds = array_map('intval', $validIds);

        // 保持前端提交顺序，第一个作为主角色
        return array_values(array_filter($roleIds, function ($roleId) use ($validIds) {
            return in_array($roleId, $validIds, true);
        }));
    }

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

        // 原生条件片段：think-orm 中只有空字符串键才会被当作原生表达式，非空键会被解析为函数名，
        // 因此角色筛选与关键字筛选必须合并到同一个空键中。
        $rawConditions = [];

        // 角色筛选：命中关联表中的任一角色，或命中历史主角色字段（兼容未迁移数据）
        if (is_numeric($roleid)) {
            $roleidInt = intval($roleid);
            $userRoleTable = Db::name('sys_account_user_role')->getTable();
            $rawConditions[] = "(pu.roleid = {$roleidInt} OR EXISTS (SELECT 1 FROM `{$userRoleTable}` ur WHERE ur.uniacid = pu.uniacid AND ur.uid = pu.uid AND ur.roleid = {$roleidInt}))";
        }

        if (!empty($keyword)) {
            $keywordStr = trim($keyword);
            $rawConditions[] = "(pu.realname like '%{$keywordStr}%' or pu.mobile like '%{$keywordStr}%')";
        }

        if (!empty($rawConditions)) {
            $condition[''] = Db::raw(' ' . implode(' AND ', $rawConditions) . ' ');
        }

        $field = " pu.*,u.username,u.password,u.status ";
        $list = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where($condition)->order('pu.id desc')->page($this->pIndex, $this->pSize)->select()->toArray();
        $total = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        // 批量获取每个用户的全部关联角色（避免 N+1 查询）
        $userRolesMap = $this->getUserRolesMap(array_column($list, 'uid'));

        foreach ($list as $key => $item) {
            $roleInfo = Db::name('sys_account_perm_role')->where([
                'id' => $item['roleid'],
                'uniacid' => $this->uniacid,
                'deleted' => 0,
            ])->find();
            // rolename / role_key 保持原语义：主角色，供其它引用继续使用
            $list[$key]['rolename'] = $roleInfo['rolename'] ?? '';
            $list[$key]['role_key'] = $roleInfo['role_key'] ?? '';

            // 全部关联角色；无关联记录时回退主角色，兼容历史数据
            $roles = $userRolesMap[$item['uid']] ?? [];
            if (empty($roles) && !empty($roleInfo)) {
                $roles = [[
                    'id'       => $roleInfo['id'],
                    'rolename' => $roleInfo['rolename'],
                    'role_key' => $roleInfo['role_key'],
                ]];
            }
            $list[$key]['roles'] = $roles;
            // 列表展示切片：前 N 个直接平铺，其余折叠，避免角色过多撑破表格列宽
            $list[$key]['roles_visible'] = array_slice($roles, 0, self::ROLE_DISPLAY_LIMIT);
            $list[$key]['roles_hidden']  = array_slice($roles, self::ROLE_DISPLAY_LIMIT);
            // 悬浮提示文本：完整角色名（含 role_key），供 title 属性使用
            $roleTips = [];
            foreach ($roles as $roleTipItem) {
                $roleTips[] = $roleTipItem['rolename']
                    . (!empty($roleTipItem['role_key']) ? '(' . $roleTipItem['role_key'] . ')' : '');
            }
            $list[$key]['roles_tip'] = implode('、', $roleTips);
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
        $item = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where(['pu.id' => $id, 'pu.uniacid' => $this->uniacid, 'pu.deleted' => 0])->find();
        // 当前员工已关联的全部角色ID（无关联记录时回退主角色，兼容历史数据）
        $selectedRoleIds = $this->getUserRoleIds(intval($item['uid'] ?? 0), intval($item['roleid'] ?? 0));
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
            // 多角色：roleid 提交为数组，第一个作为主角色写回 sys_account_perm_user.roleid
            $roleIds = $this->params['roleid'] ?? [];
            if (!is_array($roleIds)) {
                $roleIds = $roleIds === '' ? [] : [$roleIds];
            }
            $roleIds = $this->filterValidRoleIds($roleIds);
            $roleId = $roleIds[0] ?? 0;
            // 编辑多角色员工时优先保留原主角色，避免浏览器按角色选项展示顺序提交后意外更换主角色。
            $currentPrimaryRoleId = intval($item['roleid'] ?? 0);
            if ($currentPrimaryRoleId > 0 && in_array($currentPrimaryRoleId, $roleIds, true)) {
                $roleId = $currentPrimaryRoleId;
            }
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
                // 强密码校验
                $passwordCheck = $this->checkStrongPassword($password, $username);
                if ($passwordCheck !== true) {
                    show_json(0, $passwordCheck);
                }
            }

            // ★ 核心：perms 只存用户手动勾选的权限，角色权限完全不保存
            // permsarray = 角色权限（disabled）+ 用户手动勾选 → 只取差集 = 用户额外权限
            $savedPermsRaw = array_filter(explode(',', $this->params['permsarray'] ?? ''));
            // 多角色：角色权限取所有已选角色的并集
            $rolePermsArray = [];
            if (!empty($roleIds)) {
                // 角色权限必须限定当前租户及有效状态，避免跨租户角色被混入权限并集。
                $rolePermsList = Db::name('sys_account_perm_role')
                    ->where(['uniacid' => $this->uniacid, 'deleted' => 0, 'status' => 1])
                    ->whereIn('id', $roleIds)
                    ->column('perms');
                foreach ($rolePermsList as $rolePermsStr) {
                    $rolePermsArray = array_merge($rolePermsArray, array_filter(explode(',', $rolePermsStr ?? '')));
                }
                $rolePermsArray = array_values(array_unique($rolePermsArray));
            }
            // 差集：用户勾选的 - 角色的 = 用户自己额外勾选的（只存这部分）
            $userExtraPerms = array_values(array_diff($savedPermsRaw, $rolePermsArray));

            $data = [
                'uniacid'   => $this->uniacid,
                'realname'  => $realname,
                'mobile'    => $mobile,
                'roleid'    => $roleId,
                'status'    => $status,
                'mid'       => $mid,
                'is_limit'  => $isLimit,
                'perms'     => implode(',', $userExtraPerms),  // 只存用户额外权限
                'app_perms' => implode(',', $app_perms),
            ];

            // 兼容后面 module 取第一个权限点
            $permsArray = $savedPermsRaw;

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

                // 同步多角色关联
                $this->syncUserRoles(intval($item['uid']), $roleIds);
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
                            $roleInfo = Db::name("sys_account_perm_role")->field("perms")->where([
                                'id' => $roleId,
                                'uniacid' => $this->uniacid,
                                'deleted' => 0,
                                'status' => 1,
                            ])->find();
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

                // 同步多角色关联（新增场景 uid 来自新建的 sys_users 记录）
                $this->syncUserRoles(intval($data['uid'] ?? 0), $roleIds);
            }

            // $this->success(['url' => webUrl('perm/userPost', ['id' => $id, 'module' => $this->params['module']])]);
            $this->success(['url' => webUrl('perm/user')]);
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
            // 多角色：角色权限取所有已关联角色的并集
            if (!empty($selectedRoleIds)) {
                $roleInfoList = Db::name('sys_account_perm_role')->field('perms,app_perms')->where([
                    'uniacid' => $this->uniacid,
                    'deleted' => 0,
                    'status' => 1,
                ])->whereIn('id', $selectedRoleIds)->select()->toArray();
                foreach ($roleInfoList as $roleInfo) {
                    $rolePerms = array_merge($rolePerms, array_filter(explode(',', $roleInfo['perms'] ?? '')));
                    $roleAppPerms = array_merge($roleAppPerms, array_filter(explode(',', $roleInfo['app_perms'] ?? '')));
                }
                $rolePerms = array_values(array_unique($rolePerms));
                $roleAppPerms = array_values(array_unique($roleAppPerms));
            }
            // 用户自己额外勾选的权限（perms 字段，保存时已排除角色权限）
            $userPerms = array_filter(explode(',', $item['perms'] ?? ''));
            $appPerms = array_filter(explode(',', $item['app_perms'] ?? ''));
        }

        // dump($rolePerms);
        // dump($userPerms);
        // dump($roleAppPerms);
        // die;

        return $this->template('perm/user/post', [
            'item'            => $item,
            'selectedRoleIds' => $selectedRoleIds, // 已关联的全部角色ID
            'roleOptions'    => Db::name('sys_account_perm_role')->where(['uniacid' => $this->uniacid, 'deleted' => 0, 'status' => 1])->field('id,rolename,perms')->order('displayorder desc, id desc')->select()->toArray(),
            'perms'          => $perms,
            'operator_perms' => $operatorPerms,
            'accounts_perms' => $accountsPerms,
            'role_perms'     => $rolePerms,
            'role_app_perms' => $roleAppPerms,
            'user_perms'     => $userPerms,    // 用户额外权限（已排除角色权限）
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
        $role_key = $this->params['role_key'] ?? '';

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

        // 角色标识搜索
        if (!empty($role_key)) {
            $condition[] = ['role_key', 'like', '%' . trim($role_key) . '%'];
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

        // 预加载所有角色标识数据（避免N+1查询）
        $roleKeyNames = Db::name('sys_account_role_key')
            ->where(['uniacid' => $this->uniacid, 'deleted' => 0])
            ->column('name', 'role_key');

        // 获取角色标识选项列表（用于下拉搜索）
        $roleKeyOptions = Db::name('sys_account_role_key')
            ->where('uniacid', $this->uniacid)
            ->where('status', 1)
            ->where('deleted', 0)
            ->order('id asc')
            ->field('id, name, role_key')
            ->select()
            ->toArray();

        // 统计每个角色的员工数量
        foreach ($treeList as &$row) {
            $row['usercount'] = Db::name("sys_account_perm_user")
                ->where(['roleid' => $row['id'], 'deleted' => 0])
                ->count();
            // 关联的角色标识名称（角色表 role_key = 标识表 role_key）
            $rk = trim((string)($row['role_key'] ?? ''));
            $row['flag_name'] = $rk !== '' ? ($roleKeyNames[$rk] ?? '') : '';
        }
        unset($row);

        $result = [
            'list'          => $treeList,
            'pager'         => $pager,
            'total'         => $total,
            'status'        => $status,
            'role_key'      => $role_key,
            'roleKeyOptions'=> $roleKeyOptions,
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
            // 角色标识：角色表 role_key 直接对应 sys_account_role_key.role_key（可留空）

            // 角色标识（role_key）校验：允许为空、允许跨角色复用，仅校验长度
            if (strlen($role_key) > 64) {
                $this->error('角色标识长度不能超过64个字符');
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

            // $this->success(['url' => webUrl('perm/rolePost', ['id' => $id, 'module' => $this->params['module']])]);
            $this->success(['url' => webUrl('perm/role')]);
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

        // 角色标识选项（来自 sys_account_role_key 表，不再写死在代码中）
        $flagOptions = Db::name('sys_account_role_key')
            ->where('uniacid', $this->uniacid)
            ->where('status', 1)
            ->where('deleted', 0)
            ->order('id asc')
            ->field('id, name, role_key')
            ->select()
            ->toArray();

        // 当前角色已选中的标识 role_key（一对一，角色表 role_key 对应标识表 role_key）
        $currentRoleKey = !empty($item) ? trim((string)($item['role_key'] ?? '')) : '';

        return $this->template('perm/role/post', [
            'item'           => $item,
            'perms'          => $perms,
            'operator_perms' => $operatorPerms,
            'accounts_perms' => $accountsPerms,
            'role_perms'     => $rolePerms,
            'role_app_perms' => $roleAppPerms,
            'user_perms'     => $userPerms,
            'app_perms'      => $appPerms,
            'roleOptions'     => $roleOptions,
            'flagOptions'     => $flagOptions,
            'currentRoleKey'  => $currentRoleKey,
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

        // 检查是否有员工使用该角色（主角色字段）
        $hasUsers = Db::name("sys_account_perm_user")
            ->where(['roleid' => $id, 'uniacid' => $this->uniacid, 'deleted' => 0])
            ->count();
        if ($hasUsers > 0) {
            show_json(0, ["message" => "该角色下存在员工，请先移除员工或修改员工角色"]);
        }

        // 检查是否有员工通过多角色关联表使用该角色（仅统计未删除的员工）
        $hasRelUsers = Db::name("sys_account_user_role")->alias('ur')
            ->join('sys_account_perm_user pu', 'pu.uid = ur.uid AND pu.uniacid = ur.uniacid')
            ->where(['ur.roleid' => $id, 'ur.uniacid' => $this->uniacid, 'pu.deleted' => 0])
            ->count();
        if ($hasRelUsers > 0) {
            show_json(0, ["message" => "该角色已分配给员工（多角色关联），请先移除员工或修改员工角色"]);
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

    // 角色标识管理 - 列表
    public function rolekey(): \think\response\View
    {
        // 获取搜索参数
        $name = trim($this->params['name'] ?? '');
        $role_key = trim($this->params['role_key'] ?? '');
        $status = $this->params['status'] ?? '';

        // 基础条件（只查当前商户）
        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        // 标识名称模糊搜索
        if (!empty($name)) {
            $condition[] = ['name', 'like', '%' . $name . '%'];
        }

        // 标识键值模糊搜索
        if (!empty($role_key)) {
            $condition[] = ['role_key', 'like', '%' . $role_key . '%'];
        }

        // 状态搜索
        if (is_numeric($status)) {
            $condition['status'] = intval($status);
        }

        // 查询列表
        $list = Db::name('sys_account_role_key')
            ->where($condition)
            ->order('displayorder desc, id desc')
            ->page($this->pIndex, $this->pSize)
            ->select()
            ->toArray();

        // 格式化创建时间
        foreach ($list as &$item) {
            $item['createtime'] = !empty($item['createtime']) ? date('Y-m-d H:i', $item['createtime']) : '--';
        }
        unset($item);

        // 总记录数
        $total = Db::name('sys_account_role_key')
            ->where($condition)
            ->count();

        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $result = [
            'list'      => $list,
            'total'     => $total,
            'pager'     => $pager,
            'name'      => $name,
            'role_key'  => $role_key,
            'status'    => $status,
        ];

        return $this->template('perm/rolekey/list', $result);
    }

    // 角色标识管理 - 添加/编辑
    public function rolekeyPost()
    {
        $id = intval($this->params['id'] ?? 0);
        // 编辑仅允许访问当前商户的有效记录，避免更新其他商户或已删除的标识。
        $item = Db::name('sys_account_role_key')->where(['id' => $id, 'uniacid' => (int)$this->uniacid, 'deleted' => 0])->find();
        if ($id > 0 && !$item) {
            $this->error('角色标识不存在或已删除');
        }

        if ($this->request->isPost()) {
            $name = trim((string)($this->params['name'] ?? ''));
            $role_key = trim((string)($this->params['role_key'] ?? ''));
            $description = trim((string)($this->params['description'] ?? ''));
            $displayorder = intval($this->params['displayorder'] ?? 0);
            $status = intval($this->params['status'] ?? 1);

            // 验证标识名称
            if (empty($name)) {
                $this->error('标识名称不能为空');
            }

            // 验证标识键值
            if (empty($role_key)) {
                $this->error('标识键值不能为空');
            }

            // 标识键值格式验证（只允许字母、数字、下划线、横线）
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $role_key)) {
                $this->error('标识键值只能包含字母、数字、下划线和横线');
            }

            // 标识键值长度验证
            if (strlen($role_key) > 64) {
                $this->error('标识键值长度不能超过64个字符');
            }

            // 检查标识键值是否重复（同一商户下）
            // 编辑时：如果role_key未修改则跳过检查；如果修改了则检查是否与其他记录重复
            if ($id > 0 && !empty($item)) {
                // 编辑模式：role_key未变化时不检查
                if ($role_key !== $item['role_key']) {
                    $condition = [
                        'uniacid'  => $this->uniacid,
                        'role_key' => $role_key,
                    ];
                    // 唯一键包含已删除记录，编辑时也必须检查全部占用记录。
                    $exists = Db::name('sys_account_role_key')->where($condition)->where('id', '<>', $id)->find();
                    if ($exists) {
                        $this->error('该标识键值已存在，请使用其他键值');
                    }
                }
            } else {
                // 新增模式：包含软删除记录，命中后复用原记录而非再次插入。
                $condition = [
                    'uniacid'  => $this->uniacid,
                    'role_key' => $role_key,
                ];
                $exists = Db::name('sys_account_role_key')->where($condition)->find();
                if ($exists && (int)$exists['deleted'] === 0) {
                    $this->error('该标识键值已存在，请使用其他键值');
                }
            }

            $data = [
                'uniacid'     => (int)$this->uniacid,
                'name'        => $name,
                'role_key'    => $role_key,
                'description' => $description,
                'displayorder'=> $displayorder,
                'status'      => $status,
            ];

            if (!empty($id)) {
                // 编辑
                $data['updatetime'] = time();
                Db::name('sys_account_role_key')->where(['id' => $id, 'uniacid' => (int)$this->uniacid, 'deleted' => 0])->update($data);
            } elseif ($exists) {
                // 恢复同商户同键的软删除记录，更新本次表单内容并保留原 ID、创建时间。
                $data['deleted'] = 0;
                $data['updatetime'] = time();
                $restored = Db::name('sys_account_role_key')
                    ->where(['id' => (int)$exists['id'], 'uniacid' => (int)$this->uniacid, 'deleted' => 1])
                    ->update($data);
                // 并发恢复只允许一次成功，避免覆盖另一个请求已经恢复的有效记录。
                if (!$restored) {
                    $this->error('该标识已恢复或状态已变化，请刷新后重试');
                }
            } else {
                // 新增
                $data['createtime'] = time();
                $data['updatetime'] = time();
                Db::name('sys_account_role_key')->insert($data);
            }

            $this->success(['url' => webUrl('perm/rolekey')]);
        }

        return $this->template('perm/rolekey/post', [
            'item' => $item,
        ]);
    }

    // 角色标识管理 - 删除
    public function rolekeyDelete()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, ["message" => "参数错误"]);
        }

        // 检查是否有角色正在使用该标识
        $roleUsing = Db::name("sys_account_perm_role")
            ->where(['role_key' => Db::name('sys_account_role_key')->where(['id' => $id])->value('role_key'), 'uniacid' => $this->uniacid, 'deleted' => 0])
            ->count();
        if ($roleUsing > 0) {
            show_json(0, ["message" => "该标识已被角色使用，请先修改角色的标识后再删除"]);
        }

        $items = Db::name("sys_account_role_key")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_account_role_key")->where("id", '=', $item['id'])->update(['deleted' => 1]);
        }

        $this->success(["url" => referer()]);
    }

    // 角色标识管理 - 状态切换
    public function rolekeyChange()
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

        // 只允许修改的字段
        $allowedFields = ['status', 'displayorder'];
        if (!in_array($type, $allowedFields)) {
            show_json(0, ["message" => "不允许修改的字段"]);
        }

        $items = Db::name("sys_account_role_key")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_account_role_key")->where("id", '=', $item['id'])->update([$type => $value]);
        }

        $this->success();
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

    /**
     * 校验强密码
     * @param string $password 密码
     * @param string $username 用户名（可选，用于校验密码不能包含用户名）
     * @return bool|string 返回 true 表示密码强度合格，返回字符串表示错误信息
     */
    private function checkStrongPassword($password, $username = '')
    {
        // 1. 长度校验：至少10位
        if (strlen($password) < 10) {
            return '密码过于简单，长度至少10位';
        }

        // 2. 必须包含大写字母、小写字母、数字、特殊字符中的至少3项
        $score = 0;

        if (preg_match('/[a-z]/', $password)) {
            ++$score;
        }

        if (preg_match('/[A-Z]/', $password)) {
            ++$score;
        }

        if (preg_match('/[0-9]/', $password)) {
            ++$score;
        }

        if (preg_match('/[_\-\+\=\*\!\@\#\$\%\^\&\(\)\[\]\{\}\;\:\"\'\,\.\<\>\/\?\|\\\\`~]/', $password)) {
            ++$score;
        }

        if ($score < 3) {
            return '密码过于简单，必须包含小写字母、大写字母、数字、特殊字符中的至少3项';
        }

        // 3. 不能包含连续3个以上相同字符
        if (preg_match('/(.)\1{2,}/', $password)) {
            return '密码过于简单，不能包含连续3个以上相同字符';
        }

        // 4. 不能包含常见弱密码
        $weakPasswords = [
            '123456', '123456789', 'qwerty', 'password', '111111',
            '12345678', 'abc123', '1234567', 'password1', '12345',
            '1234567890', '123123', 'admin', 'administrator'
        ];

        if (in_array(strtolower($password), $weakPasswords)) {
            return '密码过于简单，不能使用常见弱密码';
        }

        // 5. 不能包含用户名（如果提供了用户名）
        if (!empty($username) && stripos($password, $username) !== false) {
            return '密码过于简单，不能包含账号名称';
        }

        // 6. 检查字符重复性（不能有超过6个相同字符）
        $charCount = [];
        $passwordChars = str_split($password);
        foreach ($passwordChars as $char) {
            if (!isset($charCount[$char])) {
                $charCount[$char] = 0;
            }
            $charCount[$char]++;
            if ($charCount[$char] > 6) {
                return '密码过于简单，单个字符不能出现超过6次';
            }
        }

        return true;
    }

}
