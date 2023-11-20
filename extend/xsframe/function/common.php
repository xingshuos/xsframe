<?php


error_reporting(E_ALL ^ E_NOTICE);


use xsframe\wrapper\SettingsWrapper;
use xsframe\enum\SysSettingsKeyEnum;
use xsframe\facade\wrapper\PermFacade;
use think\facade\Env;

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

// 应用公共文件

define('IA_ROOT', str_replace("\\", '/', dirname(dirname(dirname(dirname(__FILE__))))));
define('APP_PATH', IA_ROOT . "/app");

// 获取真实应用名称
if (!function_exists('realModuleName')) {
    function realModuleName($moduleName)
    {
        $map = config("app.app_map");
        $realModuleName = array_search($moduleName, $map);
        return $realModuleName ? $realModuleName : $moduleName;
    }
}

// 获取完整表
if (!function_exists('tablename')) {
    function tablename($table)
    {
        $tablepre = Env::get('database.prefix');
        return " `{$tablepre}{$table}` ";
    }
}

// 验证主菜单权限
if (!function_exists('cm')) {
    function cm($permUrl)
    {
        return PermFacade::checkPerm($permUrl, 1);
    }
}

// 验证子菜单权限
if (!function_exists('cs')) {
    function cs($permUrl)
    {
        return PermFacade::checkPerm($permUrl, 2);
    }
}

// 验证权限操作
if (!function_exists('cp')) {
    function cp($permUrl)
    {
        $appName = app('http')->getName();
        return PermFacade::checkPerm($appName . "/" . $permUrl, 3);
    }
}

// 返回分页数据
if (!function_exists('getPageNum')) {
    function getPageNum($total, $pageSize = 10)
    {
        $pageNum = 1;
        if ($pageSize < $total) {
            $pageNum = intval($total / $pageSize) + (($total % $pageSize) > 0 ? 1 : 0);
        }

        return $pageNum;
    }
}

// 返回json数据
if (!function_exists('show_json')) {
    function show_json($status = 1, $return = null)
    {
        $ret = array(
            'status' => $status,
            'result' => $status == 1 ? array('url' => referer()) : array()
        );
        if (!is_array($return)) {
            if ($return) {
                $ret['result']['message'] = $return;
            }
            die(json_encode($ret));
        } else {
            $ret['result'] = $return;
        }

        if (isset($return['url'])) {
            $ret['result']['url'] = (string)$return['url'];
        } else if ($status == 1) {
            $ret['result']['url'] = referer();
        }
        die(json_encode($ret));
    }
}

if (!function_exists('is_weixin')) {
    function is_weixin()
    {
        global $_W;

        if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
            return false;
        }

        return true;
    }
}

if (!function_exists('is_h5app')) {
    function is_h5app()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'CK 2.0')) {
            return true;
        }

        return false;
    }
}

if (!function_exists('is_ios')) {
    function is_ios()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            return true;
        }

        return false;
    }
}

if (!function_exists('is_mobile')) {
    function is_mobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\\d+|meego).+mobile|avantgo|bada\\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\\-(n|u)|c55\\/|capi|ccwa|cdm\\-|cell|chtm|cldc|cmd\\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\\-s|devi|dica|dmob|do(c|p)o|ds(12|\\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\\-|_)|g1 u|g560|gene|gf\\-5|g\\-mo|go(\\.w|od)|gr(ad|un)|haie|hcit|hd\\-(m|p|t)|hei\\-|hi(pt|ta)|hp( i|ip)|hs\\-c|ht(c(\\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\\-(20|go|ma)|i230|iac( |\\-|\\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\\/)|klon|kpt |kwc\\-|kyo(c|k)|le(no|xi)|lg( g|\\/(k|l|u)|50|54|\\-[a-w])|libw|lynx|m1\\-w|m3ga|m50\\/|ma(te|ui|xo)|mc(01|21|ca)|m\\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\\-2|po(ck|rt|se)|prox|psio|pt\\-g|qa\\-a|qc(07|12|21|32|60|\\-[2-7]|i\\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\\-|oo|p\\-)|sdk\\/|se(c(\\-|0|1)|47|mc|nd|ri)|sgh\\-|shar|sie(\\-|m)|sk\\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\\-|v\\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\\-|tdg\\-|tel(i|m)|tim\\-|t\\-mo|to(pl|sh)|ts(70|m\\-|m3|m5)|tx\\-9|up(\\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\\-|your|zeto|zte\\-/i', substr($useragent, 0, 4))) {
            return true;
        }

        return false;
    }
}

