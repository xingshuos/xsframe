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

use xsframe\wrapper\AccountHostWrapper;
use xsframe\wrapper\UserWrapper;

/**
 * 域名访问默认应用
 */
class DomainBindMiddleware
{
    protected $accountHostWrapper;

    public function __construct()
    {
        if (!$this->accountHostWrapper instanceof AccountHostWrapper) {
            $this->accountHostWrapper = new AccountHostWrapper();
        }
    }

    /**
     * 执行域名访问默认应用
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $module = app('http')->getName();
        if (empty($module) || (empty($request->root()) && !empty($module))) {
            $url = $request->header()['host'];

            // TODO 每次加载最新数据 可以优化为读取缓存的方式
            $domainMappingArr = $this->accountHostWrapper->getAccountHost(true);
            if (!empty($domainMappingArr) && !empty($domainMappingArr[$url])) {
                $module = $domainMappingArr[$url]['default_module'];
                $appMap = array_flip(config('app.app_map'));
                $realModuleName = array_key_exists($module, $appMap) ? $appMap[$module] : '';
                $url = UserWrapper::getModuleOneUrl($realModuleName ?: $module);
                exit(header("location:" . $url));
            }
        }

        return $next($request);
    }

}
