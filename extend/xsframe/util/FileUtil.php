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

class FileUtil
{
    // 创建目录
    public static function mkDirs($path): bool
    {
        if (!is_dir($path)) {
            self::mkdirs(dirname($path));
            mkDir($path, 0777, true);
        }
        return is_dir($path);
    }

    // 删除目录
    public static function rmDirs($path, $unFiles = [], $clean = false): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        $files = glob($path . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (!in_array($file, $unFiles)) {
                    is_dir($file) ? self::deleteDirectoryRecursively($file) : @unlink($file);
                }
            }
        }

        return $clean || @rmdir($path);
    }

    // 清空隐藏文件
    public static function deleteDirectoryRecursively($dir): bool
    {
        if (!is_dir($dir)) {
            // 如果不是目录，直接返回 false
            return false;
        }

        // 开启目录句柄
        $files = @scandir($dir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;

                if (is_dir($filePath)) {
                    // 如果是目录，则递归删除子目录
                    self::deleteDirectoryRecursively($filePath);
                } else {
                    // 如果是文件，则直接删除文件
                    unlink($filePath);
                }
            }
        }

        // 当目录为空时，删除父目录
        return @rmdir($dir);
    }

    // 加载所有应用的命令行对应路径
    public static function getAppCommands(): array
    {
        $rootPath = str_replace("\\", "/", app()->getRootPath());

        $appPath = $rootPath . "app";
        $dirList = FileUtil::dirsOnes($appPath);

        $commands = [];
        foreach ($dirList as $key => $dirItem) {
            $appCommands = FileUtil::getDir($appPath . "/$dirItem/command");
            if (!empty($appCommands)) {
                foreach ($appCommands as $commandItem) {
                    $commandPath = str_replace($rootPath, "", rtrim($commandItem['path'], ".php"));
                    $commands[]  = str_replace("/", "\\", $commandPath);
                }
            }
        }

        return $commands;
    }

    // 获取文件夹下一级文件夹列表
    public static function dirsOnes($path): array
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
    public static function oldDirToNewDir($path, $newPath, $oldPath = '', $unSyncFiles = [], $unSyncFolder = ['source']): bool
    {
        if (empty($oldPath)) {
            $oldPath = $path;
        }

        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                $tmpFile  = str_replace("//", "/", $path . "/" . $file);
                $fileName = basename($tmpFile);

                $isSvnPath = strpos($tmpFile, ".svn") !== false;
                $isGitPath = strpos($tmpFile, ".git") !== false;

                if ($isSvnPath || $isGitPath || in_array($tmpFile, $unSyncFiles) || in_array($fileName, ['install.php', 'uninstall.php', 'upgrade.php', 'manifest.xml'])) {
                    continue;
                }

                if ($file != '.' && $file != '..') {
                    $tmpPath  = str_replace("//", "/", $path . "/" . $file);
                    $pathName = str_replace($oldPath, '', $tmpPath);

                    if (!empty($unSyncFolder)) {
                        $isContinue = true;
                        foreach ($unSyncFolder as $folderName) {
                            $findPath = $oldPath . $folderName;
                            if (StringUtil::strexists($tmpPath, $findPath)) {
                                $isContinue = false;
                                break;
                            }
                        }
                        if (!$isContinue) {
                            continue;
                        }
                    }

                    if (is_dir($tmpPath)) {
                        if (!is_dir($newPath . $pathName)) {
                            @mkdir($newPath . $pathName, 0777, true);
                        }
                        self::oldDirToNewDir($tmpPath, $newPath, $oldPath);
                    } else if (is_file($tmpPath)) {
                        $pathName    = substr($pathName, 0, strrpos($pathName, "/"));
                        $newFilePath = str_replace("//", '/', $newPath . $pathName . "/" . $fileName);

                        if (!is_file($newFilePath) || md5_file($tmpFile) != md5_file($newFilePath)) {
                            $newFilePathInfo = pathinfo($newFilePath);
                            if (!is_dir($newFilePathInfo['dirname'])) {
                                @mkdir($newFilePathInfo['dirname'], 0777, true);
                            }

                            @copy($tmpFile, $newFilePath);
                            @chmod($newFilePath, 0777);
                        }
                    }
                }
            }
            $dp->close();
        } else {
            if (!is_file($newPath) || md5_file($path) != md5_file($newPath)) {
                @copy($path, $newPath);
                @chmod($newPath, 0777);
            }
        }
        return true;
    }

    // 查找文件夹下所有文件
    public static function searchDir($path, &$data, $syncTypes = null)
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if ($file != '.' && $file != '..') {
                    if (!empty($syncTypes) && !empty($extension) && !in_array($extension, $syncTypes)) {
                        continue;
                    }
                    self::searchDir($path . '/' . $file, $data, $syncTypes);
                }
            }
            $dp->close();
        }

        $isSvnPath = strpos($path, ".svn") !== false;
        $isGitPath = strpos($path, ".git") !== false;

        if (is_file($path) && !$isSvnPath && !$isGitPath) {
            $path   = ltrim($path, ".");
            $data[] = [
                'path'     => $path,
                'checksum' => md5_file($path)
            ];
        }
    }

    // 获取目录下所有文件
    public static function getDir($dir, $syncTypes = null): array
    {
        $data = [];
        self::searchDir($dir, $data, $syncTypes);
        return $data;
    }

    // 补全文件绝对路径
    public static function getMd5files($files = null): array
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
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);       //最长等待时间

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
    public static function fileTree($path, $include = []): array
    {
        $files = [];
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
    public static function fileRandomName($dir, $ext = null, $length = 16): string
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
    public static function fileDirExistImage($path): bool
    {
        $rootPath       = str_replace("\\", "/", app()->getRootPath());
        $attachmentPath = $rootPath . "public/attachment/";
        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                while (($file = readdir($dir)) !== false) {
                    if (in_array($file, ['.', '..'])) {
                        continue;
                    }
                    if (is_file($path . '/' . $file) && self::fileIsImage($path . '/' . $file)) {
                        if (strpos($path, $attachmentPath) === 0) {
                            $attachment = str_replace($attachmentPath . 'images/', '', $path . '/' . $file);
                            [$file_account] = explode('/', $attachment);
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
    public static function fileIsImage($url): bool
    {
        if (!parsePath($url)) {
            return false;
        }
        $pathInfo  = pathinfo($url);
        $extension = strtolower($pathInfo['extension']);
        return !empty($extension) && in_array($extension, ['jpg', 'jpeg', 'gif', 'png']);
    }

    // 文件限制
    public static function fileTreeLimit($path, $limit = 0, $acquired_files_count = 0): array
    {
        $files = [];
        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                while (($file = readdir($dir)) !== false) {
                    if (in_array($file, ['.', '..'])) {
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
    public static function fileDelete($file): bool
    {
        $rootPath       = str_replace("\\", "/", app()->getRootPath());
        $attachmentPath = $rootPath . "public/attachment/";

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