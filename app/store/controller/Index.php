<?php


namespace app\store\controller;

use think\App;

class Index extends Base
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

        return $this->template('/pc/index', $result);
    }

    public function __call($method, $args)
    {
        $this->index();
    }
}