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

namespace xsframe\enum;

use xsframe\base\BaseEnum;

class SysSettingsKeyEnum extends BaseEnum
{
    // 以下常量是为系统常用缓存做键值使用

    # 管理员
    const ADMIN_USER_KEY = "__admin_session";

    # 站点设置
    const WEBSITE_KEY = 'website_sets';

    # 附件设置
    const ATTACHMENT_KEY = 'attachment_sets';

    # 域名映射
    const DOMAIN_MAPPING_LIST_KEY = 'domain_mapping_list';

    # 项目设置
    const ACCOUNT_INFO_KEY = 'account_info_';

    # 模块设置
    const MODULE_SETS_KEY = 'account_module_sets_';

    # 模块信息
    const MODULE_INFO_KEY = 'module_info_';
}