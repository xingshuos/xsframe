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
    protected $uniacid;

    // 用户列表
    public function user()
    {
        $keyword = $this->params['keyword'];
        $status = $this->params['status'];

        $condition = [
            'pu.uniacid' => $this->uniacid,
            'pu.deleted' => 0,
        ];

        if (is_numeric($status)) {
            $condition['u.status'] = $status;
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" pu.realname like '%" . trim($keyword) . "%' or pu.mobile like '%" . trim($keyword) . "%' ");
        }

        $field = " pu.*,u.username,u.password,u.status ";
        $list = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where($condition)->order('pu.id desc')->page($this->pIndex, $this->pSize)->select()->toArray();
        $total = Db::name("sys_account_perm_user")->alias('pu')->field($field)->leftJoin("sys_users u", "u.id = pu.uid")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as $key => $item) {
            $roleName = Db::name('sys_account_perm_role')->where(['id' => $item['roleid']])->value('rolename');
            $list[$key]['rolename'] = $roleName;
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

        if ($this->request->isPost()) {
            $username = trim($this->params['username'] ?? '');
            $password = trim($this->params['password'] ?? '');
            $realname = trim($this->params['realname'] ?? '');
            $mobile = trim($this->params['mobile'] ?? '');
            $status = intval($this->params['status'] ?? 0);
            $roleId = trim($this->params['roleid'] ?? 0);

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
                'uniacid'  => $this->uniacid,
                'realname' => $realname,
                'mobile'   => $mobile,
                'roleid'   => $roleId,
                'status'   => $status,
            ];

            $data['perms'] = trim($this->params['permsarray']);
            $permsArray = explode(",", $this->params['permsarray']);

            $salt = RandomUtil::random(6);

            if (!empty($item['id'])) {
                Db::name('sys_account_perm_user')->where(['id' => $item['id']])->update($data);

                $userUpdateData = ['status' => $data['status']];
                if (!empty($password)) {
                    $password = md5($password . $salt);
                    $userUpdateData['password'] = $password;
                    $userUpdateData['salt'] = $salt;
                }
                Db::name('sys_users')->where(['id' => $item['uid']])->update($userUpdateData);
                if (!empty($permsArray[0])) {
                    Db::name('sys_account_users')->where(['user_id' => $item['uid'], 'uniacid' => $this->uniacid])->update(['module' => $permsArray[0]]);
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
                    $userId = Db::name('sys_users')->insertGetId($userData);
                    $data['uid'] = $userId;

                    $permsArray = explode(",", $this->params['permsarray']);

                    if (!empty($permsArray[0])) {
                        $userAccountData = [
                            'user_id' => $userId,
                            'uniacid' => $this->uniacid,
                            'module'  => $permsArray[0],
                        ];
                        Db::name('sys_account_users')->insert($userAccountData);
                    }
                }

                Db::name('sys_account_perm_user')->insert($data);
            }
            $this->success(['url' => webUrl('perm/userPost', ['id' => $id, 'module' => $this->params['module']])]);
        }

        $perms = PermFacade::formatPerms($this->uniacid);

        $operatorPerms = []; // 当前用户权限
        $accountsPerms = []; // 排除系统应用

        if ($this->adminSession['role'] == 'operator') {
            $operator = Db::name('sys_account_perm_user')->field('perms')->where(['uid' => $this->userId, 'uniacid' => $this->uniacid])->find();
            $operatorPerms = explode(',', $operator['perms2']);
        }

        $rolePerms = [];
        $userPerms = [];

        if (!empty($item)) {
            if (!empty($item['roleid'])) {
                $roleInfo = Db::name('sys_account_perm_role')->field('perms')->where(['id' => $item['roleid']])->find();
                $rolePerms = explode(',', $roleInfo['perms']);
            }
            $userPerms = explode(',', $item['perms']);
        }

        // dump($rolePerms);
        // dump($userPerms);
        // die;

        return $this->template('perm/user/post', [
            'item'           => $item,
            'role'           => $role,
            'perms'          => $perms,
            'operator_perms' => $operatorPerms,
            'accounts_perms' => $accountsPerms,
            'role_perms'     => $rolePerms,
            'user_perms'     => $userPerms,
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

    /*角色管理*/
    public function role()
    {
        $keyword = $this->params['keyword'];
        $status = $this->params['status'];

        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        if (is_numeric($status)) {
            $condition['status'] = $status;
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" rolename like '%" . trim($keyword) . "%'");
        }

        $list = Db::name("sys_account_perm_role")->field('*')->where($condition)->order('id desc')->page($this->pIndex, $this->pSize)->select()->toArray();
        $total = Db::name("sys_account_perm_role")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$row) {
            $row['usercount'] = Db::name("sys_account_perm_user")->where(['roleid' => $row['id'], 'deleted' => 0])->count();
        }

        $result = [
            'list'   => $list,
            'pager'  => $pager,
            'total'  => $total,
            'status' => $status,
        ];

        return $this->template('perm/role/list', $result);
    }

    public function rolePost()
    {
        $id = $this->params['id'];

        $item = Db::name('sys_account_perm_role')->where(['id' => $id])->find();

        if ($this->request->isPost()) {
            $data = [
                'uniacid'  => $this->uniacid,
                'rolename' => trim($this->params['rolename']),
                'status'   => intval($this->params['status']),
                'perms'    => trim($this->params['permsarray']),
            ];
            if (!empty($id)) {
                Db::name('sys_account_perm_role')->where(['id' => $item['id']])->update($data);
            } else {
                $id = Db::name('sys_account_perm_role')->insertGetId($data);
            }
            $this->success(['url' => webUrl('perm/rolePost', ['id' => $id, 'module' => $this->params['module']])]);
        }


        $perms = PermFacade::formatPerms($this->uniacid);
        // dump($perms);
        // die;

        $operatorPerms = []; // 当前用户权限
        $accountsPerms = []; // 排除系统应用

        if ($this->adminSession['role'] == 'operator') {
            $operator = Db::name('sys_account_perm_user')->field('perms')->where(['uid' => $this->userId, 'uniacid' => $this->uniacid])->find();
            $operatorPerms = explode(',', $operator['perms2']);
        }

        $rolePerms = [];
        $userPerms = [];

        if (!empty($item)) {
            $dataPerms = $item['perms'];
            $rolePerms = explode(',', $dataPerms);
            $userPerms = $rolePerms;
        }

        // dump($rolePerms);
        // dump($userPerms);
        // die;

        return $this->template('perm/role/post', [
            'item'           => $item,
            'perms'          => $perms,
            'operator_perms' => $operatorPerms,
            'accounts_perms' => $accountsPerms,
            'role_perms'     => $rolePerms,
            'user_perms'     => $userPerms,
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

        $items = Db::name("sys_account_perm_role")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("sys_account_perm_role")->where("id", '=', $item['id'])->update(['deleted' => 1]);
        }
        $this->success(["url" => referer()]);
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

        $condition = [];
        $condition['uniacid'] = $this->uniacid;

        if (!empty($kwd)) {
            $condition[''] = Db::raw(" rolename like '%" . trim($keyword) . "%' ");
        }

        $ds = Db::name("sys_account_perm_role")->where($condition)->select();

        $result = [
            'ds' => $ds
        ];
        return $this->template('perm/role/query', $result);
    }
}