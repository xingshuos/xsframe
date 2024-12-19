<?php

namespace app\xs_form\controller\api;

use xsframe\base\ApiBaseController;

class Index extends ApiBaseController
{
    public function index(): \think\response\Json
    {
        $result = [
            'name' => '张三',
        ];
        return $this->success($result);
    }
}
