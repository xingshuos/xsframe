<?php

namespace app\store\controller\pc;

use app\store\facade\service\AdvServiceFacade;
use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\MenuServiceFacade;
use app\store\facade\service\TeacherServiceFacade;
use think\App;
use think\facade\Db;

class Index extends Base
{
    public function index()
    {
        $result = [];
        return $this->template('pc/index', $result);
    }

    public function search()
    {
        $keyword  = $this->params['wd'] ?? '';
        $menuList = $this->getMenuList($keyword);

        $result = [
            'keyword'  => $keyword,
            'menuList' => $menuList,
        ];
        return $this->template('pc/search/index', $result);
    }

    private function getMenuList($keyword = null)
    {
        $menuList = MenuServiceFacade::getList(['uniacid' => $this->uniacid, 'type' => 1, 'status' => 1, 'enabled' => 1], "id,title,thumb,link", "displayorder desc,id desc");
        $menuList = set_medias($menuList, ['thumb']);

        $condition = ['uniacid' => $this->uniacid, 'status' => 1, 'deleted' => 0];
        if (!empty($keyword)) {
            $condition[''] = Db::raw(" ( title like '%" . trim($keyword) . "%' or goodssn like '%" . trim($keyword) . "%' ) ");
        }

        foreach ($menuList as $key => &$item) {
            $total = 0;
            switch ($key) {
                case 0: // 名家典藏
                    $item['styleType']    = 1;
                    $condition['tags_id'] = 1;
                    $total                = GoodsServiceFacade::getTotal($condition);
                    break;
                case 1: // 限量版
                    $item['styleType']    = 2;
                    $condition['tags_id'] = 2;
                    $total                = GoodsServiceFacade::getTotal($condition);
                    break;
                case 2: // 鹿隐版
                    $item['styleType']    = 3;
                    $condition['tags_id'] = 3;
                    $total                = GoodsServiceFacade::getTotal($condition);
                    break;
                case 3: // 雅集
                    $item['styleType']    = 4;
                    $condition['tags_id'] = 4;
                    $total                = GoodsServiceFacade::getTotal($condition);
                    break;
                case 4: // 艺术家
                    $item['styleType'] = 5;
                    $item['tags_id']   = 0;
                    unset($condition['tags_id']);
                    $condition = ['uniacid' => $this->uniacid, 'enabled' => 1, 'is_deleted' => 0];
                    if (!empty($keyword)) {
                        $condition[''] = Db::raw(" ( name like '%" . trim($keyword) . "%' or sub_name like '%" . trim($keyword) . "%' ) ");
                    }
                    $total = TeacherServiceFacade::getTotal($condition);
                    break;
            }
            $item['total'] = $total;
        }
        unset($item);
        return $menuList;
    }

    public function __call($method, $args)
    {
        $this->index();
    }
}