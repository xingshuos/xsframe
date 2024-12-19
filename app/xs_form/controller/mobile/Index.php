<?php
declare (strict_types=1);

namespace app\xs_form\controller\mobile;

use xsframe\base\MobileBaseController;

class Index extends MobileBaseController
{
    public function index()
    {
        return $this->template('index/index');
    }

    public function test()
    {
        return $this->template('index/test');
    }
}
