<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\util;

class ImgUtil
{
    public static function createPosterImg($imgPath, $bgUrl, $data = null)
    {
        set_time_limit(50);
        @ini_set('memory_limit', '256M');

        $bg       = self::createImage($bgUrl);
        $img_info = getimagesize($bgUrl);

        $width  = $img_info[0];
        $height = $img_info[1];
        $target = imagecreatetruecolor($width, $height);
        imagecopy($target, $bg, 0, 0, 0, 0, $width, $height);
        imagedestroy($bg);

        foreach ($data as $d) {
            $d = self::getRealData($d);
            if ($d['type'] == 'qrcode') {
                $target = self::mergeImage($target, $d['src'], $d['left'], $d['top'], $d['width'], $d['height']);
            } else if ($d['type'] == 'createtime') {
                $target = self::mergeText($target, $d, date('Y-m-d H:i', time()));
            } else if ($d['type'] == 'logo') {
                $target = self::mergeImage($target, $d['src'], $d['left'], $d['top'], $d['width'], $d['height']);
            }
        }

        imagejpeg($target, $imgPath);
        imagedestroy($target);
    }

    /**
     * 图片定位
     */
    public static function getRealData($data)
    {
        $data['left']   = intval(str_replace('px', '', $data['left']));
        $data['top']    = intval(str_replace('px', '', $data['top']));
        $data['width']  = intval(str_replace('px', '', $data['width']));
        $data['height'] = intval(str_replace('px', '', $data['height']));
        $data['size']   = intval(str_replace('px', '', $data['size']));
        return $data;
    }

    /**
     * 创建图片
     */
    public static function createImage($imgurl)
    {
        $resp = RequestUtil::request($imgurl);
        return imagecreatefromstring($resp);
    }

    /**
     * 绘制图片
     */
    public static function mergeImage($target, $imgurl, $left, $top, $width, $height)
    {
        $img = self::createImage($imgurl);
        $w   = imagesx($img);
        $h   = imagesy($img);
        imagecopyresized($target, $img, $left, $top, 0, 0, $width, $height, $w, $h);
        imagedestroy($img);
        return $target;
    }

    /**
     * 绘制文本
     */
    public static function mergeText($target, $data, $text)
    {
        $rootPath = str_replace("\\", "/", dirname(dirname(dirname(dirname(__file__)))));
        $font     = $rootPath . "/public/fonts/msyh.ttf";

        $colors = self::hex2rgb($data['color']);
        $color  = imagecolorallocate($target, $colors['red'], $colors['green'], $colors['blue']);

        $text = self::emoji($text);
        $text = mb_convert_encoding(strval($text), "html-entities", "utf-8");

        imagettftext($target, $data['size'], 0, $data['left'], $data['top'] + $data['size'], $color, $font, $text);
        return $target;
    }

    /**
     * 过滤emoji表情符号
     */
    public static function emoji($text)
    {
        $clean_text = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text     = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text   = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text     = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc  = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text    = preg_replace($regexDingbats, '', $clean_text);

        $regexDingbats = '/[\x{231a}-\x{23ab}\x{23e9}-\x{23ec}\x{23f0}-\x{23f3}]/u';
        $clean_text    = preg_replace($regexDingbats, '', $clean_text);

        return $clean_text;
    }

    /**
     * 文字颜色
     */
    public static function hex2rgb($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = array(
                $colour[0] . $colour[1],
                $colour[2] . $colour[3],
                $colour[4] . $colour[5]
            );
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = array(
                $colour[0] . $colour[0],
                $colour[1] . $colour[1],
                $colour[2] . $colour[2]
            );
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array(
            'red'   => $r,
            'green' => $g,
            'blue'  => $b
        );
    }

    // 补全加载图片lazy-load(pc端处理)
    public static function html2images($detail = '', $suffix = null, $clearVideoHeight = false, $imgLazy = false)
    {
        $detail = str_replace($suffix, "", $detail);
        $detail = htmlspecialchars_decode($detail);

        # 转换缩略图
        if ($suffix) {
            preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);

            $images = array();
            if (isset($imgs[1])) {
                $imgs[1] = array_unique($imgs[1]);
                foreach ($imgs[1] as $img) {
                    if (strpos($img, '?') !== false) {

                    } else {
                        $im       = array(
                            'old' => $img,
                            'new' => tomedia($img, $suffix)
                        );
                        $images[] = $im;
                    }
                }
            }

            foreach ($images as $img1) {
                $detail = str_replace($img1['old'], $img1['new'], $detail);
            }
        }

        # 图片懒加载
        if ($imgLazy) {
            preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);

            $images = array();
            if (isset($imgs[0])) {
                $imgs[0] = array_unique($imgs[0]);
                foreach ($imgs[0] as $img) {

                    $pattern = "/src=(.*?)/";
                    $newImg  = preg_replace($pattern, "class=\"lazy-load\" src='/app/gm_arts/static/images/nopic.png' data-original=", $img);

                    $im       = array(
                        'old' => $img,
                        'new' => $newImg
                    );
                    $images[] = $im;
                }
            }

            foreach ($images as $img1) {
                $detail = str_replace($img1['old'], $img1['new'], $detail);
            }
        }

        # 处理视频
        if ($clearVideoHeight) {

            preg_match_all('/<video.*?src=[\\\\\'| \\"](.*?(?:[\\.mp4|\\.avi]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $videos);
            $videosArr = array();
            if (isset($videos[0])) {
                $videos[0] = array_unique($videos[0]);
                foreach ($videos[0] as $videoStr) {

                    $pattern     = "/height=[\\\\'| \\\"](.*?)[\\\\'| \\\"]/";
                    $newVideoStr = preg_replace($pattern, "", $videoStr);

                    $pattern1    = "/style=[\\\\'|\\\"](.*?)[\\\\'|\\\"]/";
                    $newVideoStr = preg_replace($pattern1, " ", $newVideoStr);

                    $im          = array(
                        'old' => $videoStr,
                        'new' => $newVideoStr
                    );
                    $videosArr[] = $im;
                }
                unset($videoStr);
            }

            foreach ($videosArr as $videoDom) {
                $detail = str_replace($videoDom['old'], $videoDom['new'], $detail);
            }
        }

        return $detail;
    }
}