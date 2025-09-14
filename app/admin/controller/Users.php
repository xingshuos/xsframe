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

use think\facade\Db;
use xsframe\enum\UserRoleKeyEnum;
use xsframe\facade\service\DbServiceFacade;
use xsframe\util\RandomUtil;

class Users extends Base
{
    public function index()
    {
        return redirect('/admin/users/profile');
    }

    public function profile()
    {
        if ($this->request->isPost()) {
            $username = $this->params['username'];
            $password = $this->params['password'];
            $newPassword = $this->params['newPassword'];

            $adminSession = $this->adminSession;
            $userInfo = Db::name('sys_users')->field("id,username,password,salt")->where(['id' => $adminSession['uid']])->find();
            $password = md5($password . $userInfo['salt']);
            if (md5($password . $userInfo['salt']) != $adminSession['hash']) {
                show_json(0, "原始密码错误，请重新输入");
            }
            if (strlen($newPassword) < 6) {
                show_json(0, "请输入不小于6位数的密码");
            }
            if (empty($username)) {
                show_json(0, "登录账号不能为空");
            }

            $salt = RandomUtil::random(6);
            Db::name('sys_users')->where(['id' => $userInfo['id']])->update(['username' => $username, 'password' => md5($newPassword . $salt), 'salt' => $salt]);
            show_json(1, "密码已修改请重新登录");
        }

        return $this->template('profile');
    }

    public function list()
    {
        $condition = [
            'id'      => Db::raw("> 1"),
            'deleted' => 0
        ];

        $keyword = trim($this->params['keyword']);
        if (!empty($keyword)) {
            $condition['username'] = Db::raw(" like '%" . trim($keyword) . "%'");
        }

        $role = trim($this->params['role']);
        if (!empty($role)) {
            $condition['role'] = $role;
        }

        $list = Db::name("sys_users")->where($condition)->order('id desc')->page($this->pIndex, $this->pSize)->select();
        $total = Db::name("sys_users")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $list = $list->toArray();
        foreach ($list as &$item) {
            $usersAccountInfo = Db::name("sys_account_users")->field("uniacid")->where(['user_id' => $item['id']])->find();
            $accountInfo = Db::name("sys_account")->field("uniacid,name,logo")->where(['uniacid' => $usersAccountInfo['uniacid']])->find();
            $item['account'] = $accountInfo;
        }

        $var = [
            'list'  => $list,
            'pager' => $pager,
        ];
        return $this->template('list', $var);
    }

    public function auth()
    {
        $condition = [
            'id'      => Db::raw("> 1"),
            'deleted' => 0
        ];

        $keyword = trim($this->params['keyword']);
        if (!empty($keyword)) {
            $condition['code|use_username'] = Db::raw(" like '%" . trim($keyword) . "%'");
        }

        $list = Db::name("sys_users_auth")->where($condition)->order('id desc')->page($this->pIndex, $this->pSize)->select();
        $total = Db::name("sys_users_auth")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $var = [
            'list'  => $list,
            'total' => $total,
            'pager' => $pager,
        ];
        return $this->template('auth', $var);
    }

    public function authPost()
    {
        $id = intval($this->params['id'] ?? 0);
        $uniacid = intval($this->params['uniacid'] ?? 0);

        $item = Db::name('sys_users_auth')->where(['id' => $id])->find();

        if ($this->request->isPost()) {
            $data = [
                "uniacid"  => $uniacid,
                "end_time" => strtotime($this->params["end_time"]),
            ];

            if (!empty($id)) {
                Db::name('sys_users_auth')->where(['id' => $id])->update($data);
                $this->success(["message" => "更新成功","url" => webUrl("users/auth")]);
            } else {
                $data['createtime'] = time();
                $data['code'] = RandomUtil::random(32);
                Db::name('sys_users_auth')->insertGetId($data);
                $this->success(["message" => "创建成功","url" => webUrl("users/auth")]);
            }
        }

        $var = [
            'item' => $item,
        ];
        return $this->template('authPost', $var);
    }

    public function edit()
    {
        return $this->post();
    }

    public function add()
    {
        return $this->post();
    }

