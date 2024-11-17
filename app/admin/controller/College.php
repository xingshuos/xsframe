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

class College extends AdminBaseController
{
    // 文章列表
    public function article()
    {
        return $this->template('article');
    }

    // 文章分类
    public function category()
    {
        return $this->template('category');
    }

    // 系统图标
    public function icon()
    {
        return $this->template('icon');
    }
}