<?php


namespace app\admin\enum;

use xsframe\base\BaseEnum;

class CacheKeyEnum extends BaseEnum
{
    # 获取框架更新日志列表
    const CLOUD_FRAME_UPGRADE_LIST_KEY = 'cloud_frame_upgrade_list';

    # 获取框架更新文件列表
    const CLOUD_FRAME_UPGRADE_FILES_KEY = 'cloud_frame_upgrade_files';
}