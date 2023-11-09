<?php

namespace app\store\service;

use xsframe\base\BaseService;

class GoodsTagsService extends BaseService
{
    protected $tableName = "shop_goods_tags";

    public function getFullCategory($condition, $fullName = false)
    {
        $allCategory = array();

        $category = $this->getAll($condition, "*", 'parentid ASC, displayorder DESC');

        if (empty($category)) {
            return array();
        }

        foreach ($category as &$c) {
            if (empty($c['parentid'])) {
                $allCategory[] = $c;

                foreach ($category as &$c1) {
                    if ($c1['parentid'] != $c['id']) {
                        continue;
                    }

                    if ($fullName) {
                        $c1['name'] = $c['name'] . '-' . $c1['name'];
                    }

                    $allCategory[] = $c1;

                    foreach ($category as &$c2) {
                        if ($c2['parentid'] != $c1['id']) {
                            continue;
                        }

                        if ($fullName) {
                            $c2['name'] = $c1['name'] . '-' . $c2['name'];
                        }

                        $allCategory[] = $c2;

                        foreach ($category as &$c3) {
                            if ($c3['parentid'] != $c2['id']) {
                                continue;
                            }

                            if ($fullName) {
                                $c3['name'] = $c2['name'] . '-' . $c3['name'];
                            }

                            $allCategory[] = $c3;
                        }

                        unset($c3);
                    }

                    unset($c2);
                }

                unset($c1);
            }

            unset($c);
        }

        return $allCategory;
    }
}