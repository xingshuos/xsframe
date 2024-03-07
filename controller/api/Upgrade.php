<?php

namespace app\xs_cloud\controller\api;

use app\xs_cloud\facade\service\FrameVersionServiceFacade;
use xsframe\base\ApiBaseController;

class Upgrade extends ApiBaseController
{
    public function getUpgradeList(): \think\response\Json
    {
        $upgradeList = FrameVersionServiceFacade::getList(['status' => 1, 'deleted' => 0]);

        $result = [
            'list' => $upgradeList,
        ];

        return $this->success($result);
    }
}
