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

namespace xsframe\util;

class PermUtil
{
    public static function initPerm()
    {
        $modules = array('admin');  //模块名称
        $i = 0;
        foreach ($modules as $module) {
            $all_controller = self::getController($module);
            foreach ($all_controller as $controller) {
                $all_action = self::getAction($module, $controller);
                foreach ($all_action as $action) {
                    $controller = str_replace('Controller', '', $controller);
                    $data[$i]['module'] = strtolower($module);
                    $data[$i]['controller'] = strtolower($controller);
                    $data[$i]['action'] = strtolower($action);
                    $data[$i]['res_name'] = self::getActionName($action);

                    //入库
                    if (!empty($module) && !empty($controller) && !empty($action)) {
                        $data[$i]['res_url'] = strtolower($module . '/' . $controller . '/' . $action);
                        // $rule = db('admin_resource')->where('res_name', strtolower($data[$i]['rule']))->find();
                        // if (!$rule) {
                        // $idata = array();
                        // db('admin_resource')->insert($idata);
                        // }
                    }

                    $i++;
                }
            }
        }
        return $data;
    }

    private static function getActionName($action)
    {
        $action = strtolower($action);
        $actionArray = [
            'insert' => "添加",
            'delete' => "删除",
            'update' => "修改",
            'view' => "列表",
            'index' => "显示",
        ];
        return $actionArray[$action] ?? '其他';
    }

    private static function getController($module)
    {
        if (empty($module)) {
            return null;
        }
        $module_path = env('app_path') . '/' . $module . '/controller/';  //控制器路径
        if (!is_dir($module_path)) {
            return null;
        }

        $module_controller_files = FileUtil::dirsOnes($module_path);
        $files = [];
        foreach ($module_controller_files as $controller_files) {
            $path = $module_path . $controller_files . '/*.php';
            $ary_files = glob($path);

            foreach ($ary_files as $file) {
                if (is_dir($file)) {
                    continue;
                } else {
                    $files[] = $controller_files . "/" . basename($file, '.php');
                }
            }
        }
        return $files;
    }


    //获取所有方法名称
    protected static function getAction($module, $controller)
    {
        if (empty($controller)) {
            return null;
        }
        $customer_functions = [];
        $file = env('app_path') . $module . '/controller/' . $controller . '.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $content, $matches);
            $functions = $matches[1];
            //排除部分方法
            $inherents_functions = array('_initialize', '__construct', 'getActionName', 'isAjax', 'display', 'show', 'fetch', 'buildHtml', 'assign', '__set', 'get', '__get', '__isset', '__call', 'error', 'success', 'ajaxReturn', 'redirect', '__destruct', '_empty');
            foreach ($functions as $func) {
                $func = trim($func);
                if (!in_array($func, $inherents_functions)) {
                    $customer_functions[] = $func;
                }
            }
            return $customer_functions;
        } else {
            return false;
        }
    }
}