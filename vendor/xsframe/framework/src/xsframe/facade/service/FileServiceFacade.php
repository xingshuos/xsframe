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

namespace xsframe\facade\service;

use xsframe\service\FileService;
use think\Facade;

/**
 * @method static deleteFile($filePath)
 * @method static uploadFile(string $filename, string $filePath)
 */
class FileServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return FileService::class;
    }
}