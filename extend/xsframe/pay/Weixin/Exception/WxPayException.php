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

/**
 * Created by Date: 2019/4/11
 */

namespace xsframe\pay\Weixin\Exception;

use app\common\Exception\Code\ExceptionEnum;

class WxPayException extends \RuntimeException
{
    protected $code = ExceptionEnum::THIRD_WEIXIN_PAY_CODE;

    public function errorMessage()
    {
        return $this->getMessage();
    }
}