<?php

namespace app\xs_cloud\controller\api;

use app\xs_cloud\facade\service\AppServiceFacade;
use app\xs_cloud\facade\service\MemberAppServiceFacade;

class Apps extends Base
{

    // 获取app列表
    public function getAppList(): \think\response\Json
    {
        $memberAppList = MemberAppServiceFacade::getAll(['mid' => $this->memberInfo['id'],'endtime'], "identifier", "id desc", "identifier");
        $memberAppListKeys = array_keys($memberAppList);
        $appList = AppServiceFacade::getAll(['identifier' => $memberAppListKeys, 'status' => 1, 'deleted' => 0]);

        foreach ($appList as &$item) {
            $item['logo'] = tomedia($item['logo']);
            unset($item['id']);
        }

        $result = [
            'appList' => $appList,
        ];

        return $this->success($result);
    }

}
