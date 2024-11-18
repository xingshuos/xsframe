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
    // 文档教程
    public function document(): \think\response\View
    {
        return $this->template('document');
    }

    // 应用开发
    public function develop(): \think\response\View
    {
        return $this->template('develop');
    }

    // 使用反馈
    public function feedback(): \think\response\View
    {
        return $this->template('feedback');
    }

    // 优化建议
    public function optimize(): \think\response\View
    {
        return $this->template('optimize');
    }

    // 开源交流
    public function exchange(): \think\response\View
    {
        return $this->template('exchange');
    }

    // 创客学堂
    public function affiliate(): \think\response\View
    {
        return $this->template('affiliate');
    }

    // 应用玩法
    public function guide(): \think\response\View
    {
        return $this->template('guide');
    }

    // 系统图标
    public function icon()
    {
        return $this->template('icon');
    }
}