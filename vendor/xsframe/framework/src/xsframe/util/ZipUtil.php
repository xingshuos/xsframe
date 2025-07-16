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

class ZipUtil
{
    /**
     * 优化ZIP文件结构：移除首层目录，将内容直接置于根目录
     */
    public static function optimizeStructure($folderName, $zipPath, $clearZip = true)
    {
        // 验证文件存在性
        if (!file_exists($zipPath)) return false;

        // 创建临时内存 ZIP
        $newZip = new \ZipArchive();
        $newZipPath = dirname($zipPath) . '/optimized_' . basename($zipPath);

        if ($newZip->open($newZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        // 打开原始 ZIP
        $srcZip = new \ZipArchive();
        if ($srcZip->open($zipPath) !== true) return false;

        $prefix = "{$folderName}/";  // 已知的目录前缀
        for ($i = 0; $i < $srcZip->numFiles; $i++) {
            $entry = $srcZip->getNameIndex($i);

            // 跳过顶级目录本身
            if ($entry === $prefix) {
                continue;
            }

            // 跳过 macOS 系统目录
            if (strpos($entry, '__MACOSX/') === 0 || strpos($entry, '.DS_Store') !== false) {
                continue;
            }

            // 移除指定目录前缀
            if (strpos($entry, $prefix) === 0) {
                $newName = substr($entry, strlen($prefix));

                // 创建空目录条目
                if (substr($newName, -1) === '/') {
                    $newZip->addEmptyDir($newName);
                } // 添加文件内容
                else {
                    $stream = $srcZip->getStream($entry);
                    if ($stream) {
                        $content = stream_get_contents($stream);
                        fclose($stream);

                        if ($content !== false) {
                            $newZip->addFromString($newName, $content);
                        }
                    }
                }
            }
        }

        $srcZip->close();
        $newZip->close();

        if ($clearZip) {
            @unlink($zipPath);
            @rename($newZipPath, $zipPath);
        };

        return $newZipPath;
    }
}