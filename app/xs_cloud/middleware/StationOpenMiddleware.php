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

/**
 * Class StationOpenMiddleware
 * @package app\api\middleware
 */
class StationOpenMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        $stationOpen = true; // 站点是否启用 默认启用
        if (!$stationOpen) {
            exit('<!DOCTYPE html> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> <title>网站已关闭</title> <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7"> <style> html{height:100%;}@media only screen and (max-width: 320px){html {font-size: 28px !important; }}@media (min-width: 320.99px) and (max-width: 428px){html {font-size: 30px !important;}}@media (min-width: 428.99px) and (max-width: 481px){html { font-size: 32px !important; }}@media (min-width: 481.99px) and (max-width: 640.99px){html {font-size: 35px !important; }}@media (min-width: 641px){html {font-size: 40px !important; }}p img{max-width:100%;max-height:300px;}p{height:auto;width:100%;font-size: .6rem;}body{height:96%;}.pic{ position: absolute;top: 50%;left: 50%;-webkit-transform: translate(-50%, -50%);-moz-transform: translate(-50%, -50%);-ms-transform: translate(-50%, -50%);-o-transform: translate(-50%, -50%);transform: translate(-50%, -50%); }@media (max-width:767px){.pic{ position: absolute;top:50%;width:96%}} </style> </head> <body oncontextmenu="self.event.returnValue=false" onselectstart="return false"> <div class="pic"> <p > 是的，正如你所见—我们的网站已经下线了。 <br> <br> 幸运的是，我们还有机会，不久的将来我们会重新相遇！ <br> <br> </p> </div> </body> </html>');
        }
        return $next($request);
    }
}
