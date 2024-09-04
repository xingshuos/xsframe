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

use Qcloud\Cos\Client;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Exception;
use xsframe\exception\ApiException;
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
    public function qiNiu($key, $secret, $bucket): bool
    {
        // 初始化Auth状态
        $auth = new Auth($key, $secret);

        // 构建UploadToken
        $token = $auth->uploadToken($bucket);

        // 初始化UploadManager对象并进行文件上传
        $uploadMgr = new UploadManager();

        // 调用UploadManager的putFileUp方法进行文件上传
        $attachmentPath = IA_ROOT . "/public/attachment/";
        $filename = 'HeEngine.ico';
        $filePath = $attachmentPath . 'images/global/' . $filename;

        [$ret, $err] = $uploadMgr->putFile($token, $filename, $filePath);

        if ($err !== null) {
            $err = (array)$err;
            $err = (array)array_pop($err);
            $err = json_decode($err['body'], true);
            throw new ApiException($err['error']);
        }

        return true;
    }

    // cos
    public function cos($appid, $secretId, $secretKey, $bucket, $region): bool
    {
        $cosClient = new Client([
            'region'      => $region,
            'credentials' => [
                'appId'     => $appid,
                'secretId'  => $secretId,
                'secretKey' => $secretKey,
            ],
        ]);

        // 调用UploadManager的putFileUp方法进行文件上传
        $attachmentPath = IA_ROOT . "/public/attachment/";
        $filename = 'HeEngine.ico';
        $filePath = $attachmentPath . 'images/global/' . $filename;

        try {
            $result = $cosClient->putObject([
                'Bucket' => $bucket,
                'Key'    => $filename,
                'Body'   => fopen($filePath, 'rb')
            ]);
            // dd($result);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage());
        }

        return true;
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

        if (!empty($local_attachment)) {
            foreach ($local_attachment as $attachment) {
                $filename = str_replace($attachmentPath, '', $attachment);
                [$image_dir, $file_account] = explode('/', $filename);

                # TODO 应该验证下 $file_account 也就是 uniacid 是否配置过独立的存储空间 这里暂时是没有验证独立库的，一旦提交上传将都提交到全局库
                if ($file_account == 'global' || !FileUtil::fileIsImage($attachment)) {
                    continue;
                }

                $this->fileRemoteUpload($setting, $attachmentPath, $filename);
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

        // 阿里云
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
        }

        // 七牛云
        if ($setting['remote']['type'] == '3') {
            try {
                // 初始化Auth状态
                $auth = new Auth($setting['remote']['qiniu']['accesskey'], $setting['remote']['qiniu']['secretkey']);

                // 构建UploadToken
                $token = $auth->uploadToken($setting['remote']['qiniu']['bucket']);

                // 初始化UploadManager对象并进行文件上传
                $uploadMgr = new UploadManager();

                [$ret, $err] = $uploadMgr->putFile($token, $filename, $attachmentPath . $filename);

                if ($err !== null) {
                    $err = (array)$err;
                    $err = (array)array_pop($err);
                    $err = json_decode($err['body'], true);
                    throw new ApiException($err['error']);
                }
            } catch (Exception $e) {
                throw new ApiException($e->getMessage());
            }
        }

        // 腾讯云
        if ($setting['remote']['type'] == '4') {
            try {
                $region = $setting['remote']['cos']['local'];
                $appid = $setting['remote']['cos']['appid'];
                $secretId = $setting['remote']['cos']['secretid'];
                $secretKey = $setting['remote']['cos']['secretkey'];
                $bucket = $setting['remote']['cos']['bucket'];

                $cosClient = new Client([
                    'region'      => $region,
                    'credentials' => [
                        'appId'     => $appid,
                        'secretId'  => $secretId,
                        'secretKey' => $secretKey,
                    ],
                ]);

                $cosClient->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $filename,
                    'Body'   => fopen($attachmentPath . $filename, 'rb')
                ]);
            } catch (Exception $e) {
                throw new ApiException($e->getMessage());
            }
        }

        if ($auto_delete_local) {
            FileUtil::fileDelete($filename);
        }

        return true;
    }
}