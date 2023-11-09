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

namespace xsframe\facade\service;

use xsframe\service\OssService;
use think\Facade;

/**
 * @method static getVideoUrl($video_url, $config)
 * @method static deleteFile($filePath)
 * @method static uploadFile(string $ltrim, $path, $bucket = null)
 */
class OssServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return OssService::class;
    }
}