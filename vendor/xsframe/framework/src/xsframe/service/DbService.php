<?php

namespace xsframe\service;

use think\App;
use think\Request;
use xsframe\base\BaseService;
use xsframe\traits\ServiceTraits;
use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\SettingsWrapper;

class DbService
{
    protected $request;
    protected $header;
    protected $app;
    protected $params;

    use ServiceTraits;

    public function __construct(Request $request, App $app)
    {
        $this->request = $request;
        $this->header = $request->header();
        $this->app = $app;
        $this->params = $this->request->param();
    }
}