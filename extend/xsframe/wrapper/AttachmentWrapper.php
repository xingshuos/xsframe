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

namespace xsframe\wrapper;

use xsframe\enum\SysSettingsKeyEnum;
use xsframe\util\ErrorUtil;
use xsframe\util\FileUtil;
use OSS\Core\OssException;
use OSS\OssClient;

class AttachmentWrapper
{
    // aliOss
    public function aliOss($key, $secret, $customUrl, $bucket, $internal = 0)
    {
        $buckets = $this->attachmentAliossBuctkets($key, $secret);
        if (ErrorUtil::isError($buckets)) {
            show_json(-1, $buckets['msg']);
        }

        [$bucket, $url] = explode('@@', $bucket);
        $result = $this->attachmentNewAliossAuth($key, $secret, $bucket, $internal);
        if (ErrorUtil::isError($result)) {
            show_json(-1, $result['msg']);
        }

        return true;
    }

    // 获取数据中心组
    public function buckets($key, $secret)
    {
        $buckets = $this->attachmentAliossBuctkets($key, $secret);
        if (ErrorUtil::isError($buckets)) {
            show_json(-1, $buckets['msg']);
        }
        $bucketDataCenter = $this->attachmentAliossDataCenters();
        $bucket = [];
        foreach ($buckets as $key => $value) {
            $value['loca_name'] = $key . '@@' . $bucketDataCenter[$value['location']];
            $bucket[] = $value;
        }
        return $bucket;
    }

    // qiNiu
    public function qiNiu($key, $secret, $bucket)
    {
        load()->library('qiniu');
        $auth = new Qiniu\Auth($key, $secret);
        $token = $auth->uploadToken($bucket);
        $config = new Qiniu\Config();
        $uploadmgr = new Qiniu\Storage\UploadManager($config);

        $attachmentPath = IA_ROOT . "/public/attachment/";
        $filename = 'HeEngine.ico';
        $filePath = $attachmentPath . 'images/global/' . $filename;

        list($ret, $err) = $uploadmgr->putFile($token, 'MicroEngine.ico', $filePath);
        if ($err !== null) {
            $err = (array)$err;
            $err = (array)array_pop($err);
            $err = json_decode($err['body'], true);
            return false;
        } else {
            return true;
        }
    }

    // cos
    public function cos()
    {
        $result = [

        ];
        return $result;
    }

    private function attachmentNewAliossAuth($key, $secret, $bucket, $internal = false)
    {
        $attachmentPath = IA_ROOT . "/public/attachment/";

        $buckets = $this->attachmentAliossBuctkets($key, $secret);
        $host = $internal ? '-internal.aliyuncs.com' : '.aliyuncs.com';
        $url = 'http://' . $buckets[$bucket]['location'] . $host;

        try {
            $filename = 'HeEngine.ico';
            $filePath = $attachmentPath . 'images/global/' . $filename;
            if (!is_file($filePath)) {
                @touch($filePath);
            }

            $ossClient = new OssClient($key, $secret, $url);
            $ossClient->uploadFile($bucket, $filename, $filePath);
        } catch (OssException $e) {
            show_json(-1, $e->getMessage());
        }
        return true;
    }

    // 文件上传
    public function fileDirRemoteUpload($setting, $attachmentPath, $dir_path, $limit = 50)
    {
        if (empty($setting['remote']['type'])) {
            show_json(-1, '未开启远程附件');
        }

        $dir_path = safe_gpc_path($dir_path);
        if (!empty($dir_path)) {
            $local_attachment = FileUtil::fileTreeLimit($dir_path, $limit);
        } else {
            $local_attachment = [];
        }

        if (is_array($local_attachment) && !empty($local_attachment)) {
            foreach ($local_attachment as $attachment) {
                $filename = str_replace($attachmentPath, '', $attachment);
                [$image_dir, $file_account] = explode('/', $filename);

                if ($file_account == 'global' || !FileUtil::fileIsImage($attachment)) {
                    continue;
                }

                // if (is_numeric($file_account) && is_dir($attachmentPath . 'images/' . $file_account) && !empty($setting['remote_complete_info'][$file_account]['type'])) {
                //     $setting['remote'] = $setting['remote_complete_info'][$file_account];
                // } else {
                //     $setting['remote'] = $setting['remote_complete_info'];
                // }

                $result = $this->fileRemoteUpload($setting, $attachmentPath, $filename);

                if (ErrorUtil::isError($result)) {
                    show_json(-1, $result['msg']);
                }
            }
        }
        return true;
    }

    // 获取oss数据中心
    private function attachmentAliossDataCenters()
    {
        $bucketDataCenter = [
            'oss-cn-hangzhou' => '杭州数据中心',
            'oss-cn-qingdao'  => '青岛数据中心',
            'oss-cn-beijing'  => '北京数据中心',
            'oss-cn-hongkong' => '香港数据中心',
            'oss-cn-shenzhen' => '深圳数据中心',
            'oss-cn-shanghai' => '上海数据中心',
            'oss-us-west-1'   => '美国硅谷数据中心',
        ];
        return $bucketDataCenter;
    }

    // 获取alioss仓库
    private function attachmentAliossBuctkets($key, $secret)
    {
        $url = 'http://oss-cn-beijing.aliyuncs.com';

        $ossClient = null;
        try {
            $ossClient = new OssClient($key, $secret, $url);
        } catch (OssException $e) {
            show_json(-1, $e->getMessage());
        }

        $bucketListInfo = null;
        try {
            $bucketListInfo = $ossClient->listBuckets();
        } catch (OssException $e) {
            show_json(-1, $e->getMessage());
        }

        $bucketListInfo = $bucketListInfo->getBucketList();
        $bucketList = [];
        foreach ($bucketListInfo as &$bucket) {
            $bucketList[$bucket->getName()] = ['name' => $bucket->getName(), 'location' => $bucket->getLocation()];
        }

        return $bucketList;
    }

    // 服务器上传文件
    public function fileRemoteUpload($setting, $attachmentPath, $filename, $auto_delete_local = true)
    {
        if (empty($setting['remote']['type'])) {
            return false;
        }

        if ($setting['remote']['type'] == '2') {
            [$bucket, $url] = explode('@@', $setting['remote']['alioss']['bucket']);

            $buckets = $this->attachmentAliossBuctkets($setting['remote']['alioss']['key'], $setting['remote']['alioss']['secret']);
            $host_name = $setting['remote']['alioss']['internal'] ? '-internal.aliyuncs.com' : '.aliyuncs.com';
            $endpoint = 'http://' . $buckets[$bucket]['location'] . $host_name;

            try {
                $ossClient = new OssClient($setting['remote']['alioss']['key'], $setting['remote']['alioss']['secret'], $endpoint);
                $ossClient->uploadFile($bucket, $filename, $attachmentPath . $filename);
            } catch (OssException $e) {
                show_json(-1, $e->getMessage());
            }

            if ($auto_delete_local) {
                FileUtil::fileDelete($filename);
            }
        }

        return true;
    }
}