<?php

// +----------------------------------------------------------------------
// | 星数为来数字化开发引擎 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 星数为来(杭州)科技有限公司
// +----------------------------------------------------------------------

namespace app\admin\service;

use xsframe\base\BaseService;

class CommonService extends BaseService
{
    public function getAreas($new_area = false)
    {
        if (!empty($new_area)) {
            $file = IA_ROOT . '/public/app/admin/static/components/area/AreaNew.xml';
        } else {
            $file = IA_ROOT . '/public/app/admin/static/components/area/Area.xml';
        }

        $file_str = file_get_contents($file);
        $areas = json_decode(json_encode(simplexml_load_string($file_str)), true);

        if (!empty($new_area) && !empty($areas['province'])) {
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

    public function getProvinceNameByCode($areaCode = '')
    {
        $areas = $this->getAreas(true);
        foreach ($areas['province'] as $k => $v) {
            if ($v['@attributes']['code'] == $areaCode) {
                return $v['@attributes']['name'];
            }
        }
        return '';
    }

    // type = province|city|area
    public function getProvinceCodeByName($name = '', $type = '')
    {
        $areas = $this->getAreas(true);
        foreach ($areas['province'] as $k => $v) {
            if ($v['@attributes']['name'] == $name) {
                return $v['@attributes']['code'];
            } else {
                if ($type != 'province') {
                    foreach ($v['city'] as $k1 => $v1) {
                        if ($v1['@attributes']['name'] == $name) {
                            return $v1['@attributes']['code'];
                        } else {
                            if ($type != 'city') {
                                foreach ($v['city'] as $k2 => $v2) {
                                    if ($v2['@attributes']['name'] == $name) {
                                        return $v2['@attributes']['code'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return '';
    }
}