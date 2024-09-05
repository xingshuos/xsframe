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

use think\Exception;

class ImgUtil
{
    public static function createPosterImg($imgPath, $bgUrl, $data = null)
    {
        //  PHP GD 库
        if (!extension_loaded('imagick')) {
            set_time_limit(50);
            @ini_set('memory_limit', '512M');

            $bg = self::createImage($bgUrl);
            $img_info = getimagesize($bgUrl);

            $width = $img_info[0];
            $height = $img_info[1];

            $target = imagecreatetruecolor($width, $height);
            imagecopy($target, $bg, 0, 0, 0, 0, $width, $height);
            imagedestroy($bg);

            if (!empty($data)) {
                foreach ($data as $d) {
                    $d = self::getRealData($d);
                    if ($d['type'] == 'img' && !empty($d['src'])) {
                        $target = self::mergeImage($target, $d['src'], $d['left'], $d['top'], $d['width'], $d['height'], $d['rotate'] ?? 0);
                    } else if ($d['type'] == 'text') {
                        $target = self::mergeText($target, $d['text'], $d['size'], $d['color'], $d['left'], $d['top']);
                    }
                }
            }

            imagejpeg($target, $imgPath); // 可接受的有损压缩
            // imagepng($target, $imgPath); // 原图像无损清晰度
            imagedestroy($target);
        } else {

            // 使用compositeImage方法将图片定位到背景上 // 这里使用的是Imagick::COMPOSITE_OVER复合方式，你可以根据需要选择其他方式
            // 如果你的源图像没有透明度，并且你只是想简单地替换背景图像上的某个区域，那么 Imagick::COMPOSITE_SRC 可能会更快，因为它不考虑目标图像的颜色，只使用源图像的颜色。
            // 如果你的源图像有透明度，并且你希望它在合成时与目标图像的颜色混合，那么 Imagick::COMPOSITE_OVER 是合适的。
            // 对于性能要求极高的场景，可以尝试使用 Imagick::COMPOSITE_COPY，但这种方式不会混合像素，而是直接复制源图像的像素到目标图像上，这可能会导致边缘出现锯齿或不自然的过渡。

            $backgroundPath = $bgUrl;

            // 创建一个Imagick对象来处理背景图片
            $background = new \Imagick($backgroundPath);
            $background->setImageFormat('png'); // 使用无损格式以保持质量（如果需要）

            $tempFileArr = [];
            if (!empty($data)) {

                /*将字体处理逻辑优化 start*/
                $draw = new \ImagickDraw();
                $rootPath = str_replace("\\", "/", dirname(dirname(dirname(dirname(__file__)))));
                $defaultFont = $d['font'] ?? $rootPath . "/public/app/admin/static/fonts/msyh.ttf";
                $defaultFontSize = 16; // 假设默认字体大小
                $textColor = '#000000';
                $defaultFillColor = new \ImagickPixel($textColor); // 假设默认为黑色
                $draw->setFont($defaultFont);
                $draw->setFontSize($defaultFontSize);
                $draw->setFillColor($defaultFillColor);
                /*将字体处理逻辑优化 end*/

                foreach ($data as $d) {
                    $d = self::getRealData($d);
                    if ($d['type'] == 'img' && !empty($d['src'])) {
                        $retPath = self::getImagePath($d['src']);
                        $imagePath = $retPath['filePath'];
                        $tempFile = $retPath['tempFile'];
                        if ($tempFile) {
                            $tempFileArr[] = $tempFile;
                        }
                        if (is_file($imagePath)) {
                            $mergeImage = new \Imagick($imagePath);
                            if ($d['rotate']) {
                                $mergeImage->rotateImage('none', -($d['rotate']));
                            }
                            $mergeImage->resizeImage($d['width'], $d['height'], \Imagick::FILTER_LANCZOS, 1);
                            $background->compositeImage($mergeImage, \Imagick::COMPOSITE_OVER, $d['left'], $d['top']);
                            $mergeImage->clear();
                            $mergeImage->destroy();
                        }
                    } else if ($d['type'] == 'text') {
                        $text = self::emoji($d['text']);

                        if ($d['font'] && is_file($d['font'])) {
                            $draw->setFont($d['font']);
                        }

                        if ($d['size']) {
                            $fontSize = $d['size'] * 1.35; // 如果需要根据每个文本项调整大小，在这里临时修改
                            $draw->setFontSize($fontSize); // 可选：只有当每个文本的大小可能不同时才在这里修改
                        }

                        if ($d['color'] && $d['color'] != $textColor) {
                            $draw->setFillColor(new \ImagickPixel($d['color']));
                        }

                        $background->annotateImage($draw, $d['left'], $d['top'] + $d['size'], $d['rotate'] ?? 0, $text); // 高度注意这里加上了字体大小
                    }
                }
            }

            $background->writeImage($imgPath);

            // 清理资源
            $background->clear();
            $background->destroy();

            // 删除临时文件
            foreach ($tempFileArr as $tempFile) {
                @unlink($tempFile);
            }
        }
    }

