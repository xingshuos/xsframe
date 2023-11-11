<?php
declare (strict_types=1);

namespace app\store\controller\web;

use xsframe\base\AdminBaseController;

class Index extends AdminBaseController
{
    public function index()
    {
        return redirect('web.sysset/site');
    }
}
