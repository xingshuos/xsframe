<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\base;


abstract class CacheBaseController extends BaseController
{
    protected $clientBaseType = 'cache';
    protected function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        if (method_exists($this, '_cache_initialize')) {
            $this->_cache_initialize();
        }
    }

    // 初始化
    protected function _cache_initialize()
    {
    }
}