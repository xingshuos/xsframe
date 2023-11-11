<?php

namespace app\store\controller\web\goods;

use app\store\facade\service\ChaptersItemServiceFacade;
use app\store\facade\service\ChaptersServiceFacade;
use app\store\facade\service\GoodsCategoryServiceFacade;
use app\store\facade\service\GoodsParamServiceFacade;
use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\GoodsTagsServiceFacade;
use app\store\facade\service\SpecItemServiceFacade;
use app\store\facade\service\SpecServiceFacade;
use app\store\facade\service\TeacherServiceFacade;
use xsframe\base\AdminBaseController;
use xsframe\util\NumberUtil;
use xsframe\util\RandomUtil;
use xsframe\util\StringUtil;
use xsframe\util\TimeUtil;
use think\facade\Db;
use think\Image;

class Index extends AdminBaseController
{
    protected $tableName = 'store_goods';

    public function index()
    {
        return $this->main();
    }

    public function main($goodsFrom = "sale")
    {
        $condition = [
            'uniacid' => $this->uniacid
        ];

        if (!empty($this->params['type'])) {
            $condition['type'] = $this->params['type'];
        }

        if (!empty($this->params["attribute"])) {
            if ($this->params["attribute"] == "new") {
                $condition['isnew'] = 1;
            } else {
                if ($this->params["attribute"] == "hot") {
                    $condition['ishot'] = 1;
                } else {
                    if ($this->params["attribute"] == "recommand") {
                        $condition['isrecommand'] = 1;
                    } else {
                        if ($this->params["attribute"] == "sendfree") {
                            $condition['issendfree'] = 1;
                        }
                    }
                }
            }
        }

        $status = 1;

        if ($goodsFrom == "sale") { // 销售中
            $condition['status']  = 1;
            $condition['deleted'] = 0;
        } else {
            if ($goodsFrom == "cycle") { // 已删除
                $status               = 0;
                $condition['deleted'] = 1;
            }
        }

        if (!empty($this->params["cate"])) {
            $condition[''] = Db::raw(" FIND_IN_SET(" . intval($this->params["cate"]) . ",cates)<>0 ");
        }

        if (!empty($this->params["tagsid"])) {
            $condition['tags_id'] = intval($this->params["tagsid"]);
        }

        $total = GoodsServiceFacade::getTotal($condition);
        $list  = GoodsServiceFacade::getList($condition);

        foreach ($list as $key => &$value) {
            $value["allcates"]  = explode(",", $value["cates"]);
            $value["allcates"]  = array_unique($value["allcates"]);
            $value['tags_name'] = GoodsTagsServiceFacade::getValue(['id' => $value['tags_id']], "name");
        }

        $categorys = GoodsCategoryServiceFacade::getFullCategory(['deleted' => 0, 'uniacid' => $this->uniacid], true);
        $category  = array();
        foreach ($categorys as $cate) {
            $category[$cate["id"]] = $cate;
        }

        $tags = GoodsTagsServiceFacade::getAll(['deleted' => 0, 'uniacid' => $this->uniacid]);

        $result = [
            'status'    => $status,
            'category'  => $category,
            'tags'      => $tags,
            'total'     => $total,
            'list'      => $list,
            'goodsFrom' => $goodsFrom,
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

    public function sale()
    {
        return $this->main("sale");
    }

    public function out()
    {
        return $this->main("out");
    }

    public function stock()
    {
        return $this->main("stock");
    }

    public function cycle()
    {
        return $this->main("cycle");
    }

    public function restore()
    {
        $id = ($this->params["id"]);
        if (empty($id)) {
            $id = $this->params["ids"];
        }
        $items = GoodsServiceFacade::getList(['uniacid' => $this->uniacid, 'id' => $id]);
        foreach ($items as $item) {
            GoodsServiceFacade::updateInfo(['deleted' => 0], ['id' => $item['id']]);
        }
        show_json(1, array("url" => referer()));
    }

    public function post()
    {

        $id = ($this->params["id"]);

        if ($this->request->isPost()) {

            if (empty($id)) {
                $goodsType = intval($this->params['type']);
            } else {
                $goodsType = intval($this->params['goodstype']);
            }

            $data = array(
                "uniacid"       => $this->uniacid,
                "title"         => trim($this->params["goodsname"]),
                "unit"          => trim($this->params["unit"]),
                "keywords"      => trim($this->params["keywords"]),
                "thumb_first"   => intval($this->params["thumb_first"]),
                "type"          => $goodsType,
                "teacherid"     => intval($this->params['teacherid'] || 0),
                "isrecommand"   => intval($this->params["isrecommand"]),
                "isnew"         => intval($this->params["isnew"]),
                "ishot"         => intval($this->params["ishot"]),
                "issendfree"    => intval($this->params["issendfree"]),
                "marketprice"   => floatval($this->params["marketprice"]),
                "originalprice" => trim($this->params["originalprice"]),
                "invoice"       => intval($this->params["invoice"]),
                "status"        => intval($this->params["status"]),
                "goodssn"       => trim($this->params["goodssn"]),
                "productsn"     => trim($this->params["productsn"]),
                "weight"        => $this->params["weight"],
                "total"         => intval($this->params["total"]),
                "showtotal"     => intval($this->params["showtotal"]),
                "totalcnf"      => intval($this->params["totalcnf"]),
                "subtitle"      => trim($this->params["subtitle"]),
                "content"       => htmlspecialchars_decode($this->params["content"]),
                "createtime"    => time(),
                "updatetime"    => time(),
            );

            $data['tags_id'] = intval($this->params['tags_id']);

            $pcates = array();
            $ccates = array();
            $tcates = array();
            $cates  = array();

            $pcateid = 0;
            $ccateid = 0;
            $tcateid = 0;

            if (is_array($this->params["cates"])) {
                $cates = $this->params["cates"];

                foreach ($cates as $key => $cid) {
                    $c = GoodsCategoryServiceFacade::getInfo(['id' => $cid, 'uniacid' => $this->uniacid], 'level');
                    if ($c["level"] == 1) {
                        $pcates[] = $cid;
                    } else {
                        if ($c["level"] == 2) {
                            $ccates[] = $cid;
                        } else {
                            if ($c["level"] == 3) {
                                $tcates[] = $cid;
                            }
                        }
                    }
                    if ($key == 0) {
                        if ($c["level"] == 1) {
                            $pcateid = $cid;
                        } else {
                            if ($c["level"] == 2) {
                                $crow    = GoodsCategoryServiceFacade::getInfo(['id' => $cid, 'uniacid' => $this->uniacid], 'parentid');
                                $pcateid = $crow["parentid"];
                                $ccateid = $cid;
                            } else {
                                if ($c["level"] == 3) {
                                    $tcateid = $cid;
                                    $tcate   = GoodsCategoryServiceFacade::getInfo(['id' => $cid, 'uniacid' => $this->uniacid], 'id,parentid');
                                    $ccateid = $tcate["parentid"];
                                    $ccate   = GoodsCategoryServiceFacade::getInfo(['id' => $cid, 'uniacid' => $this->uniacid], 'id,parentid');
                                    $pcateid = $ccate["parentid"];
                                }
                            }
                        }
                    }
                }
            }

            $data["pcate"]  = $pcateid;
            $data["ccate"]  = $ccateid;
            $data["tcate"]  = $tcateid;
            $data["cates"]  = implode(",", $cates);
            $data["pcates"] = implode(",", $pcates);
            $data["ccates"] = implode(",", $ccates);
            $data["tcates"] = implode(",", $tcates);

            if (is_array($this->params["thumbs"])) {
                $thumbs    = $this->params["thumbs"];
                $thumb_url = array();
                foreach ($thumbs as $th) {
                    $thumb_url[] = trim($th);
                }
                $data["thumb_url"] = serialize($thumb_url);
            }

            if (!empty($this->params["thumb"])) {
                $data["thumb"] = $this->params["thumb"];
                $thumbSize     = $this->getMediaSize($data["thumb"]);
                if (!empty($thumbSize)) {
                    $data["thumb_size"] = json_encode($thumbSize);
                }
            }

            $thumbOriginal = [];
            if (!empty($this->params["thumb_original"])) {
                $thumbOriginal['thumb']  = trim($this->params["thumb_original"]);
                $thumbOriginal['size']   = trim($this->params["thumb_original_size"]);
                $thumbOriginal['width']  = trim($this->params["thumb_original_width"]);
                $thumbOriginal['height'] = trim($this->params["thumb_original_height"]);
                $data["thumb_original"]  = serialize($thumbOriginal);
            }

            // 实物商品
            if ($data['type'] == 1) {

            } else {
                // 线上课程
                if ($data['type'] == 2) {
                    // 章节 start
                    $chapter_ids             = empty($this->params["chapter_id"]) ? [] : $this->params["chapter_id"];
                    $chapter_statuss         = empty($this->params["chapter_status"]) ? [] : $this->params["chapter_status"];
                    $chapter_titles          = $this->params["chapter_title"];
                    $chapter_thumbs          = $this->params["chapter_thumb"];
                    $chapter_original_thumbs = $this->params["thumb_original"];
                    $chapterIds              = array();
                    $len                     = count($chapter_ids);

                    $chapters_item_total = 0;

                    $originalprice = 0.00; // 总价格
                    $durationTotal = 0.00; // 总秒数

                    for ($k = 0; $k < $len; $k++) {
                        $get_chapter_id = $chapter_ids[$k];
                        $a              = array(
                            "uniacid"        => $this->uniacid,
                            "title"          => $chapter_titles[$get_chapter_id],
                            "thumb"          => $chapter_thumbs[$get_chapter_id],
                            "thumb_original" => $chapter_original_thumbs[$get_chapter_id],
                            "status"         => $chapter_statuss[$k],
                            "goodsid"        => $id,
                            "displayorder"   => $k + 1,
                            "create_time"    => time(),
                            "update_time"    => time(),
                        );
                        if (is_numeric($get_chapter_id)) {
                            unset($a['create_time']);
                            ChaptersServiceFacade::updateInfo($a, ['id' => $get_chapter_id]);
                            $chapter_id = $get_chapter_id;
                        } else {
                            $chapter_id = ChaptersServiceFacade::insertInfo($a);
                        }

                        $chapter_item_ids          = $this->params["chapter_item_id_" . $get_chapter_id] ?? [];
                        $displayorder_vals         = $this->params["displayorder_val_" . $get_chapter_id] ?? [];
                        $chapter_item_titles       = $this->params["chapter_item_title_" . $get_chapter_id] ?? [];
                        $chapter_item_shows        = $this->params["chapter_item_show_" . $get_chapter_id] ?? [];
                        $chapter_item_statuss      = $this->params["chapter_item_status_" . $get_chapter_id] ?? [];
                        $chapter_item_isrecommands = $this->params["chapter_item_isrecommand_" . $get_chapter_id] ?? [];
                        // $chapter_item_ispublics           = $this->params["chapter_item_ispublic_" . $get_chapter_id] ?? [];
                        // $chapter_item_ispublics_show      = $this->params["chapter_item_ispublic_show_" . $get_chapter_id] ?? [];
                        $chapter_item_prices              = $this->params["chapter_item_price_" . $get_chapter_id] ?? [];
                        $chapter_item_video_thumbs        = $this->params["chapter_item_video_thumb_" . $get_chapter_id] ?? [];
                        $chapter_item_video_duration      = $this->params["chapter_item_video_duration_" . $get_chapter_id] ?? [];
                        $chapter_item_video_duration_time = $this->params["chapter_item_video_duration_time_" . $get_chapter_id] ?? [];
                        $chapter_item_videos              = $this->params["chapter_item_video_url_" . $get_chapter_id] ?? [];

                        $chapters_price        = 0.00;
                        $chapters_total        = 0;
                        $chaptersDurationTotal = 0.00; // 章秒数

                        $itemIds = array();
                        if (!empty($chapter_item_ids)) {
                            $itemLen = count($chapter_item_ids);

                            $chapters_item_total = $chapters_item_total + intval($itemLen);

                            for ($n = 0; $n < $itemLen; $n++) {
                                $get_item_id = $chapter_item_ids[$n];

                                $chapters_total        = $chapters_total + 1;
                                $chapters_price        = $chapters_price + floatval($chapter_item_prices[$n]);
                                $originalprice         = $originalprice + floatval($chapter_item_prices[$n]);
                                $durationTotal         = $durationTotal + floatval($chapter_item_video_duration[$n]);
                                $chaptersDurationTotal = $chaptersDurationTotal + floatval($chapter_item_video_duration[$n]);

                                $d = array(
                                    "uniacid"             => $this->uniacid,
                                    "goodsid"             => $id,
                                    "chapter_id"          => $chapter_id,
                                    "title"               => $chapter_item_titles[$n],
                                    "displayorder_val"    => $displayorder_vals[$n],
                                    "video_url"           => $chapter_item_videos[$n],
                                    "video_duration"      => $chapter_item_video_duration[$n],
                                    "video_duration_time" => $chapter_item_video_duration_time[$n],
                                    "thumb"               => $chapter_item_video_thumbs[$n],
                                    "is_trysee"           => intval($chapter_item_shows[$n]),
                                    "unit_price"          => floatval($chapter_item_prices[$n]),
                                    // "is_public"           => intval($chapter_item_ispublics[$n]),
                                    // "is_public_show"      => intval($chapter_item_ispublics_show[$n]),
                                    "status"              => intval($chapter_item_statuss[$n]),
                                    "isrecommand"         => intval($chapter_item_isrecommands[$n]),
                                    "displayorder"        => $n + 1,
                                    "create_time"         => time(),
                                    "update_time"         => time(),
                                );

                                if (!empty($d['video_duration_time']) && empty($d['video_duration'])) {
                                    $d['video_duration'] = TimeUtil::timeToDuration($d['video_duration_time']);
                                } else {
                                    if (empty($d['video_duration_time']) && !empty($d['video_duration'])) {
                                        $d['video_duration_time'] = TimeUtil::timeToDuration($d['video_duration']);
                                    }
                                }

                                if (is_numeric($get_item_id)) {
                                    unset($d['create_time']);
                                    ChaptersItemServiceFacade::updateInfo($d, ['id' => $get_item_id]);
                                    $item_id = $get_item_id;
                                } else {
                                    $item_id = ChaptersItemServiceFacade::insertInfo($d);
                                }

                                $itemIds[] = $item_id;
                            }

                        }

                        if (0 < count($itemIds)) {
                            ChaptersItemServiceFacade::updateInfo(['is_deleted' => 1], [
                                'chapter_id' => $chapter_id,
                                'id'         => Db::raw('not in (' . implode(",", $itemIds) . ")"),
                            ]);
                        } else {
                            ChaptersItemServiceFacade::updateInfo(['is_deleted' => 1], [
                                'chapter_id' => $chapter_id,
                            ]);
                        }

                        ChaptersServiceFacade::updateInfo([
                            "content"        => serialize($itemIds),
                            'chapters_price' => $chapters_price,
                            'lesson_total'   => $chapters_total,
                            'duration'       => $chaptersDurationTotal,
                            'duration_time'  => TimeUtil::changeTimeType(intval($chaptersDurationTotal)),
                        ], ["id" => $chapter_id]);
                        $chapterIds[] = $chapter_id;
                    }

                    if (0 < count($chapterIds)) {
                        ChaptersServiceFacade::updateInfo(['is_deleted' => 1], [
                            'goodsid' => $id,
                            'id'      => Db::raw('not in (' . implode(",", $chapterIds) . ")"),
                        ]);

                        ChaptersItemServiceFacade::updateInfo(['is_deleted' => 1], [
                            'goodsid'    => $id,
                            'chapter_id' => Db::raw('not in (' . implode(",", $chapterIds) . ")"),
                        ]);
                    } else {
                        ChaptersServiceFacade::updateInfo(['is_deleted' => 1], [
                            'goodsid' => $id,
                        ]);

                        ChaptersItemServiceFacade::updateInfo(['is_deleted' => 1], [
                            'goodsid' => $id,
                        ]);
                    }
                    // 章节 end
                }
            }

            if (empty($id)) {
                $id = GoodsServiceFacade::insertInfo($data);
            } else {
                unset($data['createtime']);
                GoodsServiceFacade::updateInfo($data, ['id' => $id]);
            }

            // 参数
            $param_ids    = $this->params['param_id'] ?? [];
            $param_titles = $this->params['param_title'] ?? "";
            $param_values = $this->params['param_value'] ?? "";

            $len      = count($param_ids);
            $paramids = array();
            $k        = 0;

            while ($k < $len) {
                $param_id     = '';
                $get_param_id = $param_ids[$k];
                $a            = array(
                    'uniacid'      => $this->uniacid,
                    'title'        => $param_titles[$k],
                    'value'        => $param_values[$k],
                    'displayorder' => $k,
                    'goodsid'      => $id
                );
                if (!is_numeric($get_param_id)) {
                    $param_id = GoodsParamServiceFacade::insertInfo($a);
                } else {
                    GoodsParamServiceFacade::updateInfo($a, ['id' => $get_param_id]);
                    $param_id = $get_param_id;
                }
                $paramids[] = $param_id;
                ++$k;
            }

            if (0 < count($paramids)) {
                GoodsParamServiceFacade::deleteInfo(['goodsid' => $id, 'id' => Db::raw('not in (' . implode(',', $paramids) . ')')]);
            } else {
                GoodsParamServiceFacade::deleteInfo(['goodsid' => $id]);
            }
            // 参数 end

            show_json(1, array("url" => webUrl("web.goods.index/edit", array("id" => $id, "tab" => str_replace("#tab_", "", $this->params["tab"])))));
        }

        $item = GoodsServiceFacade::getInfo(['id' => $id]);

        $cates = [];
        if (!empty($item["cates"])) {
            $cates = explode(",", $item["cates"]);
        }

        $picList = [];
        if (!empty($item["thumb"])) {
            // $picList = array_merge(array($item["thumb"]), iunserializer($item["thumb_url"]));
            $picList = iunserializer($item["thumb_url"]);
        }

        $thumbOriginal = [];
        if (!empty($item["thumb_original"])) {
            $thumbOriginal = iunserializer($item["thumb_original"]);
        }

        $categorys = GoodsCategoryServiceFacade::getFullCategory(['deleted' => 0, 'uniacid' => $this->uniacid], true);
        $category  = array();
        foreach ($categorys as $cate) {
            $category[$cate["id"]] = $cate;
        }

        $tags = GoodsTagsServiceFacade::getAll(['deleted' => 0, 'uniacid' => $this->uniacid]);

        $levels = [];
        $levels = array_merge(array(array("id" => 0, "key" => "default", "levelname" => (empty($_W["shopset"]["shop"]["levelname"]) ? "默认会员" : $_W["shopset"]["shop"]["levelname"]))), $levels);

        $params = GoodsParamServiceFacade::getAll(['goodsid' => $id], "*", "displayorder asc");

        $result = [
            'category'      => $category,
            'tags'          => $tags,
            'item'          => $item,
            'params'        => $params,
            'picList'       => $picList,
            'thumbOriginal' => $thumbOriginal,
            'cates'         => $cates,
            'levels'      => $levels,
        ];
        return $this->template('post', $result);
    }

    // 获取图片宽高信息
    private function getMediaSize($thumb)
    {
        $thumbPath = $this->iaRoot . "/public/attachment/" . $thumb;
        if (is_file($thumbPath)) {
            $image  = Image::open($thumbPath);
            $width  = $image->width();
            $height = $image->height();

            return [
                'width'  => $width,
                'height' => $height,
            ];
        }

        return [];
    }

    // 引入模板
    public function tpl()
    {
        $tpl      = trim($this->params["tpl"]);
        $template = "";
        if ($tpl == "param") {
            $tag      = RandomUtil::random(32);
            $template = $this->template('web/goods/index/tpl/param', ['tag' => $tag]);
        } else {
            $numbers = StringUtil::getUpperNumber();
            if ($tpl == "chapter") {
                $chapter = array(
                    "id"           => RandomUtil::random(32),
                    "displayorder" => intval($this->params["num"]),
                    "title"        => $this->params["title"],
                    "items"        => []
                );
                // dump($chapter);
                // die;
                $template = $this->template('web/goods/index/tpl/chapter', [
                    'numbers' => $numbers,
                    'chapter' => $chapter,
                ]);
            } else {
                if ($tpl == "chapter_item") {
                    $id          = $this->params['goodsid'];
                    $chapter     = array(
                        "id" => $this->params["chapterid"],
                    );
                    $chapterItem = array(
                        "id"           => RandomUtil::random(32),
                        "title"        => $this->params["title"],
                        "displayorder" => intval($this->params["num"]),
                        "show"         => 1,
                    );
                    $template    = $this->template('web/goods/index/tpl/chapter_item', [
                        'numbers'     => $numbers,
                        'id'          => $id,
                        'chapter'     => $chapter,
                        'chapterItem' => $chapterItem,
                    ]);
                }
            }
        }

        return $template;
    }

}
