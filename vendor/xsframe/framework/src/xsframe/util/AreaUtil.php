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

namespace xsframe\util;

class AreaUtil
{
    // 获取全部地址列表
    public static function getAreas($newArea = false)
    {
        if (!empty($newArea)) {
            $file = IA_ROOT . '/public/app/admin/static/components/area/AreaNew.xml';
        } else {
            $file = IA_ROOT . '/public/app/admin/static/components/area/Area.xml';
        }

        $file_str = file_get_contents($file);
        $areas = json_decode(json_encode(simplexml_load_string($file_str)), true);
        if (!empty($newArea) && !empty($areas['province'])) {
            foreach ($areas['province'] as $k => &$row) {
                if (0 < $k) {
                    if (empty($row['city'][0])) {
                        $row['city'][0]['@attributes'] = $row['city']['@attributes'];
                        $row['city'][0]['county'] = $row['city']['county'];
                        unset($row['city']['@attributes']);
                        unset($row['city']['county']);
                    }
                } else {
                    unset($areas['province'][0]);
                }

                foreach ($row['city'] as $k1 => $v1) {
                    if (empty($v1['county'][0])) {
                        $row['city'][$k1]['county'][0]['@attributes'] = $v1['county']['@attributes'];
                        unset($row['city'][$k1]['county']['@attributes']);
                    }
                }
            }

            unset($row);
        }

        return $areas;
    }
}