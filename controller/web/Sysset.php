<?php
declare (strict_types=1);

namespace app\xs_cloud\controller\web;

use xsframe\base\AdminBaseController;

class Sysset extends AdminBaseController
{
    public function index()
    {
        return redirect('/store/web.sysset/site');
    }

    public function site()
    {
        $basic = [];
        $list  = [];

        $var = [
            'basic' => $basic,
            'list'  => $list,
        ];
        return $this->template('site', $var);
    }
}
