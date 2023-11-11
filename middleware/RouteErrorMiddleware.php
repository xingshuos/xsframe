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

namespace app\xs_cloud\middleware;


use xsframe\interfaces\MiddlewareInterface;
use think\Response;

/**
 * 路由中间件
 * Class RouteErrorMiddleware
 */
class RouteErrorMiddleware implements MiddlewareInterface
{

    /**
     * 允许跨域的域名
     * @var string
     */
    protected $cookieDomain;

    public function handle($request, \Closure $next)
    {

    }
}
