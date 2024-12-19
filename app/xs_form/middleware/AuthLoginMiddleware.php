<?php
// +----------------------------------------------------------------------
// | 星数 [ xsframe赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2023 https://www.xsframe.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 星数PHP并不是自由软件，未经许可不能去掉xsframe相关版权
// +----------------------------------------------------------------------
// | Author: GuiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace app\xs_form\middleware;

use xsframe\facade\service\SysMemberServiceFacade;
use xsframe\interfaces\MiddlewareInterface;

/**
 * Class AuthLoginMiddleware
 * @package app\api\middleware
 */
class AuthLoginMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        SysMemberServiceFacade::checkLogin();
        return $next($request);
    }
}
