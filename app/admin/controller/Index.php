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

use xsframe\enum\SysSettingsKeyEnum;

class Index extends Base
{
    public function index()
    {
        $adminSession = $_COOKIE[SysSettingsKeyEnum::ADMIN_USER_KEY] ?? '';
        if (!empty($adminSession)) {
            return redirect('/admin/home/welcome');
        }
        return redirect('/admin/login');
    }
}