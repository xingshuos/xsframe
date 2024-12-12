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
use xsframe\util\RequestUtil;

class College extends AdminBaseController
{
    // 文档教程
    public function document(): \think\response\View
    {
        $result = $this->getList(4);
        return $this->template('document', $result);
    }

    // 应用开发
    public function develop(): \think\response\View
    {
        $result = $this->getList(2);
        return $this->template('develop', $result);
    }

    // 使用反馈
    public function feedback(): \think\response\View
    {
        $result = $this->getList(1);
        return $this->template('feedback', $result);
    }

    // 优化建议
    public function optimize(): \think\response\View
    {
        $result = $this->getList(3);
        return $this->template('optimize', $result);
    }

    // 开源交流
    public function exchange(): \think\response\View
    {
        $result = $this->getList(6);
        return $this->template('exchange', $result);
    }

    // 创客学堂
    public function affiliate(): \think\response\View
    {
        $result = $this->getList(5);
        return $this->template('affiliate', $result);
    }

    // 应用玩法
    public function guide(): \think\response\View
    {
        $result = $this->getList(7);
        return $this->template('guide', $result);
    }

    // 系统图标
    public function icon()
    {
        return $this->template('icon');
    }

    private function getList($cateId): array
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $list = [];
        $total = 0;

        $testUrl = null;
        // $testUrl = "http://www.xsframe.com";
        $cloudResult = RequestUtil::cloudHttpPost("college/list", ['key' => $key, 'token' => $token, 'cateid' => $cateId, 'page' => $this->pIndex], null, $testUrl);
        if ($cloudResult['code'] == 200) {
            $list = $cloudResult['data']['list'];
            $total = $cloudResult['data']['total'];
        }

        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $result = [
            'list'  => $list,
            'total' => $total,
            'pager' => $pager,
        ];
        return $result;
    }
}
