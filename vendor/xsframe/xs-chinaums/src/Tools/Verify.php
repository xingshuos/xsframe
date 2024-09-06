<?php

namespace xsframe\chinaums\Tools;

/**
 * 加密类
 */
class Verify
{
    public static function makeSign($md5Key, $params)
    {
        $str = self::buildSignStr($params) . $md5Key;
        if ($params['signType'] == 'SHA256') {
            return strtoupper(hash('sha256', $str));
        }
        return strtoupper(hash('md5', $str));
    }

    public static function buildSignStr($params)
    {
        $keys = [];
        foreach ($params as $key => $value) {
            if ($key == 'sign' || is_null($value)) {
                continue;
            }
            array_push($keys, $key);
        }
        $str = '';
        sort($keys);
        $len = count($keys);
        for ($i = 0; $i < $len; $i++) {
            $v = $params[$keys[$i]];
            if (is_array($v)) {
                $v = json_encode($v);
            }
            $str .= $keys[$i] . '=' . $v . (($i === $len - 1) ? '' : "&");
        }
        return $str;
    }
}