    // 获取文件路径(如果是远程地址就先下载在返回)
    public static function getImagePath($url)
    {
        $tempFile = null; // 临时文件记得删除
        if (!is_file($url) && strexists($url, 'http')) {
            $correctedUrl = self::extractProtocolAndDomain($url);
            $correctedUrl = str_replace($correctedUrl, "", $url);
            $imagePath = str_replace("/attachment/", "", $correctedUrl);

            if (!is_file($url)) {
                try {
                    $tempFile = tempnam(sys_get_temp_dir(), 'img');
                    ini_set('default_socket_timeout', 5);//设置默认超时时间为5秒
                    $getImageData = file_get_contents($url);
                    @file_put_contents($tempFile, $getImageData);
                    $url = $tempFile;
                } catch (Exception $exception) {
                    $url = "";
                }
            } else {
                $url = $imagePath;
            }
        }

        return [
            'filePath' => $url,
            'tempFile' => $tempFile,
        ];
    }


    // 读取域名
    public static function extractProtocolAndDomain($url)
    {
        // 正则表达式匹配http或https开头，后面跟着://，然后是任意非空白字符
        preg_match('/^(https?:\/\/)?([\w.-]+)(\/.*)?$/i', $url, $matches);

        // 提取出协议（可能为空）和域名
        $protocol = isset($matches[1]) && $matches[1] ? $matches[1] : '';
        $domain = $matches[2];

        return $protocol . $domain;
    }

    /**
     * 图片定位
     */
    public static function getRealData($data)
    {
        $data['left'] = intval(str_replace('px', '', $data['left']));
        $data['top'] = intval(str_replace('px', '', $data['top']));
        $data['width'] = intval(str_replace('px', '', $data['width']));
        $data['height'] = intval(str_replace('px', '', $data['height']));
        $data['size'] = intval(str_replace('px', '', $data['size']));
        return $data;
    }

    /**
     * 创建图片
     */
    public static function createImage($imgUrl)
    {
        if (strexists($imgUrl, 'http')) {
            $resp = RequestUtil::httpGet($imgUrl);
        } else {
            $resp = file_get_contents($imgUrl);
        }

        try {
            $imageData = imagecreatefromstring($resp);
        } catch (Exception $exception) {
            LoggerUtil::error("createImage imgUrl:" . $imgUrl);
            LoggerUtil::error("errorMsg:" . $exception->getMessage());
        }
        return $imageData;
    }

    /**
     * 绘制图片
     */
    public static function mergeImage($target, $imgUrl, $left, $top, $width, $height, $rotated = 0)
    {
        if (!empty($rotated)) {
            $image_info = getimagesize($imgUrl);
            $source = "";
            switch ($image_info['mime']) {
                case 'image/png':
                    // 处理 PNG 文件
                    $source = imagecreatefrompng($imgUrl);
                    break;
                case 'image/jpeg':
                    // 处理 JPEG 文件
                    $source = imagecreatefromjpeg($imgUrl);
                    break;
                case 'image/gif':
                    // 处理 GIF 文件
                    $source = imagecreatefromgif($imgUrl);
                    break;
            }

            $img = imagerotate($source, $rotated, 0); // 旋转90度
        } else {
            $img = self::createImage($imgUrl);
        }

        try {
            $w = imagesx($img);
            $h = imagesy($img);

            imagecopyresized($target, $img, $left, $top, 0, 0, $width, $height, $w, $h);
            imagedestroy($img);
        } catch (Exception $exception) {
            LoggerUtil::error("mergeImage imgUrl:" . $imgUrl);
            LoggerUtil::error("errorMsg:" . $exception->getMessage());
        }
        return $target;
    }

