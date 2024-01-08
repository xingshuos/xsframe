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

namespace xsframe\enum;

use xsframe\base\BaseEnum;

class UserRoleKeyEnum extends BaseEnum
{

    # 超级管理员
    const OWNER_KEY = "owner";

    # 项目管理员
    const MANAGER_KEY = 'manager';

    # 普通管理员
    const OPERATOR_KEY = 'operator';
}