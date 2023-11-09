<?php


namespace app\store\controller;

use think\App;

class Detail extends Base
{
    public function index()
    {
        $title       = '';
        $keywords    = '';
        $description = '';

        $result = [
            'title'       => $title,
            'keywords'    => $keywords,
            'description' => $description,
        ];

        return $this->template('/pc/detail', $result);
    }

    public function __call($method, $args)
    {
        $this->index();
    }
}