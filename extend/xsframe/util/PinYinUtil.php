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

use xsframe\enum\ChinesePinyinEnum;

class PinYinUtil
{
    // 获取首字母
    public static function getFirstPinyin($string): string
    {
        $first  = '';
        $pinyin = self::ChineseToPinyin($string);
        if (!empty($pinyin)) {
            $first = strtoupper(substr($pinyin, 0, 1));
        }
        if (!empty($first) && (ord($first) < 65 || ord($first) > 90)) {
            return '';
        }
        return $first;
    }

    /**
     * @desc convert chinese(include letter & number) to pinyin
     * @param string $string
     * @param boolean $isSimple
     * @param boolean $isInitial
     * @param boolean $isPolyphone
     * @param boolean $isAll
     * @return mixed
     **/
    public static function ChineseToPinyin($string, $isSimple = true, $isInitial = false, $isPolyphone = false, $isAll = false)
    {
        $result = null;

        if (empty($string)) {
            return $result;
        }

        $arrFullPinyin    = array();
        $arrInitialPinyin = array();
        $arrStringList    = self::splitString($string);

        if (!is_array($arrStringList)) {
            return $result;
        }

        $arrPinyinList = self::toPinyinList($arrStringList);

        if (!is_array($arrPinyinList)) {
            return $result;
        }

        if ($isSimple === true) {
            foreach ($arrPinyinList as $arrPinyin) {
                if (empty($arrPinyin)) {
                    continue;
                }
                $result .= $arrPinyin[0];
            }

            return $result;
        }

        $arrFirstPinyin = array_shift($arrPinyinList);

        if (($isInitial !== true) || ($isAll === true)) {
            $arrPrevPinyin = $arrFirstPinyin;
            foreach ($arrPinyinList as $arrPinyin) {
                $arrFullPinyin = array();
                foreach ($arrPrevPinyin as $strPrevPinyin) {
                    foreach ($arrPinyin as $strPinyin) {
                        $arrFullPinyin[] = $strPrevPinyin . $strPinyin;
                    }
                }
                $arrPrevPinyin = $arrFullPinyin;
            }
        }

        if (($isInitial === true) || ($isAll === true)) {
            if (ord($arrFirstPinyin[0]) > 129) {
                $arrPrevInitialPinyin[0] = $arrFirstPinyin[0];
            } else {
                $arrPrevInitialPinyin[0] = substr($arrFirstPinyin[0], 0, 1);
            }
            foreach ($arrPinyinList as $arrPinyin) {
                $arrInitialPinyin = array();
                foreach ($arrPrevInitialPinyin as $strPrevPinyin) {
                    foreach ($arrPinyin as $strPinyin) {
                        if (ord($strPinyin) > 129) {
                            $arrInitialPinyin[] = $strPrevPinyin . $strPinyin;
                        } else {
                            $arrInitialPinyin[] = $strPrevPinyin . substr($strPinyin, 0, 1);
                        }
                    }
                }
                $arrPrevInitialPinyin = $arrInitialPinyin;
            }
        }

        if ($isAll === true) {
            $result['full']    = $arrFullPinyin;
            $result['initial'] = $arrInitialPinyin;
        } elseif ($isPolyphone === true) {
            if (($isInitial === true)) {
                $result = $arrInitialPinyin;
            } else {
                $result = $arrFullPinyin;
            }
        } else {
            if (($isInitial === true)) {
                $result = reset($arrInitialPinyin);
            } else {
                $result = reset($arrFullPinyin);
            }
        }

        return $result;
    }

    /**
     * @desc split string
     * @param string $string
     * @return array
     **/
    private static function splitString($string)
    {
        $arrResult = array();

        $intLen = mb_strlen($string);
        while ($intLen) {
            $arrResult[] = mb_substr($string, 0, 1, 'utf8');
            $string      = mb_substr($string, 1, $intLen, 'utf8');
            $intLen      = mb_strlen($string);
        }

        return $arrResult;
    }

    /**
     * @desc change to single character list to pinyin list
     * @param array $arrStringList
     * @return array
     **/
    private static function toPinyinList($arrStringList)
    {
        $arrResult = array();

        if (!is_array($arrStringList)) {
            return $arrResult;
        }

        foreach ($arrStringList as $string) {
            switch (strlen($string)) {
                case 1:
                    $arrResult[] = array($string);
                    break;
                case 3:
                    if (isset(ChinesePinyinEnum::$arrChinesePinyinTable[$string])) {
                        $arrResult[] = ChinesePinyinEnum::$arrChinesePinyinTable[$string];
                    } else {
                        $arrResult[] = array($string);
                    }
                    break;
                default :
                    $arrResult[] = array($string);
            }
        }

        return $arrResult;
    }
}