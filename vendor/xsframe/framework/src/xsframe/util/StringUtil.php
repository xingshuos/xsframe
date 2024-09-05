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

class StringUtil
{

    /**
     * 是否是json
     * @param $string
     * @return bool
     */
    public static function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * 是否包含子串
     */
    public static function strexists($string, $find)
    {
        $isExists = false;
        if (!empty($string) && !empty($find) && is_string($string) && is_string($find)) {
            $isExists = !(strpos((string)$string, (string)$find) === false);
        }
        return $isExists;
    }

    /**
     * 字符串加密
     * @param $string
     * @return string
     */
    public static function strEnCode($string)
    {
        return self::strCode($string, 'ENCODE');
    }

    /**
     * 字符串解密
     * @param $string
     * @return string
     */
    public static function strDeCode($string)
    {
        return self::strCode($string, 'DECODE');
    }

    /**
     * 字符串加密解密
     * @param $string
     * @param string $action
     * @param string $key
     * @return string
     */
    public static function strCode($string, $action = 'ENCODE', $key = 'sc')
    {
        $action != 'ENCODE' && $string = base64_decode($string);
        $code = '';
        // $key = substr(md5($_SERVER['HTTP_USER_AGENT']), 8, 18);
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k = $i % $keyLen;
            $code .= $string[$i] ^ $key[$k];
        }
        return ($action != 'DECODE' ? base64_encode($code) : $code);
    }

    // 生成短链接
    public static function short($str)
    {
        $code = floatval(sprintf('%u', crc32($str)));
        $sstr = '';
        while ($code) {
            $mod = fmod($code, 62);
            if ($mod > 9 && $mod <= 35) {
                $mod = chr($mod + 55);
            } else if ($mod > 35) {
                $mod = chr($mod + 61);
            }
            $sstr .= $mod;
            $code = floor($code / 62);
        }
        return $sstr;
    }

    // OssOauth
    public static function OssOauth($uri, $seconds, $privateKey = 'dg3HwR8cXrvrUXK', $uid = 0)
    {
        $datetime = new \DateTime();
        $datetime->modify("+$seconds seconds");
        $time = $datetime->getTimestamp();
        $rand = 0;
        $string = '/' . $uri . '-' . $time . '-' . 0 . '-' . $uid . '-' . $privateKey;
        $hash = md5($string);
        $string = $time . '-' . $rand . '-' . $uid . '-' . $hash;
        return $string;
    }

    /**
     * 格式化字符截取 去掉 回车与html代码
     * @param $content
     * @param $start
     * @param $end
     * @return mixed|string
     */
    public static function formatSubStr($content, $start, $end)
    {
        $content = str_replace("\r", "", $content);
        $content = str_replace("\n", "", $content);
        $content = strip_tags($content);
        $content = trim($content);
        $content = mb_substr($content, $start, $end, 'utf8');
        return $content;
    }

    /**
     * 生成支付流水号, appid(2) + $pay_type(1) + channel(6) + yymmddHHMMSSssssss(18) + rand_number
     *
     * @param int $app_id 应用id
     * @param string $channel 渠道号
     *
     * @return string
     */
    public static function generateSN($app_id, $pay_type, $channel)
    {
        if (strlen($channel) > 6) {
            $channel = substr($channel, 0, 6);
        }
        $us_str = sprintf('%f', microtime(true));
        $arr = explode('.', $us_str);
        $sn = sprintf("%d%d%s%s%d", $app_id, $pay_type, $channel, date('ymdHis'), $arr[1]);
        $l = 32 - strlen($sn);
        if ($l > 0) {
            $num = rand(pow(10, $l - 1), pow(10, $l) - 1);
            $sn .= sprintf('%d', $num);
        }
        return $sn;
    }

    /**
     * 生成账单流水号, yymmddHHMMSSssssss + rand_number
     *
     * @return string
     * @throws string
     */
    public static function generateBillSN()
    {
        $us_str = sprintf('%f', microtime(true));
        $arr = explode('.', $us_str);
        $sn = sprintf("%s%d", date('ymdHis'), $arr[1]);
        $l = 32 - strlen($sn);
        if ($l > 0) {
            $num = rand(pow(10, $l - 1), pow(10, $l) - 1);
            $sn .= sprintf('%d', $num);
        }
        return $sn;
    }

    /**
     * 生成随机字符串
     * @param $length
     * @param bool $numeric
     */
    public static function randomStr($length, $numeric = false)
    {
        RandomUtil::random($length, $numeric);
    }

    public static function formatPrice($price)
    {
        return sprintf('%.02f', (int)$price / 100.0);
    }

    public static function formatBean($bean)
    {
        return sprintf('%.02f', (int)$bean / 100.0);
    }

    public static function formatSetPrice($price)
    {
        return (int)round((float)$price * 100);
    }

    public static function hideStr($string, $start = 0, $length = 0, $re = '*')
    {
        if (empty($string))
            return '';
        $strarr = [];
        $mb_strlen = mb_strlen($string);
        while ($mb_strlen) {//循环把字符串变为数组
            $strarr[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $mb_strlen, 'utf8');
            $mb_strlen = mb_strlen($string);
        }
        $strlen = count($strarr);
        $begin = $start >= 0 ? $start : ($strlen - abs($start));
        $end = $last = $strlen - 1;
        if ($length > 0) {
            $end = $begin + $length - 1;
        } else if ($length < 0) {
            $end -= abs($length);
        }
        for ($i = $begin; $i <= $end; $i++) {
            $strarr[$i] = $re;
        }
        return implode('', $strarr);
    }

    /**
     * 判断字符串是否为空
     *
     * @param string $str
     * @return bool
     */
    public static function emptyStr($str)
    {
        return '' === $str;
    }

    /**
     * 下划线转驼峰
     * @param $unCamelizeWords
     * @param string $separator
     * @return string
     */
    public static function camelize($unCamelizeWords, $separator = '_')
    {
        $unCamelizeWords = $separator . str_replace($separator, " ", strtolower($unCamelizeWords));
        return ltrim(str_replace(" ", "", ucwords($unCamelizeWords)), $separator);
    }

    /**
     * 驼峰命名转下划线命名
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    public static function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * str字符串是否以needle为开头
     *
     * @param $str
     * @param $needle
     * @return bool
     */
    public static function startsWith($str, $needle)
    {
        return strpos($str, $needle) === 0;
    }

    /**
     * xml转数组
     * @param $string
     * @param string $class_name
     * @param int $options
     * @param string $ns
     * @param bool $is_prefix
     * @return bool|\SimpleXMLElement
     */
    public static function isimplexml_load_string($string, $class_name = 'SimpleXMLElement', $options = 0, $ns = '', $is_prefix = false)
    {
        libxml_disable_entity_loader(true);
        if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $string)) {
            return false;
        }
        return simplexml_load_string($string, $class_name, $options, $ns, $is_prefix);
    }

    /**
     * 数据脱敏
     * @param string $string 需要脱敏值
     * @param int $start 开始
     * @param int $length 结束
     * @param string $re 脱敏替代符号
     * @return bool|string
     * 例子:
     * dataDesensitization('18811113683', 3, 4); //188****3683
     * dataDesensitization('乐杨俊', 0, -1); //**俊
     */
    public static function dataDesensitization($string, $start = 0, $length = 0, $re = '*')
    {
        if (empty($string)) {
            return false;
        }
        $strarr = [];
        $mb_strlen = mb_strlen($string);
        while ($mb_strlen) {//循环把字符串变为数组
            $strarr[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $mb_strlen, 'utf8');
            $mb_strlen = mb_strlen($string);
        }
        $strlen = count($strarr);

        $begin = $start >= 0 ? $start : ($strlen - abs($start));
        $end = $last = $strlen - 1;
        if ($length > 0) {
            $end = $begin + $length - 1;
        } else if ($length < 0) {
            $end -= abs($length);
        }
        for ($i = $begin; $i <= $end; $i++) {
            $strarr[$i] = $re;
        }
        // dump([$begin,$end,$last,$strarr]);die;
        // if ($begin >= $end || $begin >= $last || $end > $last)
        //     return false;
        return implode('', $strarr);
    }


    /**
     * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
     * @param string $user_name 姓名
     * @param string $repeatStr 替换的字符
     * @param string $encode 字符编码
     * @return string 格式化后的姓名
     */
    public static function userNameCut($user_name, $repeatStr = '*', $encode = 'utf-8')
    {
        if (empty($user_name)) {
            return '***';
        }
        $length = mb_strlen($user_name, $encode);
        $firstStr = mb_substr($user_name, 0, 1, $encode);
        $lastStr = mb_substr($user_name, -1, 1, $encode);
        return $length == 2 ? $firstStr . str_repeat($repeatStr, $length - 1) : $firstStr . str_repeat($repeatStr, $length - 2) . $lastStr;
    }

    /**
     * 只保留字符串前四位和后四位
     * @param $str
     * @return string
     */
    public static function cardNoCut($str)
    {
        if (empty($str)) {
            return '***';
        }
        $length = strlen($str);
        $front = substr($str, 0, 4);
        $back = substr($str, -4, 4);
        return $length == 16 ? $front . str_repeat('*', $length - 8) . $back : $front . str_repeat('*', $length - 8) . $back;
    }

    // 去掉指定字符串以后的内容
    public static function rmStrEnd($str, $key = '')
    {
        $index = strpos($str, $key);
        return substr($str, 0, $index);
    }

    /**
     * 随机生成名字 拼团用
     */
    public static function getRandomName()
    {
        $arrXing = [
            '赵', '钱', '孙', '李', '周', '吴', '郑', '王', '冯', '陈', '褚', '卫', '蒋', '沈', '韩', '杨', '朱', '秦', '尤', '许', '何', '吕', '施', '张', '孔', '曹', '严', '华', '金', '魏', '陶', '姜', '戚', '谢', '邹', '喻', '柏', '水', '窦', '章', '云', '苏', '潘', '葛', '奚', '范', '彭', '郎', '鲁', '韦', '昌', '马', '苗', '凤', '花', '方', '任', '袁', '柳', '鲍', '史', '唐', '费', '薛', '雷', '贺', '倪', '汤', '滕', '殷', '罗', '毕', '郝', '安', '常', '傅', '卞', '齐', '元', '顾', '孟', '平', '黄', '穆', '萧', '尹', '姚', '邵', '湛', '汪', '祁', '毛', '狄', '米', '伏', '成', '戴', '谈', '宋', '茅', '庞', '熊', '纪', '舒', '屈', '项', '祝', '董', '梁', '杜', '阮', '蓝', '闵', '季', '贾', '路', '娄', '江', '童', '颜', '郭', '梅', '盛', '林', '钟', '徐', '邱', '骆', '高', '夏', '蔡', '田', '樊', '胡', '凌', '霍', '虞', '万', '支', '柯', '管', '卢', '莫', '柯', '房', '裘', '缪', '解', '应', '宗', '丁', '宣', '邓', '单', '杭', '洪', '包', '诸', '左', '石', '崔', '吉', '龚', '程', '嵇', '邢', '裴', '陆', '荣', '翁', '荀', '于', '惠', '甄', '曲', '封', '储', '仲', '伊', '宁', '仇', '甘', '武', '符', '刘', '景', '詹', '龙', '叶', '幸', '司', '黎', '溥', '印', '怀', '蒲', '邰', '从', '索', '赖', '卓', '屠', '池', '乔', '胥', '闻', '莘', '党', '翟', '谭', '贡', '劳', '逄', '姬', '申', '扶', '堵', '冉', '宰', '雍', '桑', '寿', '通', '燕', '浦', '尚', '农', '温', '别', '庄', '晏', '柴', '瞿', '阎', '连', '习', '容', '向', '古', '易', '廖', '庾', '终', '步', '都', '耿', '满', '弘', '匡', '国', '文', '寇', '广', '禄', '阙', '东', '欧', '利', '师', '巩', '聂', '关', '荆', '司马', '上官', '欧阳', '夏侯', '诸葛', '闻人', '东方', '赫连', '皇甫', '尉迟', '公羊', '澹台', '公冶', '宗政', '濮阳', '淳于', '单于', '太叔', '申屠', '公孙', '仲孙', '轩辕', '令狐', '徐离', '宇文', '长孙', '慕容', '司徒', '司空'
        ];
        $numXing = count($arrXing);
        $arrMing = [
            '伟', '刚', '勇', '毅', '俊', '峰', '强', '军', '平', '保', '东', '文', '辉', '力', '明', '永', '健', '世', '广', '志', '义', '兴', '良', '海', '山', '仁', '波', '宁', '贵', '福', '生', '龙', '元', '全', '国', '胜', '学', '祥', '才', '发', '武', '新', '利', '清', '飞', '彬', '富', '顺', '信', '子', '杰', '涛', '昌', '成', '康', '星', '光', '天', '达', '安', '岩', '中', '茂', '进', '林', '有', '坚', '和', '彪', '博', '诚', '先', '敬', '震', '振', '壮', '会', '思', '群', '豪', '心', '邦', '承', '乐', '绍', '功', '松', '善', '厚', '庆', '磊', '民', '友', '裕', '河', '哲', '江', '超', '浩', '亮', '政', '谦', '亨', '奇', '固', '之', '轮', '翰', '朗', '伯', '宏', '言', '若', '鸣', '朋', '斌', '梁', '栋', '维', '启', '克', '伦', '翔', '旭', '鹏', '泽', '晨', '辰', '士', '以', '建', '家', '致', '树', '炎', '德', '行', '时', '泰', '盛', '雄', '琛', '钧', '冠', '策', '腾', '楠', '榕', '风', '航', '弘', '秀', '娟', '英', '华', '慧', '巧', '美', '娜', '静', '淑', '惠', '珠', '翠', '雅', '芝', '玉', '萍', '红', '娥', '玲', '芬', '芳', '燕', '彩', '春', '菊', '兰', '凤', '洁', '梅', '琳', '素', '云', '莲', '真', '环', '雪', '荣', '爱', '妹', '霞', '香', '月', '莺', '媛', '艳', '瑞', '凡', '佳', '嘉', '琼', '勤', '珍', '贞', '莉', '桂', '娣', '叶', '璧', '璐', '娅', '琦', '晶', '妍', '茜', '秋', '珊', '莎', '锦', '黛', '青', '倩', '婷', '姣', '婉', '娴', '瑾', '颖', '露', '瑶', '怡', '婵', '雁', '蓓', '纨', '仪', '荷', '丹', '蓉', '眉', '君', '琴', '蕊', '薇', '菁', '梦', '岚', '苑', '婕', '馨', '瑗', '琰', '韵', '融', '园', '艺', '咏', '卿', '聪', '澜', '纯', '毓', '悦', '昭', '冰', '爽', '琬', '茗', '羽', '希', '欣', '飘', '育', '滢', '馥', '筠', '柔', '竹', '霭', '凝', '晓', '欢', '霄', '枫', '芸', '菲', '寒', '伊', '亚', '宜', '可', '姬', '舒', '影', '荔', '枝', '丽', '阳', '妮', '宝', '贝', '初', '程', '梵', '罡', '恒', '鸿', '桦', '骅', '剑', '娇', '纪', '宽', '苛', '灵', '玛', '媚', '琪', '晴', '容', '睿', '烁', '堂', '唯', '威', '韦', '雯', '苇', '萱', '阅', '彦', '宇', '雨', '洋', '忠', '宗', '曼', '紫', '逸', '贤', '蝶', '菡', '绿', '蓝', '儿', '翠', '烟'
        ];
        $numMing = count($arrMing);
        $xing = $arrXing[mt_rand(0, $numXing - 1)];
        $ming = $arrMing[mt_rand(0, $numMing - 1)];
        return $xing . $ming;
    }

    /**
     * 数字转大写
     *
     * @param $number
     * @return string|string[]
     */
    public static function getUpperNumber($number = null)
    {
        $numbers = [
            1  => "一",
            2  => "二",
            3  => "三",
            4  => "四",
            5  => "五",
            6  => "六",
            7  => "七",
            8  => "八",
            9  => "九",
            10 => "十",
            11 => "十一",
            12 => "十二",
            13 => "十三",
            14 => "十四",
            15 => "十五",
            16 => "十六",
            17 => "十七",
            18 => "十八",
            19 => "十九",
            20 => "二十",
            21 => "二十一",
            22 => "二十二",
            23 => "二十三",
            24 => "二十四",
            25 => "二十五",
            26 => "二十六",
            27 => "二十七",
            28 => "二十八",
            29 => "二十九",
            30 => "三十",
            31 => "三十一",
            32 => "三十二",
            33 => "三十三",
            34 => "三十四",
            35 => "三十五",
            36 => "三十六",
            37 => "三十七",
            38 => "三十八",
            39 => "三十九",
            40 => "四十",
        ];
        if (empty($number)) {
            return $numbers;
        } else {
            return array_key_exists($number, $numbers) ? $numbers[$number] : "未知";
        }
    }

    // 将字符串拆分成数组
    public static function groupString($str, $groupSize = 11): array
    {
        $result = [];
        $length = mb_strlen($str, 'UTF-8'); // 使用 mb_strlen 函数并指定字符编码为 UTF-8

        for ($i = 0; $i < $length; $i += $groupSize) {
            $group = mb_substr($str, $i, $groupSize, 'UTF-8'); // 使用 mb_substr 函数并指定字符编码为 UTF-8
            $result[] = $group;
        }
        return $result;
    }

    // 递增版本号
    public static function incrementVersion($version): string
    {
        // 使用explode函数将版本号分割成数组
        $parts = explode('.', $version);

        // 假设版本号的最后一部分是要递增的
        $lastPart = intval($parts[count($parts) - 1]);

        // 递增最后一部分
        $lastPart++;

        // 重新构建版本字符串，除了最后一部分递增外，其他部分保持不变
        $parts[count($parts) - 1] = strval($lastPart);

        // 使用implode函数将数组重新组合成版本号字符串
        return implode('.', $parts);
    }

    // 两个版本号进行比较 1表示第一个版本号更大，-1表示第二个版本号更大 ，函数返回0，表示两个版本号相同
    public static function compareVersions($version1, $version2): int
    {
        // 将两个版本号分割成数组
        $parts1 = explode('.', $version1);
        $parts2 = explode('.', $version2);

        // 确定比较的版本部分数量
        $length = max(count($parts1), count($parts2));

        // 遍历每个部分进行比较
        for ($i = 0; $i < $length; $i++) {
            $part1 = isset($parts1[$i]) ? intval($parts1[$i]) : 0;
            $part2 = isset($parts2[$i]) ? intval($parts2[$i]) : 0;

            // 如果当前部分不相等，返回比较结果
            if ($part1 != $part2) {
                return ($part1 > $part2) ? 1 : -1;
            }
        }

        // 如果所有部分都相等，返回0表示版本相同
        return 0;
    }
}