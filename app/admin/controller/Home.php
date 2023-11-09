<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;


use think\facade\Db;

class Home extends Base
{
    public function index()
    {
        return $this->welcome();
    }

    public function welcome()
    {
        $accountTotal    = Db::name('sys_account')->where(['deleted' => 0])->count();
        $moduleTotal     = Db::name('sys_modules')->where(['is_deleted' => 0, 'status' => 1, 'is_install' => 1])->count();
        $userTotal       = Db::name('sys_users')->where(['status' => 1, 'deleted' => 0, 'role' => 'owner'])->count();
        $userModuleTotal = Db::name('sys_users')->where(['status' => 1, 'deleted' => 0, 'role' => 'manager'])->count();

        $result = [
            'accountTotal'    => $accountTotal,
            'moduleTotal'     => $moduleTotal,
            'userTotal'       => $userTotal,
            'userModuleTotal' => $userModuleTotal,
        ];
        return $this->template('welcome', $result);
    }

    public function icon()
    {
        return $this->template('icon');
    }

}