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

use think\facade\Cache;
use xsframe\base\AdminBaseController;
use xsframe\util\FileUtil;

class Ops extends AdminBaseController
{

    // 系统概况
    public function overview(): \think\response\View
    {
        // if ($this->request->isPost()) {
        //
        // }

        $result = [
        ];
        return $this->template('overview', $result);
    }

    // 性能优化
    public function optimize(): \think\response\View
    {
        if ($this->request->isPost()) {

        }

        $result = [
        ];
        return $this->template('optimize', $result);
    }

    // 操作日志
    public function oplog(): \think\response\View
    {
        // if ($this->request->isPost()) {
        //
        // }

        $result = [
        ];
        return $this->template('oplog', $result);
    }

    // 数据库优化
    public function database(): \think\response\View
    {
        // if ($this->request->isPost()) {
        //
        // }

        $result = [
        ];
        return $this->template('database', $result);
    }

    // 更新缓存
    public function cache(): \think\response\View
    {
        if ($this->request->isPost()) {
            $this->success("更新缓存成功！");
        }

        $result = [
        ];
        return $this->template('cache', $result);
    }

    // 检测bom
    public function bom()
    {
        $bomTree = Cache::get('bomTree');

        if ($this->request->isPost()) {
            $path = $this->iaRoot;
            $trees = FileUtil::fileTree($path);
            $bomTree = [];
            foreach ($trees as $tree) {
                $tree = str_replace($path, '', $tree);
                $tree = str_replace('\\', '/', $tree);
                if (strexists($tree, '.php')) {
                    $fname = $path . $tree;
                    $fp = fopen($fname, 'r');
                    if (!empty($fp)) {
                        $bom = fread($fp, 3);
                        fclose($fp);
                        if ($bom == "\xEF\xBB\xBF") {
                            $bomTree[] = $tree;
                        }
                    }
                }
            }
            Cache::set('bomTree', $bomTree);
            show_json(1, ['url' => url('ops/bom')]);
        }

        $result = [
            'bomTree' => $bomTree
        ];
        return $this->template('bom', $result);
    }
}