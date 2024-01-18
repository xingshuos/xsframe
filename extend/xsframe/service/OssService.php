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

namespace xsframe\service;

use OSS\Core\OssException;
use OSS\OssClient;

/*
 * 阿里云OSS
 */

class OssService
{
    private $client, $bucket;
    private $accessKeyId;
    private $accessKeySecret;
    private $endpoint;
    private $endpointInternal;

    public function __construct()
    {
//        $accessKeyId      = config('aliyun.rootAccessKeyId');
//        $accessKeySecret  = config('aliyun.rootAccessKeySecret');
//        $endpoint         = config('aliyun.endpoint');
//        $endpointInternal = config('aliyun.endpoint_internal');

        $accessKeyId      = "LJYkoWqNLc6BQleq";
        $accessKeySecret  = "R7H8RN5bHvYNeymhguxncXHVuFCXYo";
        $endpoint         = "oss-cn-beijing.aliyuncs.com";
        $endpointInternal = "oss-cn-beijing-internal.aliyuncs.com";

        $this->accessKeyId      = $accessKeyId;
        $this->accessKeySecret  = $accessKeySecret;
        $this->endpoint         = $endpoint;
        $this->endpointInternal = $endpointInternal;

        try {
            $this->client = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        } catch (OssException $e) {

        }
//        $this->bucket = config('aliyun.bucket');
        $this->bucket = "mingxinpian-attachment";
    }

    public function getVideoUrl($videoPath, $bucket = null, $timeout = 3600)
    {
        $bucket = $bucket ?? $this->bucket;
        // $result = $this->client->signUrl($bucket, $videoPath, $timeout, 'GET');
        $result = $this->client->signUrl($bucket, $videoPath, $timeout, 'GET', ['x-oss-process' => 'hls/sign']);
        return $result;
    }

    //上传字符串
    public function putObject($filename, $content, $path = '')
    {
        $object = $path . $filename;
        try {
            dump($this->client->putObject($this->bucket, $object, $content));
        } catch (OssException $e) {
            dump($e->getMessage());
        }
    }

    //上传文件（内网上传）
    public function uploadFile($filename, $path = '', $bucket = null)
    {
        try {
            if (empty($bucket)) {
                $bucket = $this->bucket;
            }
//            dump([$this->accessKeyId, $this->accessKeySecret, $this->endpointInternal]);die;
            $host_name = $this->endpointInternal ? '-internal.aliyuncs.com' : '.aliyuncs.com';
            $endpoint  = 'http://' . "oss-cn-beijing.aliyuncs.com";

            $this->client = new OssClient($this->accessKeyId, $this->accessKeySecret, $endpoint);
            return $this->client->uploadFile($bucket, $filename, $path);
        } catch (OssException $e) {
//            return $e->getMessage();
        }

    }

    //删除文件
    public function deleteFile($filename)
    {
        try {
            return $this->client->deleteObject($this->bucket, $filename);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }


    //列出对象
    public function listObj()
    {
        try {
            return $this->client->listObjects($this->bucket);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    //获取Cname
    public function getBucketCname()
    {
        try {
            dump($this->client->getBucketCname($this->bucket));
        } catch (OssException $e) {
            dump($e->getMessage());
        }
    }
}