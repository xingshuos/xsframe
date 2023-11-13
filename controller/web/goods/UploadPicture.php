<?php

namespace app\xs_cloud\controller\web\goods;

use AlibabaCloud\SDK\Vod\V20170321\Models\CreateUploadVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\RefreshUploadVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Vod;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use app\store\facade\service\ChaptersItemServiceFacade;
use Darabonba\OpenApi\Models\Config;
use xsframe\base\AdminBaseController;
use xsframe\exception\ApiException;
use xsframe\util\FileUtil;

class UploadPicture extends AdminBaseController
{

    public function index()
    {
        $goodsId  = $this->params["goodsid"];
        $isUpdate = $this->params["is_update"] ?? 0; // 是否更新视频

        $result = [
            'goodsId'  => $goodsId,
            'isUpdate' => $isUpdate,
        ];

        return $this->template('web/goods/upload/picture', $result);
    }

    /**
     * 创建上传凭证
     * @throws ApiException
     */
    public function upload()
    {
        $file = request()->file('image');

        if (empty($file)) {
            return $this->error("请选择上传文件");
        }

        $folder = $this->getFolder("image");
        $ext    = strtolower($file->extension());

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'tif'])) {
            return $this->error("文件格式错误");
        }

        $attachmentPath = IA_ROOT . "/public/attachment/";

        $fileRootPath = $attachmentPath . $folder;

        $filenamePre = FileUtil::fileRandomName($fileRootPath);
        $filename    = $filenamePre . "." . $ext;

        // 开启设置上传大小限制 start
        set_time_limit(0);
        ini_set('memory_limit', '4096M');
        // 开启设置上传大小限制 end

        $source_image = $fileRootPath . $filenamePre . "/" . $filename;

        # 原图上传本地
        $fileRet = $file->move($fileRootPath . $filenamePre, $filename);
        if (empty($fileRet)) {
            return $this->error("本地文件上传失败");
        }

        # 接收图片信息
        $fileInfo         = json_decode($this->params['fileInfo'], true);
        $mainSourceWidth  = $fileInfo['width'];
        $mainSourceHeight = $fileInfo['height'];

        $imageSize = $fileRet->getSize();

        # 1.图片过大进行缩放一下
        $image = \think\Image::open($source_image);
        if (intval($mainSourceWidth) > 8000 || intval($mainSourceHeight) > 8000) {
            $mainSourceWidth  = $mainSourceWidth * 0.5;
            $mainSourceHeight = $mainSourceHeight * 0.5;
            $image->thumb($mainSourceWidth, $mainSourceHeight)->save($source_image);
            $imageSize = filesize($source_image);
        }

        // 2.如果图片大于 10 MB 图片进行压缩一次
        if (round($imageSize / 1048576, 1) > 10) {
            // 原图像进行压缩 start
            // 压缩后的图像保存路径
            $compressed_image = $fileRootPath . $filenamePre . "/" . $filename;

            // 压缩质量（0-100，100为最高质量）
            $compression_quality = 50; // 可根据需要进行调整

            // 打开原始图像
            $source = imagecreatefromjpeg($source_image);

            // 将图像保存为压缩后的图像，控制压缩质量
            imagejpeg($source, $compressed_image, $compression_quality);

            // 释放内存
            imagedestroy($source);
            // 原图像进行压缩 end
        }

        // 方法1客户端显示不完整
        $tileSize = 512;

        // $filePathFloder = [10, 11, 12, 13, 14, 15, 16, 17, 18];
        $filePathFloder = [18, 17, 16, 15, 14, 13, 12, 11, 10];

        foreach ($filePathFloder as $folderName) {
            # 1.生成文件夹 10/11/12/13/14/15/16/17/18
            $folderNamePath = $fileRootPath . $filenamePre . "/" . $folderName . "/";
            if (!is_dir($folderNamePath)) {
                FileUtil::mkDirs($folderNamePath);
            }

            // 生成不同比例的小图
            $rate = 1;
            switch ($folderName) {
                case 10:
                    $rate = 9;
                    break;
                case 11:
                    $rate = 8;
                    break;
                case 12:
                    $rate = 7;
                    break;
                case 13:
                    $rate = 6;
                    break;
                case 14:
                    $rate = 5;
                    break;
                case 15:
                    $rate = 4;
                    break;
                case 16:
                    $rate = 3;
                    break;
                case 17:
                    $rate = 2;
                    break;
                case 18:
                    $rate = 1;
                    break;
            }

            $curSourceWidth  = max(ceil($mainSourceWidth / $rate), $tileSize);
            $curSourceHeight = max(ceil($mainSourceHeight / $rate), $tileSize);

            $saveImageFolder = $fileRootPath . $filenamePre . "/" . $folderName . "/";
            $saveImagePath   = $saveImageFolder . $filename;
            $image->thumb($curSourceWidth, $curSourceHeight)->save($saveImagePath);

            # 处理大图超时问题需要解决下 TODO 

            # 3.裁剪图片（TODO 如果小文件里边都是一张图，也就是小于等于512那么就不需要继续生成小图，目前全部图片都进行创建小图操作）
            $isTrue = $this->imageSection($saveImageFolder, $saveImagePath);
            if ($isTrue) {
                unlink($saveImagePath);
            }
        }
        unset($item);

        $result = [
            'fileInfo'     => $fileInfo,
            'filepath'     => $fileRootPath,
            'filename'     => $filename,
            'fileurl'      => $folder . $filenamePre,
            'sourceSize'   => $imageSize,
            'sourceWidth'  => $mainSourceWidth,
            'sourceHeight' => $mainSourceHeight,
        ];
        return $this->success($result);
    }

    // 图片切片处理
    private function imageSection($saveImageFolder, $imgPath)
    {
        try {
            $image = new \Imagick($imgPath);

            // 切割块的大小
            $blockSize   = 512;
            $imageWidth  = $image->getImageWidth();
            $imageHeight = $image->getImageHeight();


            // 计算行数和列数
            $columns = ceil($imageWidth / $blockSize);
            $rows    = ceil($imageHeight / $blockSize);
            // $total   = $columns * $rows;

            // 循环切割图像
            for ($y = 0; $y < $rows; $y++) {
                for ($x = 0; $x < $columns; $x++) {
                    $cropX = $x * $blockSize;
                    $cropY = $y * $blockSize;

                    // 使用cropImage方法切割图像
                    $block = clone $image;
                    $block->cropImage($blockSize, $blockSize, $cropX, $cropY);

                    // 生成新的文件名
                    $filename = "{$x}_{$y}.jpg";

                    $file = $saveImageFolder . $filename;

                    // 保存切割块
                    $block->writeImage($file);
                }
            }

            // dump($total . " : success");
            // die;
            return true;
        } catch (\Exception $e) {
            // echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // 获取目录
    private function getFolder($type)
    {
        $folder = "{$type}s/";

        $getModule = $this->module;

        if (!empty($this->isSystem) && (empty($getModule) || $getModule == 'admin')) {
            $folder .= "global/";
        } else {
            $folder .= "{$this->uniacid}/{$getModule}/";
        }
        $folder .= date('Y/m/');
        return $folder;
    }

}
