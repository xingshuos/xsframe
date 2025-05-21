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

namespace xsframe\service;

use xsframe\base\BaseService;

class AreaService
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

    // 通过code获取名称 type = province|city|area
    public function getNameByCode($areaCode = '', $type = '')
    {
        $areas = $this->getAreas(true);
        foreach ($areas['province'] as $k => $v) {
            if ($v['@attributes']['code'] == $areaCode) {
                return $v['@attributes']['name'];
            } else {
                if ($type != 'province') {
                    foreach ($v['city'] as $k1 => $v1) {
                        if ($v1['@attributes']['code'] == $areaCode) {
                            return $v1['@attributes']['name'];
                        } else {
                            if ($type != 'city') {
                                foreach ($v['city'] as $k2 => $v2) {
                                    if ($v2['@attributes']['code'] == $areaCode) {
                                        return $v2['@attributes']['name'];
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

    // 通过名称获取code type = province|city|area
    public function getCodeByName($name = '', $type = '')
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

    // 格式化地址信息
    public function formatAreas($areas = []): array
    {
        $newAreas = [];

        foreach ($areas['province'] as $key => $item) {
            $nweCity = [];
            $level = 2;

            foreach ($item['city'] as $cityKey => $cityItem) {
                $nweCity[$cityKey]['name'] = $cityItem["@attributes"]['name'];
                $nweCity[$cityKey]['label'] = $cityItem["@attributes"]['name'];
                $nweCity[$cityKey]['code'] = $cityItem["@attributes"]['code'];
                $nweCity[$cityKey]['value'] = $cityKey + 1;

                foreach ($cityItem['county'] as $countyKey => $countyItem) {
                    if (!empty($countyItem["@attributes"]['name'])) {
                        $nweCity[$cityKey]['areas'][$countyKey]['name'] = $countyItem["@attributes"]['name'];
                        $nweCity[$cityKey]['areas'][$countyKey]['label'] = $countyItem["@attributes"]['name'];
                        $nweCity[$cityKey]['areas'][$countyKey]['code'] = $countyItem["@attributes"]['code'];
                        $nweCity[$cityKey]['areas'][$countyKey]['value'] = $countyKey + 1;
                        $level = 3;
                    }
                }

                if (!empty($nweCity[$cityKey]['areas'])) {
                    array_unshift($nweCity[$cityKey]['areas'], [
                        'name'  => '不限',
                        'label' => '不限',
                        'code'  => 0,
                        'value' => 0,
                    ]);
                }
            }

            if (!empty($nweCity)) {
                array_unshift($nweCity, [
                    'name'  => '不限',
                    'label' => '不限',
                    'code'  => 0,
                    'value' => 0,
                ]);
            }

            $provinceItem = [
                'name'   => $item["@attributes"]['name'],
                'label'  => $item["@attributes"]['name'],
                'code'   => $item["@attributes"]['code'],
                'value'  => $key,
                'level'  => $level,
                'cities' => $nweCity,
            ];

            $newAreas['province'][] = $provinceItem;
        }

        return $newAreas;
    }
}