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
use think\facade\Db;
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

        $database = config('database');

        $result = [
            'database'         => $database,
            'redis_support'    => extension_loaded('redis'),
            'memcache_support' => extension_loaded('memcache'),
            'opcache_support'  => function_exists('opcache_get_configuration'),
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
        $table = trim($this->params['table'] ?? '');
        $type = intval($this->params['type'] ?? 1);

        // if ($this->request->isPost()) {
        //
        // }

        if (empty($table)) {
            $list = Db::query("SHOW TABLE STATUS");
        } else {
            if ($type) {
                $list = Db::query("SHOW FULL COLUMNS FROM {$table}");
            } else {
                $list = Db::query("SHOW COLUMNS FROM {$table}");
            }
        }

        $total = $total_size = 0;
        foreach ($list as $k => $v) {
            $list[$k]['Data_length'] = round($v['Data_length'] / 1024 / 1024, 3);  //数据大小
            $list[$k]['Index_length'] = round($v['Index_length'] / 1024 / 1024, 3); //索引大小
            $list[$k]['Data_free'] = round($v['Data_free'] / 1024 / 1024, 3);    //碎片大小
            $list[$k]['Data_total'] = round($list[$k]['Data_length'] + $list[$k]['Index_length'], 3); //合计
            $total_size += $list[$k]['Data_total'];
            $total++;
        }

        $result = [
            'total'     => $total,
            'list'      => $list,
            'totalSize' => round($total_size, 3),
        ];
        return $this->template('database', $result);
    }

    // 优化数据表
    public function optimizeTable()
    {
        $table = $this->request->post("table", '', 'strip_sql');
        if (!$table) return $this->errorMsg('请选择需要优化的数据表');

        if (is_array($table)) {
            $table = implode('`,`', $table);
        }

        Db::query("OPTIMIZE TABLE `{$table}`");
        return $this->successMsg('优化成功');
    }

    // 修复数据表
    public function repairTable()
    {
        $table = $this->request->post("table", '', 'strip_sql');
        if (!$table) return $this->error('请选择需要修复的数据表');

        if (is_array($table)) {
            $table = implode('`,`', $table);
        }

        Db::query("REPAIR TABLE `{$table}`");
        return $this->successMsg('修复成功');
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