// 获取完整路径
if (!function_exists('referer')) {
    function referer($default = "")
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $referer = substr($referer, -1) == '?' ? substr($referer, 0, -1) : $referer;
        $referer = str_replace('&amp;', '&', $referer);
        $reurl = parse_url($referer);

        if (!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.' . $reurl['host']))) {
            $referer = getSiteRoot();
        } elseif (empty($reurl['host'])) {
            $referer = ($referer ? getSiteRoot() . './' . $referer : '');
        }
        return strip_tags($referer);
    }
}

// 获取站点域名
if (!function_exists('getSiteRoot')) {
    function getSiteRoot()
    {
        // return request()->root(true) . "/";
        return getScheme() . "://" . request()->host() . "/";
    }
}

// 获取scheme
if (!function_exists('getScheme')) {
    function getScheme()
    {
        $scheme = "http";
        if (isset($_SERVER['HTTPS']) && (('1' == $_SERVER['HTTPS']) || ('on' == strtolower($_SERVER['HTTPS'])))) {
            $scheme = "https";
        }
        if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            $scheme = "https";
        }
        return $scheme;
    }
}

// 字符串是否存在
if (!function_exists('strexists')) {
    function strexists($string, $find)
    {
        return !(strpos($string, $find) === false);
    }
}

// 补全图片路径
if (!function_exists('tomedia')) {
    function tomedia($src, $suffix = null, $uniacid = null)
    {
        if (empty($src)) {
            return '';
        }
        $t = strtolower($src);
        if ((substr($t, 0, 7) == 'http://') || (substr($t, 0, 8) == 'https://') || (substr($t, 0, 2) == '//')) {
            return $src;
        }

        if (substr($src, 0, 4) === "app/") {
            $hostUrl = getAttachmentUrl(false, $uniacid);
        } else {
            $hostUrl = getAttachmentUrl(true, $uniacid);
        }

        $src = $hostUrl . "/" . ltrim($src, '/');
        return $src . ($suffix ? $suffix : '');
    }
}

// 获取附件地址
if (!function_exists('getAttachmentUrl')) {
    function getAttachmentUrl($isAttachment = true, $uniacid = null)
    {
        global $_W;
        $hostUrl = request()->domain() . ($isAttachment ? "/attachment" : '');

        if (empty($uniacid)) {
            $module = app('http')->getName();
            $uniacid = $_W['uniacid'] ?? ($_COOKIE['uniacid'] ?? 0);

            if ($module == 'admin') {
                $uniacid = 0;
            }
        }

        $settings = [];
        if ($uniacid > 0) {
            $settingsController = new SettingsWrapper();
            $settings = $settingsController->getAccountSettings($uniacid, 'settings');

            if (empty($settings) || empty($settings['remote'])) {
                $settings = $settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);
            }
        }

        if (!empty($settings) && $settings['remote']['type'] > 0) {
            $remote = $settings['remote'];
            switch ($remote['type']) {
                case 1:
                    $hostUrl = $remote['ftp']['url'];
                    break;
                case 2:
                    $hostUrl = $remote['alioss']['url'];
                    break;
                case 3:
                    $hostUrl = $remote['qiniu']['url'];
                    break;
                case 4:
                    $hostUrl = $remote['cos']['url'];
                    break;
            }
        }
        return $hostUrl;
    }
}

