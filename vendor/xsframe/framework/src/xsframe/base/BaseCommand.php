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

namespace xsframe\base;

use think\console\Command;
use think\console\Input;
use think\console\Output;

abstract class BaseCommand extends Command
{
    protected static $defaultName = '';

    protected function configure()
    {
    }

    protected function execute(Input $input, Output $output)
    {
    }
}
