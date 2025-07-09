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
use xsframe\service\ZiShuAiService;

class Util extends Base
{
    // 地图组件视图
    public function map()
    {
        return $this->template('map');
    }

    // 紫薯AI生成文字内容
    public function chatText()
    {
        $content = trim($this->params['content'] ?? '');
        $prompt = trim($this->params['prompt'] ?? '');
        $maxLength = trim($this->params['max_length'] ?? 0);

        $promptContent = "将" . (!empty($prompt) ? "这段文字“" . $prompt . "”" : '') . "内容提炼优化,";
        if (!empty($maxLength)) {
            $promptContent .= "限制长度必须不超过" . $maxLength . "个字符，";
        }

        $promptContent .= " 去空格，去换行，直接给我最终答案 只需要一条结果，不要任何备注信息，必须返回最终结果。不需要其他任何备注信息，例如最终结果类似：星数引擎AI智能体开发利器-星数为来科技，以此为标准返回";

        $params = ['prompt' => $promptContent];
        $contentNew = $content;

        try {
            $contentNew = (new ZiShuAiService($this->uniacid))->chatText($content, $params);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return $this->success(['content' => $contentNew]);
    }

    public function moduleSelector($page = 0, $identifie = null)
    {
        $page = empty($page) ? max(1, (int)$this->params['page']) : $page;
        $page_size = 8;
        $page_start = ($page - 1) * $page_size;

        $where = [
            'status'     => 1,
            'is_install' => 1,
            'is_deleted' => 0,
        ];

        if (!empty($identifie)) {
            $where['identifie'] = $identifie;
        }

        $keywords = trim($this->params['keywords']);
        if (!empty($keywords)) {
            $where[] = ['name|identifie|author|version|ability|description', 'like', '%' . trim($keywords) . '%'];
        }

        $list = Db::name('sys_modules')->where($where)->order('update_time desc,id desc')->limit($page_start, $page_size)->select();
        $count = Db::name('sys_modules')->where($where)->count();

        if (!empty($list)) {
            $list = $list->toArray();
            foreach ($list as &$item) {
                $item['logo'] = !empty($item['logo']) ? tomedia($item['logo']) : $this->siteRoot . "/app/{$item['identifie']}/icon.png";
            }
        }

        $page_num = ceil($count / $page_size);
        $total = $page_num;
        $i = 1;
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

    // 商户应用
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
                    $c = ['name' => $ck, 'title' => $c];
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

        $id = intval($this->params['id']);
        $module = Db::name('sys_modules')->where(['id' => $id])->find();
        if (empty($module)) {
            $this->error('此应用已经不存在,请移除');
        }

        return $this->template('goods_selector_op');
    }
}