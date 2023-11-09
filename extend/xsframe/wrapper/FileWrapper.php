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

namespace xsframe\wrapper;

use xsframe\enum\SysSettingsKeyEnum;
use xsframe\util\ErrorUtil;
use xsframe\util\FileUtil;
use think\facade\Db;
use think\Image;

class FileWrapper
{
    // 上传文件 TODO 目前是只上传到了本地 需要兼容 第三方 例如 OSS 七牛云等
    public function fileUpload($uniacid, $module, $userId, $type = 'image', $folder = '', $originName = '', $filename = '', $ext = '', $compress = false)
    {
        $groupId        = 0;
        $attachmentPath = IA_ROOT . "/public/attachment/";
        $harmType       = array('asp', 'php', 'jsp', 'js', 'css', 'php3', 'php4', 'php5', 'ashx', 'aspx', 'exe', 'cgi');

        if (empty($folder)) {
            return ErrorUtil::error(0, "没有上传内容");
        }

        if (!in_array($type, array('image', 'thumb', 'voice', 'video', 'audio'))) {
            return ErrorUtil::error(0, "未知的上传类型");
        }

        $ext = strtolower($ext);
        if (in_array(strtolower($ext), $harmType)) {
            return ErrorUtil::error(0, "不允许上传此类文件");
        }

        $result = [
            'name'       => $originName,
            'ext'        => $ext,
            'filename'   => $filename,
            'fileurl'    => $folder . $filename,
            'attachment' => $folder . $filename,
            'url'        => tomedia($folder . $filename),
            'type'       => $type == 'image' ? 1 : ($type == 'video' ? 2 : ($type == 'audio' ? 3 : 0)),
            'filesize'   => filesize($attachmentPath . $folder . $filename),
            'group_id'   => $groupId
        ];
        $this->addFileLog($uniacid, $userId, $result['name'], $result['fileurl'], $result['type'], $module, $groupId);

        # 图片压缩设置
        if ($type == 'image') {
            $filename = $this->fileRemoteUpload($uniacid, $attachmentPath . $folder, $filename, $ext);
        }

        return $result;
    }

    // 删除文件
    public function fileDelete($uniacid, $module, $userId, $fileUrl)
    {
        $attachmentPath = IA_ROOT . "/public/attachment/";
        $filePath       = $attachmentPath . $fileUrl;

        if (is_file($filePath)) {
            unlink($filePath);
        }

        # TODO 删除远程附件
        $this->fileRemoteDelete($fileUrl);

        $isDelete = Db::name('sys_attachment')->where(['uniacid' => $uniacid, 'fileUrl' => $fileUrl])->delete();
        if (!$isDelete) {
            return ErrorUtil::error(0, "删除失败");
        }
        return $isDelete;
    }

    // 添加文件
    private function addFileLog($uniacid, $userId, $filename, $fileurl, $type, $module, $groupId = 0)
    {
        $insertData = [
            'uniacid'    => $uniacid,
            'uid'        => $userId,
            'filename'   => $filename,
            'fileurl'    => $fileurl,
            'type'       => $type,
            'module'     => $module,
            'group_id'   => $groupId,
            'createtime' => time(),
        ];
        return Db::name('sys_attachment')->insert($insertData);
    }

    // 上传附件 TODO
    private function fileRemoteUpload($uniacid, $filePath = null, $fileName = null, $ext = '')
    {
        if (empty($filePath)) {
            return false;
        }

        $newFileName    = $fileName;
        $attachmentPath = IA_ROOT . "/public/attachment/";

        $settingsController = new SettingsWrapper();
        if ($uniacid > 0) {
            $setting = $settingsController->getAccountSettings($uniacid, 'settings');
        } else {
            $setting = $settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);
        }

        if (!empty($setting)) {

            // 图片处理
            if (!empty($setting['image'])) {
                // 启用压缩
                if ($setting['image']['is_reduce'] == 1 && $setting['image']['width'] > 0) {
                    $newFileName = FileUtil::fileRandomName($filePath, $ext);
                    $maxWidth    = $setting['image']['width'];
                    $image       = Image::open($filePath . $fileName);
                    $image->thumb($maxWidth, $maxWidth)->save($filePath . $newFileName);
                    // 删除源图
                    unlink($filePath . $fileName);
                }
            }

            // 上传附件
            $filename = str_replace($attachmentPath, '', $filePath . $newFileName);
            if (!empty($setting['remote']) && $setting['remote']['type'] > 0) {
                $attachmentController = new AttachmentWrapper();
                $attachmentController->fileRemoteUpload($setting, $attachmentPath, $filename);
            }
        }

        return $newFileName;
    }

    // 删除附件
    private function fileRemoteDelete($filePath = null)
    {
        $settingsController = new SettingsWrapper();
        $attachmentSets     = $settingsController->getSysSettings(SysSettingsKeyEnum::ATTACHMENT_KEY);

        if (empty($attachmentSets) || empty($filePath)) {
            return false;
        }

        # 删除远程附件 TODO

        return true;
    }
}