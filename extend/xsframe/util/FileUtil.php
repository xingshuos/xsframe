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


use think\Exception;

class FileUtil
{
    // 创建目录
    public static function mkDirs($path)
    {
        if (!is_dir($path)) {
            self::mkdirs(dirname($path));
            mkDir($path);
        }
        return is_dir($path);
    }

    // 删除目录
    public static function rmDirs($path, $clean = false)
    {
        if (!is_dir($path)) {
            return false;
        }
        $files = glob($path . '/*');
        if ($files) {
            foreach ($files as $file) {
                is_dir($file) ? self::rmDirs($file) : @unlink($file);
            }
        }

        return $clean ? true : @rmdir($path);
    }

    // 获取文件夹下一级文件夹列表
    public static function dirsOnes($path)
    {
        $files    = glob($path . '/*');
        $newFiles = [];
        if (!empty($files)) {
            foreach ($files as $item) {
                $item       = str_replace("\\", "/", $item);
                $newFiles[] = substr($item, strripos($item, '/') + 1);
            }
        }
        return $newFiles;
    }

    // 指定文件夹移动到新文件夹
    public static function oldDirToNewDir($path, $newPath, $oldPath = '')
    {
        if (empty($oldPath)) {
            $oldPath = $path;
        }

        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                $tmpFile = $path . "/" . $file;
                $fileName    = basename($tmpFile);

                $isSvnPath = strpos($tmpFile, ".svn") !== false;
                $isGitPath = strpos($tmpFile, ".git") !== false;

                if ($isSvnPath || $isGitPath || in_array($fileName,['install.php','uninstall.php','upgrade.php','manifest.xml']) ) {
                    continue;
                }

                if ($file != '.' && $file != '..') {
                    $tmpPath  = $path . '/' . $file;
                    $pathName = str_replace($oldPath, '', $tmpPath);

                    if (is_dir($tmpPath)) {
                        if (!is_dir($newPath . $pathName)) {
                            mkdir($newPath . $pathName);
                        }
                        self::oldDirToNewDir($tmpPath, $newPath, $oldPath);
                    } elseif (is_file($tmpPath)) {
                        $pathName    = substr($pathName, 0, strrpos($pathName, "/"));
                        $newFilePath = str_replace("//", '/', $newPath . $pathName . "/" . $fileName);

                        if (!is_file($newFilePath) || md5_file($tmpFile) != md5_file($newFilePath)) {
                            @copy($tmpFile, $newFilePath);
                        }
                    }
                }
            }
            $dp->close();
        }
        return true;
    }

    // 查找文件夹下所有文件
    public static function searchDir($path, &$data)
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    self::searchDir($path . '/' . $file, $data);
                }
            }
            $dp->close();
        }

        $isSvnPath = strpos($path, ".svn") !== false;
        $isGitPath = strpos($path, ".git") !== false;

        if (is_file($path) && !$isSvnPath && !$isGitPath) {
            $path   = ltrim($path, ".");
            $data[] = array(
                'path'     => $path,
                'checksum' => md5_file($path)
            );
        }
    }

    // 获取目录下所有文件
    public static function getDir($dir)
    {
        $data = array();
        self::searchDir($dir, $data);
        return $data;
    }

    // 补全文件绝对路径
    public static function getMd5files($files = null)
    {
        $rootPath = str_replace("\\", "/", dirname(dirname(dirname(dirname(__file__)))));
        $data     = [];
        foreach ($files as $item) {
            $data = self::getDir($rootPath . $item);
        }
        foreach ($data as $key => $item) {
            $data[$key]['path'] = str_replace($rootPath, "", $item['path']);
        }
        return $data;
    }

    // 远程上传文件
    public static function UploadSetFile($url, $filename, $type = 0)
    {
        if ($url == '') {
            return false;
        }
        //获取远程文件数据
        if ($type === 0) {
            $ch      = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);//最长执行时间
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);//最长等待时间

            $img = curl_exec($ch);
            curl_close($ch);
        }
        if ($type === 1) {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }
        if ($type === 2) {
            $img = file_get_contents($url);
        }

        $fp2 = @fopen($filename, 'a');

        fwrite($fp2, $img);
        fclose($fp2);
    }

    // 循环查询目录结构
    public static function fileTree($path, $include = array())
    {
        $files = array();
        if (!empty($include)) {
            $ds = glob($path . '/{' . implode(',', $include) . '}', GLOB_BRACE);
        } else {
            $ds = glob($path . '/*');
        }
        if (is_array($ds)) {
            foreach ($ds as $entry) {
                if (is_file($entry)) {
                    $files[] = $entry;
                }
                if (is_dir($entry)) {
                    $rs = self::fileTree($entry);
                    foreach ($rs as $f) {
                        $files[] = $f;
                    }
                }
            }
        }

        return $files;
    }

    // 生成随机文件名称
    public static function fileRandomName($dir, $ext = null, $length = 16)
    {
        do {
            if (!is_dir($dir)) {
                self::mkDirs($dir);
            }

            $filename = RandomUtil::random($length);
            if (!empty($ext)) {
                $filename .= '.' . $ext;
            }
        } while (file_exists($dir . $filename));

        return $filename;
    }

    // 是否存在某个文件夹
    public static function fileDirExistImage($path)
    {
        $attachmentPath = IA_ROOT . "/public/attachment/";
        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                while (($file = readdir($dir)) !== false) {
                    if (in_array($file, array('.', '..'))) {
                        continue;
                    }
                    if (is_file($path . '/' . $file) && self::fileIsImage($path . '/' . $file)) {
                        if (strpos($path, $attachmentPath) === 0) {
                            $attachment = str_replace($attachmentPath . 'images/', '', $path . '/' . $file);
                            list($file_account) = explode('/', $attachment);
                            if ($file_account == 'global') {
                                continue;
                            }
                        }
                        closedir($dir);
                        return true;
                    }
                    if (is_dir($path . '/' . $file) && self::fileDirExistImage($path . '/' . $file)) {
                        closedir($dir);
                        return true;
                    }
                }
                closedir($dir);
            }
        }
        return false;
    }

    // 文件是否是图片
    public static function fileIsImage($url)
    {
        if (!parsePath($url)) {
            return false;
        }
        $pathInfo  = pathinfo($url);
        $extension = strtolower($pathInfo['extension']);
        return !empty($extension) && in_array($extension, array('jpg', 'jpeg', 'gif', 'png'));
    }

    // 文件限制
    public static function fileTreeLimit($path, $limit = 0, $acquired_files_count = 0)
    {
        $files = array();
        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                while (($file = readdir($dir)) !== false) {
                    if (in_array($file, array('.', '..'))) {
                        continue;
                    }
                    if (is_file($path . '/' . $file)) {
                        $files[] = $path . '/' . $file;
                        $acquired_files_count++;
                        if ($limit > 0 && $acquired_files_count >= $limit) {
                            closedir($dir);
                            return $files;
                        }
                    }
                    if (is_dir($path . '/' . $file)) {
                        $rs = self::fileTreeLimit($path . '/' . $file, $limit, $acquired_files_count);
                        foreach ($rs as $f) {
                            $files[] = $f;
                            $acquired_files_count++;
                            if ($limit > 0 && $acquired_files_count >= $limit) {
                                closedir($dir);
                                return $files;
                            }
                        }
                    }
                }
                closedir($dir);
            }
        }
        return $files;
    }

    // 删除本地文件
    public static function fileDelete($file)
    {
        $attachmentPath = IA_ROOT . "/public/attachment/";
        if (empty($file)) {
            return false;
        }

        if (file_exists($file)) {
            @unlink($file);
        }

        if (file_exists($attachmentPath . $file)) {
            @unlink($attachmentPath . $file);
        }

        return true;
    }
}