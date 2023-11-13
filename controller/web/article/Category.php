<?php

namespace app\xs_cloud\controller\web\article;

use xsframe\base\AdminBaseController;
use think\facade\Db;

class Category extends AdminBaseController
{
    public function index()
    {
        return redirect("/{$this->module}/web.article.category/list");
    }

    public function main()
    {
        $keyword = $this->params['keyword'];

        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" name like '%" . trim($keyword) . "%' ");
        }

        $list  = Db::name("store_article_category")->where($condition)->order('displayorder desc,id desc')->page($this->pIndex, $this->pSize)->select()->toArray();
        $total = Db::name("store_article_category")->where($condition)->count();
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        $result = [
            'list'  => $list,
            'pager' => $pager,
            'total' => $total,
        ];

        return $this->template('list', $result);
    }

    public function edit()
    {
        return $this->post();
    }

    public function add()
    {
        return $this->post();
    }

    public function post()
    {
        $id   = $this->params['id'];
        $type = $this->params['type'];

        $item = Db::name('store_article_category')->where(['id' => $id])->find();
        $type = $type ?? $item['type'];

        $url = webUrl("web.article.category/list");

        if ($this->request->isPost()) {
            $data = array(
                "uniacid"      => $this->uniacid,
                "enabled"      => intval($this->params["enabled"]),
                "displayorder" => intval($this->params["displayorder"]),
                "name"         => trim($this->params["name"]),
                "description"  => trim($this->params["description"]),
            );
            if (!empty($id)) {
                Db::name('store_article_category')->where(['id' => $id])->update($data);
            } else {
                $data['createtime'] = time();
                Db::name('store_article_category')->insert($data);
            }

            $this->success(array("url" => $url));
        }

        $groups = Db::name("yt_5s_manager_group")->where(['deleted' => 0])->order('id desc')->select();
        return $this->template('post', ['item' => $item, 'type' => $type, 'groups' => $groups, 'backUrl' => $url]);
    }

    public function delete()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, array("message" => "参数错误"));
        }

        $items = Db::name("store_article_category")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("store_article_category")->where("id", '=', $item['id'])->update(['deleted' => 1]);
        }
        $this->success(array("url" => referer()));
    }

    public function change()
    {
        $id = intval($this->params["id"]);

        if (empty($id)) {
            $id = $this->params["ids"];
        }

        if (empty($id)) {
            show_json(0, array("message" => "参数错误"));
        }

        $type  = trim($this->params["type"]);
        $value = trim($this->params["value"]);

        $items = Db::name("store_article_category")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("store_article_category")->where("id", '=', $item['id'])->update([$type => $value]);
        }

        $this->success();
    }
}
