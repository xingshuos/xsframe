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

use think\facade\Db;

class Util extends Base
{
    public function moduleSelector($page = 0, $identifie = null)
    {
        $page       = empty($page) ? max(1, (int)$this->params['page']) : $page;
        $page_size  = 8;
        $page_start = ($page - 1) * $page_size;

        $where = array(
            'status'     => 1,
            'is_install' => 1,
            'is_deleted' => 0,
        );

        if (!empty($identifie)) {
            $where['identifie'] = $identifie;
        }

        $keywords = trim($this->params['keywords']);
        if (!empty($keywords)) {
            $where['name'] = Db::raw("like '%" . trim($keywords) . "%'");
        }

        $list  = Db::name('sys_modules')->where($where)->limit($page_start, $page_size)->select();
        $count = Db::name('sys_modules')->where($where)->count();

        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                $item['logo'] = !empty($item['logo']) ? tomedia($item['logo']) : $this->siteRoot . "/app/{$item['identifie']}/icon.png";
            }
        }

        $page_num = ceil($count / $page_size);
        $total    = $page_num;
        $i        = 1;
        while ($page_num) {
            $page_num_arr[] = $i++;
            --$page_num;
        }
        $slice = 0;
        if (6 < $page) {
            $slice = $page - 6;
        }
        is_array($page_num_arr) && ($page_num_arr = array_slice($page_num_arr, $slice, 10));

        return $this->template('module_selector', ['list' => $list, 'page_num_arr' => $page_num_arr, 'total' => $total, 'page' => $page]);
    }

    // 项目应用
    public function accountModuleSelector()
    {
        $uniacid = $this->params['uniacid'] ?? 0;

        $accountModules = Db::name('sys_account_modules')->field("module")->where(['uniacid' => $uniacid])->select()->toArray();
        $accountModules = array_column($accountModules, 'module') ?? null;

        return self::moduleSelector(0, $accountModules);
    }

    public function moduleSelectorJs()
    {
        return $this->template('module_selector_js');
    }

    /**
     * 选项编辑器
     */
    public function moduleSelectorOp()
    {
        $column = json_decode(htmlspecialchars_decode(urldecode(trim($this->params['column']))), 1);

        if (is_array($column)) {
            foreach ($column as $ck => &$c) {
                if (is_string($c)) {
                    $c = array('name' => $ck, 'title' => $c);
                } else {
                    if (is_array($c) && !empty($c['title'])) {
                        if (empty($c['name'])) {
                            $c['name'] = $ck;
                        }

                        continue;
                    }

                    show_json(0, 'column参数不合法');
                }
            }
        }

        $id     = intval($this->params['id']);
        $module = Db::name('sys_modules')->where(['id' => $id])->find();
        if (empty($module)) {
            $this->error('此应用已经不存在,请移除');
        }

        return $this->template('goods_selector_op');
    }
}