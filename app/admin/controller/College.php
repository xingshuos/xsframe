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

use xsframe\base\AdminBaseController;
use xsframe\util\RequestUtil;

class College extends AdminBaseController
{
    private $apiUrl = null;

    // private $apiUrl = "http://www.xsframe.com";

    public function post()
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        if ($this->request->isPost()) {
            $categoryId = intval($this->params['cateid'] ?? 0);
            $title = trim($this->params['title'] ?? '');
            $tags = trim($this->params['tags'] ?? '');
            $content = htmlspecialchars_decode($this->params['content'] ?? '');

            if (empty($categoryId)) {
                $this->error('请选择所属分类');
            }
            if (empty($title)) {
                $this->error('请输入文章标题');
            }
            if (empty($tags)) {
                $this->error('请输入文章标签');
            }
            if (empty($content)) {
                $this->error('请输入文章内容');
            }
            $data = [
                'key'     => $key,
                'token'   => $token,
                'cateid'  => $categoryId,
                'title'   => $title,
                'tags'    => $tags,
                'content' => $content,
            ];
            $cloudResult = RequestUtil::cloudHttpPost("college/create", $data, null, $this->apiUrl);
            if ($cloudResult['code'] != 200) {
                $this->error($cloudResult['msg']);
            }
            $this->success();
        }

        $categoryList = [];

        $cloudResult = RequestUtil::cloudHttpPost("college/category", ['key' => $key, 'token' => $token], null, $this->apiUrl);
        if ($cloudResult['code'] == 200) {
            $categoryList = $cloudResult['data']['list'];
        }

        $result = [
            'categoryList' => $categoryList
        ];
        return $this->template('post', $result);
    }

    public function view()
    {
        $id = intval($this->params['id'] ?? 0);
        $item = $this->getDetail($id);
        return $this->template('view', $item);
    }

    // 我的文章
    public function mine(): \think\response\View
    {
        $result = $this->getList(0, true);
        return $this->template('mine', $result);
    }

    // 文档教程
    public function document(): \think\response\View
    {
        $result = $this->getList(4);
        return $this->template('document', $result);
    }

    // 应用开发
    public function develop(): \think\response\View
    {
        $result = $this->getList(2);
        return $this->template('develop', $result);
    }

    // 使用反馈
    public function feedback(): \think\response\View
    {
        $result = $this->getList(1);
        return $this->template('feedback', $result);
    }

    // 优化建议
    public function optimize(): \think\response\View
    {
        $result = $this->getList(3);
        return $this->template('optimize', $result);
    }

    // 开源交流
    public function exchange(): \think\response\View
    {
        $result = $this->getList(6);
        return $this->template('exchange', $result);
    }

    // 创客学堂
    public function affiliate(): \think\response\View
    {
        $result = $this->getList(5);
        return $this->template('affiliate', $result);
    }

    // 应用玩法
    public function guide(): \think\response\View
    {
        $result = $this->getList(7);
        return $this->template('guide', $result);
    }

    // 系统图标
    public function icon()
    {
        return $this->template('icon');
    }

    // 系统表情
    public function emoji()
    {
        return $this->template('select_emoji');
    }

    // 系统图标
    public function selectIcon()
    {
        return $this->template('select_icon');
    }

    private function getDetail($id): array
    {
        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $item = [];
        $cloudResult = RequestUtil::cloudHttpPost("college/detail", ['key' => $key, 'token' => $token, 'id' => $id], null, $this->apiUrl);
        if ($cloudResult['code'] == 200) {
            $item = $cloudResult['data']['item'];
            if ($item) {
                $item['labels'] = explode(',', $item['labels']);
            }
        }

        // dd($item);

        return [
            'item' => $item,
        ];
    }

    private function getList($cateId, $isMine = false): array
    {
        $keyword = trim($this->params['keyword'] ?? '');
        $type = trim($this->params['type'] ?? '');

        $key = $this->websiteSets['key'] ?? '';
        $token = $this->websiteSets['token'] ?? '';

        $list = [];
        $total = 0;

        $cloudResult = RequestUtil::cloudHttpPost("college/list", [
            'key'        => $key,
            'token'      => $token,
            'cateid'     => $cateId,
            'keyword'    => $keyword,
            'order_type' => $type,
            'is_mine'    => $isMine,
            'page'       => $this->pIndex
        ], null, $this->apiUrl);

        if ($cloudResult['code'] == 200) {
            $list = $cloudResult['data']['list'];
            $total = $cloudResult['data']['total'];

            foreach ($list as &$item) {
                $item['labels'] = explode(',', $item['labels']);
            }
        }

        // dd($list[0]);

        $pager = pagination2($total, $this->pIndex, $this->pSize);

        return [
            'list'   => $list,
            'total'  => $total,
            'pager'  => $pager,
            'isMine' => $isMine,

            'communication_status' => ($this->websiteSets['communication_status'] && $this->websiteSets['key']) ?? 0,
        ];
    }
}
