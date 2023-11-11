<?php

namespace app\store\controller\web\article;

use app\store\facade\service\ArticleCategoryServiceFacade;
use app\store\facade\service\ArticleServiceFacade;
use app\store\service\ArticleService;
use xsframe\base\AdminBaseController;
use think\facade\Db;

class Index extends AdminBaseController
{
    public function index()
    {
        // 默认进入幻灯片管理
        return redirect("/{$this->module}/web.article.index/main");
    }

    public function main($type = null)
    {
        $keyword = $this->params['keyword'];
        $status  = $this->params['status'];
        $limit   = trim($this->params["limit"]);

        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        if (is_numeric($status)) {
            $condition['enabled'] = $status;
        }

        if (!empty($keyword)) {
            $condition[''] = Db::raw(" title like '%" . trim($keyword) . "%' or sub_title like '%" . trim($keyword) . "%' ");
        }

        $list  = ArticleServiceFacade::getList($condition,"*",'cateid desc,enabled desc,displayorder desc,id desc');
        $total = ArticleServiceFacade::getTotal($condition);
        $pager = pagination2($total, $this->pIndex, $this->pSize);

        foreach ($list as &$item) {
            $categoryName = ArticleCategoryServiceFacade::getValue(['id' => $item['pcateid']],'name');
            $item['category_name'] = $categoryName;
        }

        $result = [
            'list'   => $list,
            'pager'  => $pager,
            'total'  => $total,
            'status' => $status,
        ];

        // dump($result);die;
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
        $id = $this->params['id'];

        if ($this->request->isPost()) {
            $data = array(
                "uniacid"      => $this->uniacid,
                'cates'        => intval($this->params["pcateid"]), // 全部
                'pcateid'      => intval($this->params["pcateid"]), // 主
                'cateid'       => 0, // 次
                "title"        => trim($this->params["title"]),
                'sub_title'    => trim($this->params['sub_title']),
                'keywords'     => trim($this->params['keywords']),
                'description'  => trim($this->params['description']),
                "author"       => trim($this->params["author"]),
                "editor"       => trim($this->params["editor"]),
                "thumb"        => trim($this->params["thumb"]),
                "article_mp"   => trim($this->params["article_mp"]),
                "isrecommend"  => trim($this->params["isrecommend"]),
                "enabled"      => intval($this->params["enabled"]),
                "content"      => htmlspecialchars_decode($this->params["content"]),
                "displayorder" => intval($this->params["displayorder"]),
                "showtime"     => strtotime($this->params["showtime"]),
                'updatetime'   => time(),
                'viewcount_v'  => trim($this->params['viewcount_v']),
                'likenum_v'    => trim($this->params['likenum_v']),
                'video_poster' => trim($this->params['video_poster']),
                'video_url'    => trim($this->params['video_url']),
                'audio_url'    => trim($this->params['audio_url']),
            );
            if (!empty($id)) {
                Db::name('store_article')->where(["id" => $id])->update($data);
            } else {
                $data['createtime'] = time();
                $id = Db::name('store_article')->insertGetId($data);
            }

            show_json(1, array("url" => webUrl("web.article.index/edit", array("id" => $id, "tab" => str_replace("#tab_", "", $this->params["tab"])))));
        }

        $item      = Db::name('store_article')->where(['id' => $id])->find();
        $categorys = Db::name('store_article_category')->where(['uniacid' => $this->uniacid, 'deleted' => 0])->select();
        return $this->template('post', ['item' => $item, 'categorys' => $categorys]);
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

        $items = Db::name("store_article")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("store_article")->where("id", '=', $item['id'])->update(['deleted' => 1]);
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

        $items = Db::name("store_article")->where(['id' => $id])->select();
        foreach ($items as $item) {
            Db::name("store_article")->where("id", '=', $item['id'])->update([$type => $value]);
        }

        $this->success();
    }

}