    /**
     * 绘制文本
     */
    public static function mergeText($target, $text, $size, $color, $left, $top)
    {
        $rootPath = str_replace("\\", "/", dirname(dirname(dirname(dirname(__file__)))));
        $font = $rootPath . "/public/app/admin/static/fonts/msyh.ttf";

        $colors = self::hex2rgb($color);
        $color = imagecolorallocate($target, $colors['red'], $colors['green'], $colors['blue']);

        $text = self::emoji($text);
        $text = mb_convert_encoding(strval($text), "html-entities", "utf-8");

        imagettftext($target, $size, 0, $left, $top + $size, $color, $font, $text);
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
        $clean_text = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        $regexDingbats = '/[\x{231a}-\x{23ab}\x{23e9}-\x{23ec}\x{23f0}-\x{23f3}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

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
            [$r, $g, $b] = [
                $colour[0] . $colour[1],
                $colour[2] . $colour[3],
                $colour[4] . $colour[5]
            ];
        } else if (strlen($colour) == 3) {
            [$r, $g, $b] = [
                $colour[0] . $colour[0],
                $colour[1] . $colour[1],
                $colour[2] . $colour[2]
            ];
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return [
            'red'   => $r,
            'green' => $g,
            'blue'  => $b
        ];
    }

    // 补全加载图片lazy-load(pc端处理)
    public static function html2images($detail = '', $suffix = null, $clearVideoHeight = false, $imgLazy = false)
    {
        $detail = str_replace($suffix, "", $detail);
        $detail = htmlspecialchars_decode($detail);

        # 补全图片路径
        if ($detail) {
            preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);

            $images = [];
            if (isset($imgs[1])) {
                $imgs[1] = array_unique($imgs[1]);
                foreach ($imgs[1] as $img) {
                    $images[] = $img;
                }
            }

            foreach ($images as $imgUrl) {
                $detail = str_replace("'" . $imgUrl, "'" . tomedia($imgUrl), $detail);
                $detail = str_replace('"' . $imgUrl, '"' . tomedia($imgUrl), $detail);
            }
        }

        # 转换缩略图
        if ($suffix) {
            preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);

            $images = [];
            if (isset($imgs[1])) {
                $imgs[1] = array_unique($imgs[1]);
                foreach ($imgs[1] as $img) {
                    if (strpos($img, '?') !== false) {

                    } else {
                        $im = [
                            'old' => $img,
                            'new' => tomedia($img, $suffix)
                        ];
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

            $images = [];
            if (isset($imgs[0])) {
                $imgs[0] = array_unique($imgs[0]);
                foreach ($imgs[0] as $img) {

                    $pattern = "/src=(.*?)/";
                    $newImg = preg_replace($pattern, "class=\"lazy-load\" src='/home/static/images/lazy_course.jpg' data-src=", $img);

                    $im = [
                        'old' => $img,
                        'new' => $newImg
                    ];
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
            $videosArr = [];
            if (isset($videos[0])) {
                $videos[0] = array_unique($videos[0]);
                foreach ($videos[0] as $videoStr) {

                    $pattern = "/height=[\\\\'| \\\"](.*?)[\\\\'| \\\"]/";
                    $newVideoStr = preg_replace($pattern, "", $videoStr);

                    $pattern1 = "/style=[\\\\'|\\\"](.*?)[\\\\'|\\\"]/";
                    $newVideoStr = preg_replace($pattern1, " ", $newVideoStr);

                    $im = [
                        'old' => $videoStr,
                        'new' => $newVideoStr
                    ];
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