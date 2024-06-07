<?php


namespace xsframe\enum;

use xsframe\base\BaseEnum;

class CacheKeyEnum extends BaseEnum
{
    # 获取框架更新日志列表
    const CLOUD_FRAME_UPGRADE_LIST_KEY = 'cloud_frame_upgrade_list';

    # 获取框架更新文件列表
    const CLOUD_FRAME_UPGRADE_FILES_KEY = 'cloud_frame_upgrade_files';

    # 获取全部的商户列表(防止不存在的uniacid被记录)
    const SYSTEM_UNIACID_LIST_KEY = 'system_uniacid_list';

    # 获取全部被禁用的商户列表
    const SYSTEM_UNIACID_DISABLE_LIST_KEY = 'system_uniacid_disable_list';

    # 获取全部的商户应用列表(防止无权限的应用被访问)
    const UNIACID_MODULE_LIST_KEY = 'uniacid_module_list';

    # 获取全部的系统应用列表(防止无权限的应用被访问)
    const SYSTEM_MODULE_LIST_KEY = 'system_module_list';
}