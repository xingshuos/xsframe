<?php
declare (strict_types=1);

namespace app\xs_cloud\controller\web;

use xsframe\base\AdminBaseController;

class Index extends AdminBaseController
{
    public function index()
    {
        return redirect('web.sysset/site');
    }
}
