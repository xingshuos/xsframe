<?php


namespace app\store\controller\pc;

use app\store\facade\service\AdvServiceFacade;
use app\store\facade\service\ArticleCategoryServiceFacade;
use app\store\facade\service\ArticleServiceFacade;
use xsframe\util\ImgUtil;
use think\facade\Db;

class Article extends Base
{
    public function index()
    {
        $id = $this->params['id'];

        $articleInfo = ArticleServiceFacade::getInfo(['id' => $id]);
        ArticleServiceFacade::updateInfo(['viewcount' => Db::raw('viewcount + 1')], ['id' => $id]);

        $categoryInfo = ArticleCategoryServiceFacade::getInfo(['id' => $articleInfo['pcateid'], 'enabled' => 1], "id,name");

        $articleInfo['content'] = ImgUtil::html2images($articleInfo['content'], '?x-oss-process=image/resize,w_750,m_lfit', true, true);

        $result = [
            'articleInfo'  => $articleInfo,
            'categoryInfo' => $categoryInfo,
        ];

        return $this->template('pc/article/detail', $result);
    }

    public function category()
    {
        $id = $this->params['id'];

        $advList = AdvServiceFacade::getList(['uniacid' => $this->uniacid, 'enabled' => 1, 'deleted' => 0, 'cateid' => $id, 'type' => 1], "id,title,thumb,link", "displayorder desc,id desc");
        $advList = set_medias($advList, ['thumb']);

        $articleTotal = ArticleServiceFacade::getTotal(['pcateid' => $id, 'enabled' => 1, 'deleted' => 0]);
        $articleList  = ArticleServiceFacade::getList(['pcateid' => $id, 'enabled' => 1, 'deleted' => 0], "id,title,thumb,showtime,viewcount,viewcount_v,video_url", "displayorder desc,showtime desc,id desc", $this->pIndex, 12);
        $articleList  = set_medias($articleList, ['thumb']);

        $pager = pagination($articleTotal, $this->pIndex, $this->pSize);

        $categoryInfo = ArticleCategoryServiceFacade::getInfo(['id' => $id], "id,name,type");

        $result = [
            'advList'      => $advList,
            'articleList'  => $articleList,
            'pager'        => $pager,
            'categoryInfo' => $categoryInfo,
        ];

        return $this->template('pc/article/category', $result);
    }

}