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

namespace xsframe\exception;

use Exception;
use Throwable;

class BaseException extends Exception
{
    public $msg = '参数错误'; // 错误信息具体
    public $code = "404"; // HTTP 状态码 404,200...

    public function __construct($msg = "", $code = 0, Throwable $previous = null)
    {
        if (!empty($msg)) {
            $this->msg = $msg;
        }

        parent::__construct($msg, $code, $previous);
    }
}