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

class RequestUtil
{
    // HTTP get 工具
    public static function httpGet($url, $extra = [], $second = 2000)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);

        if (!empty($extra) && is_array($extra)) {
            $headers = [];
            foreach ($extra as $opt => $value) {
                if (StringUtil::strexists($opt, 'CURLOPT_')) {
                    curl_setopt($ch, constant($opt), $value);
                } else if (is_numeric($opt)) {
                    curl_setopt($ch, $opt, $value);
                } else {
                    $headers[] = "{$opt}: {$value}";
                }
            }

            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        }

        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    //HTTP post 工具
    public static function httpPost($url, $postData, $extra = [], $isShowHeader = 0, $timeout = 30, $isShowHeaderOut = 0)
    {
        // 模拟提交数据函数
        $ch = curl_init(); // 启动一个CURL会话

        curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'); // 模拟用户使用的浏览器
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); // Post提交的数据包
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_HEADER, $isShowHeader); // 返回 response 头部信息
        curl_setopt($ch, CURLINFO_HEADER_OUT, $isShowHeaderOut); // TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求 header

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); // 设置请求方式

        // 设置header
        if (!empty($extra)) {
            if (!empty($extra) && is_array($extra)) {
                $headers = [];
                foreach ($extra as $opt => $value) {
                    if (StringUtil::strexists($opt, 'CURLOPT_')) {
                        curl_setopt($ch, constant($opt), $value);
                    } else if (is_numeric($opt)) {
                        curl_setopt($ch, $opt, $value);
                    } else {
                        $headers[] = "{$opt}: {$value}";
                    }
                }
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
            }
        }

        // 发出请求
        $response = curl_exec($ch);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode !== 200) {
            return $statusCode;
        }

        curl_close($ch); // 关闭CURL会话
        return $response;
    }

    //HTTP POST JSON 工具
    public static function httpPostJson($url, $dataString, $extra = [])
    {
        if (is_array($dataString)) {
            $dataString = json_encode($dataString);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //解决内容体过大问题
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //解决内容体过大问题
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        // 设置header
        if (!empty($extra)) {
            if (is_array($extra)) {
                $headers = [
                    'Content-Type: application/json; charset=utf-8',
                    'Expect: ', //解决内容体过大问题
                    'Content-Length: ' . strlen($dataString),
                ];
                foreach ($extra as $opt => $value) {
                    if (StringUtil::strexists($opt, 'CURLOPT_')) {
                        curl_setopt($ch, constant($opt), $value);
                    } else if (is_numeric($opt)) {
                        curl_setopt($ch, $opt, $value);
                    } else {
                        $headers[] = "{$opt}: {$value}";
                    }
                }
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
            }
        }

        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); // 关闭CURL会话
        return $return_content;
    }

    // 获取随机ip地址
    public static function randIPHeader(): array
    {
        $ip_long = [
            ['607649792', '608174079'], //36.56.0.0-36.63.255.255
            ['1038614528', '1039007743'], //61.232.0.0-61.237.255.255
            ['1783627776', '1784676351'], //106.80.0.0-106.95.255.255
            ['2035023872', '2035154943'], //121.76.0.0-121.77.255.255
            ['2078801920', '2079064063'], //123.232.0.0-123.235.255.255
            ['-1950089216', '-1948778497'], //139.196.0.0-139.215.255.255
            ['-1425539072', '-1425014785'], //171.8.0.0-171.15.255.255
            ['-1236271104', '-1235419137'], //182.80.0.0-182.92.255.255
            ['-770113536', '-768606209'], //210.25.0.0-210.47.255.255
            ['-569376768', '-564133889'], //222.16.0.0-222.95.255.255
        ];

        $rand_key = mt_rand(0, 9);
        $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));

        $headers['CLIENT-IP'] = $ip;
        $headers['X-FORWARDED-FOR'] = $ip;
        return $headers;
    }

    public static function cloudHttpPost($url, array $postData = [])
    {
        if (is_array($postData)) {
            $postData['host_ip'] = $_SERVER['REMOTE_ADDR'];
            $postData['host_url'] = $_SERVER['HTTP_HOST'];
            $postData['version'] = IMS_VERSION;
            $postData['php_version'] = PHP_VERSION;
        }

        $response = self::httpPost("https://www.xsframe.cn/cloud/api/" . $url, $postData);
        $result = @json_decode($response, true);

        if (!empty($result)) {
            return false;
        } else {
            return $response;
        }
    }
}
