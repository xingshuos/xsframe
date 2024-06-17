<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

use xsframe\util\FileUtil;

return [
    // 指令定义 查看: php think 执行:php think TestCommand
    'commands' => FileUtil::getAppCommands(),
];
