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

        return $next($request);
    }

}
