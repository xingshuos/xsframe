<?php

namespace app\store\controller\web\goods;

use app\store\facade\service\GoodsCategoryServiceFacade;
use xsframe\base\AdminBaseController;
use think\facade\Db;

class Category extends AdminBaseController
{
    protected $tableName = 'store_goods_category';

    public function index()
    {
        return redirect("/{$this->module}/web.article.category/list");
    }

    public function main()
    {
        $condition = [
            'uniacid' => $this->uniacid,
            'deleted' => 0,
        ];

        if ($this->request->isPost() && !empty($this->params["datas"])) {
            $datas = json_decode(html_entity_decode($this->params["datas"]), true);
            if (!is_array($datas)) {
                show_json(0, "分类保存失败，请重试!");
            }
            $cateIds      = array();
            $displayorder = count($datas);
            foreach ($datas as $row) {
                $cateIds[] = $row["id"];
                GoodsCategoryServiceFacade::updateInfo(["parentid" => 0, "displayorder" => $displayorder, "level" => 1], ["id" => $row["id"]]);

                if (isset($row["children"]) && $row["children"] && is_array($row["children"])) {
                    $displayorder_child = count($row["children"]);
                    foreach ($row["children"] as $child) {
                        $cateIds[] = $child["id"];
                        Db::execute("update " . tablename("store_goods_category") . " set parentid=:parentid,displayorder=:displayorder,level=2 where id=:id ", array("displayorder" => $displayorder_child, "parentid" => $row["id"], "id" => $child["id"]));

                        $displayorder_child--;
                        if (isset($child["children"]) && $child["children"] && is_array($child["children"])) {
                            $displayorder_third = count($child["children"]);
                            foreach ($child["children"] as $third) {
                                $cateIds[] = $third["id"];
                                Db::execute("update " . tablename("store_goods_category") . " set  parentid=:parentid,displayorder=:displayorder,level=3 where id=:id", array("displayorder" => $displayorder_third, "parentid" => $child["id"], "id" => $third["id"]));
                                $displayorder_third--;
                                if (isset($third["children"]) && $third["children"] && is_array($third["children"])) {
                                    $displayorder_fourth = count($third["children"]);
                                    foreach ($child["children"] as $fourth) {
                                        $cateIds[] = $fourth["id"];
                                        Db::execute("update " . tablename("store_goods_category") . " set  parentid=:parentid,displayorder=:displayorder,level=3 where id=:id", array("displayorder" => $displayorder_third, "parentid" => $third["id"], "id" => $fourth["id"]));
                                        $displayorder_fourth--;
                                    }
                                }
                            }
                        }
                    }
                }
                $displayorder--;
            }

            if (!empty($cateIds)) {
                Db::execute("delete from " . tablename("store_goods_category") . " where id not in (" . implode(",", $cateIds) . ") and uniacid=:uniacid", array("uniacid" => $this->uniacid));
            }

            // TODO 更新分类信息
            // m("shop")->getCategory(true);
            // m("shop")->getAllCategory(true);
            show_json(1);
        }

        $children = array();
        $category = GoodsCategoryServiceFacade::getList($condition, "*", 'parentid ASC,displayorder desc,id asc', $this->pIndex, 100);
        foreach ($category as $index => $row) {
            if (!empty($row["parentid"])) {
                $children[$row["parentid"]][] = $row;
                unset($category[$index]);
            }
        }

        $result = [
            'category' => $category,
            'children' => $children,
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
        $id       = $this->params['id'];
        $parentid = intval($this->params["parentid"]);

        if (!empty($id)) {
            $item     = GoodsCategoryServiceFacade::getInfo(['id' => $id]);
            $parentid = $item["parentid"];
        } else {
            $item = array("displayorder" => 0);
        }

        $parent  = [];
        $parent1 = [];
        if (!empty($parentid)) {
            $parent = GoodsCategoryServiceFacade::getInfo(['id' => $parentid]);
            if (empty($parent)) {
                exit("抱歉，上级分类不存在或是已经被删除！");
            }
            if (!empty($parent["parentid"])) {
                $parent1 = GoodsCategoryServiceFacade::getInfo(['id' => $parent["parentid"]]);
            }
        }

        if (empty($parent)) {
            $level = 1;
        } else {
            if (empty($parent["parentid"])) {
                $level = 2;
            } else {
                $level = 3;
            }
        }

        if ($this->request->isPost()) {
            $data = array(
                "uniacid"      => $this->uniacid,
                "name"         => trim($this->params["name"] ?? ''),
                "thumb"        => trim($this->params["thumb"] ?? ''),
                "thumb_sel"    => trim($this->params["thumb_sel"] ?? ''),
                "description"  => trim($this->params["description"] ?? ''),
                "enabled"      => intval($this->params["enabled"] ?? 0),
                "displayorder" => intval($this->params["displayorder"] ?? 0),
                "parentid"     => intval($parentid),
                "level"        => $level,
                "style_type"   => intval($this->params['style_type'] ?? 1),
            );
            if (!empty($id)) {
                GoodsCategoryServiceFacade::updateInfo($data, ['id' => $id]);
            } else {
                $data['createtime'] = time();
                GoodsCategoryServiceFacade::insertInfo($data);
            }

            // TODO 跟新商品分类缓存信息
            // m("shop")->getCategory(true);
            // m("shop")->getAllCategory(true);

            $url = webUrl("web.goods.category/main");
            $this->success(array("url" => $url));
        }

        $result = [
            'item'     => $item,
            'parentid' => $parentid,
            'parent'   => $parent,
            'parent1'  => $parent1,
        ];
        return $this->template('post', $result);
    }
}
