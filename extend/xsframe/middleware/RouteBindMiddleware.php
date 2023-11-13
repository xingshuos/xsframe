<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace xsframe\middleware;

use xsframe\wrapper\PayAlipayNotifyWrapper;
use xsframe\wrapper\PayWechatNotifyWrapper;
use xsframe\wrapper\SiteMapWrapper;
use xsframe\wrapper\WechatWrapper;
use think\facade\Request;

/**
 * 路由中间件
 */
class RouteBindMiddleware
{
    /**
     * 设置域名绑定应用
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // 解决跨域问题 start
        // 已开启系统全局跨域请求 config \think\middleware\AllowCrossDomain::class
        // header('Content-Type: text/html;charset=utf-8');
        // header('Access-Control-Allow-Origin:*');
        // header('Access-Control-Allow-Methods:GET, POST, PATCH, PUT, DELETE, OPTIONS');
        // header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With , X-Access-Token,Content-Length,Accept-Encoding,Origin');
        // 解决跨域问题 end

        $pathInfo = Request::pathinfo();

        # 微信支付
        if ($pathInfo == 'wechat/notify') {
            exit(new PayWechatNotifyWrapper($request));
        }

        # 支付宝支付
        if ($pathInfo == 'alipay/notify') {
            exit(new PayAlipayNotifyWrapper($request));
        }

        # 微信公众号服务
        if ($pathInfo == 'wechat/service') {
            exit(new WechatWrapper($request));
        }

        # 网站收录
        if ($pathInfo == 'sitemap/create' || $pathInfo == 'sitemap') {
            exit(new SiteMapWrapper($request));
        }

        # 验证.env文件是否存在
        if( !is_file(root_path() . ".env") ){
            exit('<!DOCTYPE html> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> <title>缺失文件</title> <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7"> <style> html{height:100%;}@media only screen and (max-width: 320px){html {font-size: 28px !important; }}@media (min-width: 320.99px) and (max-width: 428px){html {font-size: 30px !important;}}@media (min-width: 428.99px) and (max-width: 481px){html { font-size: 32px !important; }}@media (min-width: 481.99px) and (max-width: 640.99px){html {font-size: 35px !important; }}@media (min-width: 641px){html {font-size: 40px !important; }}p img{max-width:100%;max-height:300px;}p{height:auto;width:100%;font-size: .6rem;}body{height:96%;}.pic{ position: absolute;top: 50%;left: 50%;-webkit-transform: translate(-50%, -50%);-moz-transform: translate(-50%, -50%);-ms-transform: translate(-50%, -50%);-o-transform: translate(-50%, -50%);transform: translate(-50%, -50%); }@media (max-width:767px){.pic{ position: absolute;top:50%;width:96%}} </style> </head> <body oncontextmenu="self.event.returnValue=false" onselectstart="return false"> <div class="pic"> <p > 是的，正如你所见—我们的网站无法正常运行。 <br> <br> 幸运的是，我们发现了问题所在， <br> <br> 请检查网站根目录配置'.root_path() . ".env".' 是否创建！ <br> <br> </p> </div> </body> </html>');
        }

        return $next($request);
    }

}
