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

namespace app\admin\controller;

use think\facade\Db;

class Member extends Base
{
    public function query()
    {
        $kwd = trim($this->params['keyword']);

        $where = [
            'is_deleted' => 0
        ];

        if (!empty($kwd)) {
            $where[] = ['nickname|mobile|realname|username', 'like', '%' . $kwd . '%'];
        }

        $list = Db::name('sys_member')->field("id,avatar,nickname,mobile,realname,realname realname1,username")->where($where)->select();
        $list = set_medias($list, ['avatar']);

        $result = [
            'list' => $list
        ];
        return $this->template('query', $result);
    }
}