// 补手机端路径
if (!function_exists('mobileUrl')) {
    function mobileUrl($src, $params = [])
    {
        if (empty($src)) {
            return '';
        }

        $paramsUrl = http_build_query($params);

        $t = strtolower($src);
        if ((substr($t, 0, 7) == 'http://') || (substr($t, 0, 8) == 'https://') || (substr($t, 0, 2) == '//')) {
            $url = $src;
        } else {
            $url = env('site.mRootUrl') . "/" . trim($src, '/');
        }

        if (strpos($src, '?') !== false) {
            $url = $url . "&" . $paramsUrl;
        } else {
            $url = $url . "?" . $paramsUrl;
        }

        return $url;
    }
}

// 文件鉴权访问
if (!function_exists('filePrivateKeyA')) {
    function filePrivateKeyA($filename)
    {
        $time = strtotime("+8 hours");
        $key = env('site.fileRootPrivateKey');
        $md5 = md5("/" . $filename . "-" . $time . "-0-0-" . $key);
        $auth_key = "?auth_key=" . $time . "-0-0-" . $md5;
        return $auth_key;
    }
}

// 补全视频路径
if (!function_exists('videoTomedia')) {
    function videoTomedia($src)
    {
        if (empty($src)) {
            return '';
        }
        $t = strtolower($src);
        if ((substr($t, 0, 7) == 'http://') || (substr($t, 0, 8) == 'https://') || (substr($t, 0, 2) == '//')) {
            return $src;
        }
        return env('site.videoRootUrl') . "/" . $src;
    }
}

// 设置图片完整路径
if (!function_exists('set_medias')) {
    function set_medias($list = array(), $fields = null, $suffix = null)
    {
        if (empty($list)) {
            return array();
        }

        if (empty($fields)) {
            foreach ($list as &$row) {
                $row = tomedia($row, $suffix);
            }

            return $list;
        }

        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        if (is_object($list)) {
            $list = $list->toArray();
        }

        if (is_array2($list)) {
            foreach ($list as $key => &$value) {
                foreach ($fields as $field) {
                    if (strexists($field, ".")) {
                        $str = explode(".", $field);
                        if (isset($value[$str[0]][$str[1]])) {
                            $value[$str[0]][$str[1]] = tomedia($value[$str[0]][$str[1]], $suffix);
                        }
                    }

                    if (isset($list[$field])) {
                        $list[$field] = tomedia($list[$field], $suffix);
                    }

                    if (is_array($value) && isset($value[$field])) {
                        $value[$field] = tomedia($value[$field], $suffix);
                    }
                }
            }

            return $list;
        }

        foreach ($fields as $field) {
            if (isset($list[$field])) {
                $list[$field] = tomedia($list[$field], $suffix);
            }
        }

        return $list;
    }
}

// 是否二维数组
if (!function_exists('is_array2')) {
    function is_array2($array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                return is_array($v);
            }
            return false;
        }
        return false;
    }
}

if (!function_exists('iserializer')) {
    function iserializer($value)
    {
        return serialize($value);
    }
}

if (!function_exists('iunserializer')) {
    function iunserializer($value)
    {
        if (empty($value)) {
            return array();
        }
        if (!is_serialized($value)) {
            return $value;
        }
        $result = unserialize($value);
        if ($result === false) {
            $temp = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs) {
                return 's:' . strlen($matchs[2]) . ':"' . $matchs[2] . '";';
            }, $value);
            return unserialize($temp);
        } else {
            return $result;
        }
    }
}

if (!function_exists('is_serialized')) {
    function is_serialized($data, $strict = true)
    {
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            if (false === $semicolon && false === $brace)
                return false;
            if (false !== $semicolon && $semicolon < 3)
                return false;
            if (false !== $brace && $brace < 4)
                return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            case 'a' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'O' :
                return false;
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }
}

