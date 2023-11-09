<?php


namespace app\store\controller\pc;

use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\UserFavoriteServiceFacade;
use app\store\facade\service\UserRecordServiceFacade;
use xsframe\exception\ApiException;

class userRecord extends Base
{
    public function index()
    {
        $status = $this->params['status'] ?? 0;

        $condition = ['mid' => $this->userId];

        if (!empty($status)) {
            switch ($status) {
                case 1:
                    $condition['type'] = 1;
                    break;
                case 2:
                    $condition['type'] = -1;
                    break;
            }
        }

        $recordList = UserRecordServiceFacade::getAll($condition, "id,type,fee,note,createtime", "createtime desc");

        foreach ($recordList as &$item) {
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
        }
        unset($item);

        $result = [
            'recordList' => $recordList,
        ];
        return $this->success($result);
    }
}