    public function post()
    {
        $id = $this->params['id'];
        $uniacid = $this->params['uniacid'] ?? 0;
        $role = trim($this->params['role'] ?? '');
        $module = $this->params['module'] ?? '';

        if ($this->request->isPost()) {
            $salt = RandomUtil::random(6);
            $username = trim($this->params["username"]);
            $password = trim($this->params['password']);

            $end_time = strval($this->params['end_time']);
            $limit_time = intval($this->params['limit_time']);

            $data = [
                "username" => $username,
                "role"     => $role,
                "status"   => trim($this->params["status"]),
            ];

            $data['end_time'] = 0;
            if ($limit_time > 0) {
                $data['end_time'] = strtotime($end_time);
            }

            if (!empty($password)) {
                $data['salt'] = $salt;
                $data['password'] = md5($password . $salt);
            }
            if (!empty($id)) {
                Db::name('sys_users')->where(['id' => $id])->update($data);
            } else {
                $data['createtime'] = time();

                $isExitUser = Db::name('sys_users')->where(['username' => $username])->count();
                if ($isExitUser) {
                    $this->error('当前账号已存在，请更换管理账号');
                }

                $id = Db::name('sys_users')->insertGetId($data);
            }

            # 非超级管理员分配商户 start
            if ($role != UserRoleKeyEnum::OWNER_KEY && !empty($uniacid)) {
                $usersAccount = Db::name('sys_account_users')->where(['user_id' => $id])->count();
                $usersAccountData = [
                    'user_id' => $id,
                    'uniacid' => $uniacid,
                ];
                if (!empty($module)) {
                    $usersAccountData['module'] = $module;
                }
                if (!empty($usersAccount)) {
                    Db::name('sys_account_users')->where(['user_id' => $id])->update($usersAccountData);
                } else {
                    Db::name('sys_account_users')->insertGetId($usersAccountData);
                }
            }
            # 非超级管理员分配商户 end

            $this->success(["url" => webUrl("users/list")]);
        }

        $item = Db::name('sys_users')->where(['id' => $id])->find();
        $accountInfo = [];

        if (!empty($item)) {
            $usersAccount = Db::name('sys_account_users')->field("id,uniacid,module")->where(['user_id' => $id])->find();
            $accountInfo = Db::name('sys_account')->where(['uniacid' => $usersAccount['uniacid']])->find();
            $accountInfo['logo'] = tomedia($accountInfo['logo']);
        }
        $var = [
            'item'        => $item,
            'accountInfo' => $accountInfo,
        ];
        return $this->template('post', $var);
    }

    public function role()
    {
        return $this->template('role');
    }

    public function log()
    {
        return $this->template('log');
    }

    // 通用更新
    public function change()
    {
        $id = intval($this->params["id"]);
        if (empty($id)) {
            $id = $this->params["ids"];
        }
        if (empty($id)) {
            show_json(0, ["message" => "ID参数错误"]);
        }

        $type = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $item = Db::name('sys_users')->where(['id' => $id])->find();
        if (empty($item)) {
            show_json(0, ["message" => "参数错误"]);
        }
        Db::name('sys_users')->where(["id" => $id])->update([$type => $value]);

        $this->success();
    }

    // 登录日志
    public function login_Log()
    {
        $keyword = trim($this->params['keyword']);
        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime("-1 month");
            $endtime = time();
        }

        $condition = [
            'deleted' => 0,
        ];

        $orderBy = "logintime";
        $searchTime = trim($this->params["searchtime"]);
        if (!empty($searchTime) && is_array($this->params["time"]) && in_array($searchTime, ["login"])) {
            $starttime = strtotime($this->params["time"]["start"]);
            $endtime = strtotime($this->params["time"]["end"]);
            $condition[''] = Db::raw($searchTime . "time >= {$starttime} AND " . $searchTime . "time <= {$endtime} ");
            $orderBy = $searchTime . "time";
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" username like '%" . trim($keyword) . "%' or lastip like '%" . trim($keyword) . "%' ");
        }

        $list = DbServiceFacade::name('sys_users_log')->getList($condition, "*", "{$orderBy} desc");
        $total = DbServiceFacade::getTotal($condition);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $result = [
            'list'      => $list,
            'total'     => $total,
            'pager'     => $pager,
            'starttime' => $starttime,
            'endtime'   => $endtime,
        ];

        return $this->template('log', $result);
    }

    // 删除日志
    public function logDel()
    {
        $id = trim($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, ["message" => "参数错误"]);
        }


        $items = DbServiceFacade::name('sys_users_log')->getAll(['id' => $id]);
        foreach ($items as $item) {
            DbServiceFacade::name('sys_users_log')->updateInfo(['deleted' => 1], ['id' => $item['id']]);
        }

        $this->success();
    }
}