if (!function_exists('authcode')) {
    function authcode($string, $operation = 'DECODE', $key = 'xsframe', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key != 'xsframe' ? $key : 'xsframe');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $operation = strtoupper($operation);
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            try {
                if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                    return substr($result, 26);
                } else {
                    return '';
                }
            } catch (Exception $exception) {
                return '';
            }

        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}

if (!function_exists('is_mobile_request')) {
    function is_mobile_request()
    {
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
        $mobile_browser = '0';
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
            $mobile_browser++;
        if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false))
            $mobile_browser++;
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
            $mobile_browser++;
        if (isset($_SERVER['HTTP_PROFILE']))
            $mobile_browser++;
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda', 'xda-'
        );
        if (in_array($mobile_ua, $mobile_agents))
            $mobile_browser++;
        if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
            $mobile_browser++;
        // Pre-final check to reset everything if the user is on Windows
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
            $mobile_browser = 0;
        // But WP7 is also Windows, with a slightly different characteristic
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
            $mobile_browser++;
        if ($mobile_browser > 0)
            return true;
        else
            return false;
    }
}

if (!function_exists('isetcookie')) {
    function isetcookie($key, $value, $expire = 0, $httponly = false)
    {
        $expire = $expire != 0 ? (time() + $expire) : 0;
        $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
        return setcookie($key, $value, $expire, "/", "", $secure, $httponly);
    }
}

if (!function_exists('is_wechat')) {
    function is_wechat()
    {
        if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
            return false;
        }
        return true;
    }
}

if (!(function_exists("encrypt"))) {
    function encrypt($string, $key, $iv)
    {
        $data = openssl_encrypt($string, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $data = strtolower(bin2hex($data));
        return $data;
    }
}

if (!(function_exists("decrypt"))) {
    function decrypt($string, $key, $iv)
    {
        $decrypted = openssl_decrypt(hex2bin($string), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }
}

if (!function_exists('sizeCount')) {
    function sizeCount($size)
    {
        if ($size >= 1073741824) {
            $size = round($size / 1073741824 * 100) / 100 . ' GB';
        } elseif ($size >= 1048576) {
            $size = round($size / 1048576 * 100) / 100 . ' MB';
        } elseif ($size >= 1024) {
            $size = round($size / 1024 * 100) / 100 . ' KB';
        } else {
            $size = $size . ' Bytes';
        }
        return $size;
    }
}

if (!function_exists('byteCount')) {
    function byteCount($str)
    {
        if (strtolower($str[strlen($str) - 1]) == 'b') {
            $str = substr($str, 0, -1);
        }
        if (strtolower($str[strlen($str) - 1]) == 'k') {
            return floatval($str) * 1024;
        }
        if (strtolower($str[strlen($str) - 1]) == 'm') {
            return floatval($str) * 1048576;
        }
        if (strtolower($str[strlen($str) - 1]) == 'g') {
            return floatval($str) * 1073741824;
        }
    }
}

if (!function_exists('parsePath')) {
    function parsePath($path)
    {
        $danger_char = array('../', '{php', '<?php', '<%', '<?', '..\\', '\\\\', '\\', '..\\\\', '%00', '\0', '\r');
        foreach ($danger_char as $char) {
            if (strexists($path, $char)) {
                return false;
            }
        }
        return $path;
    }
}

if (!function_exists('starts_with')) {
    function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('searchUrl')) {
    /**
     * Url生成 保留其他参数
     * @param string $url 路由地址
     * @param array $vars 变量
     * @param bool|string $suffix 生成的URL后缀
     * @param bool|string $domain 域名
     */
    function searchUrl(string $url = '', array $vars = [], $suffix = true, $domain = false)
    {
        $params = request()->param();
        return \think\facade\Route::buildUrl($url, array_filter(array_merge($params, $vars)))->suffix($suffix)->domain($domain);
    }
}