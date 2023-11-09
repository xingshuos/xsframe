<?php

namespace app\store\controller\web\goods;

use AlibabaCloud\SDK\Vod\V20170321\Models\CreateUploadVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\RefreshUploadVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Vod;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use app\store\facade\service\ChaptersItemServiceFacade;
use Darabonba\OpenApi\Models\Config;
use xsframe\base\AdminBaseController;
use xsframe\exception\ApiException;

class Upload extends AdminBaseController
{

    public function index()
    {
        $goodsId       = $this->params["goodsid"];
        $chapterId     = $this->params["chapter_id"];
        $chapterItemId = $this->params["chapter_item_id"] ?? 0;
        $displayorder  = $this->params["displayorder"];

        $isUpdate = $this->params["is_update"] ?? 0; // 是否更新视频

        $chapterItemInfo = ChaptersItemServiceFacade::getInfo(['id' => $chapterItemId], "id,video_url");

        $result = [
            'goodsId'         => $goodsId,
            'chapterId'       => $chapterId,
            'chapterItemId'   => $chapterItemId,
            'chapterItemInfo' => $chapterItemInfo,
            'displayorder'    => $displayorder,
            'isUpdate'        => $isUpdate,
        ];

        return $this->template('web/goods/upload/index', $result);
    }

    /**
     * 创建上传凭证
     * @throws ApiException
     */
    public function createUploadVideo()
    {
        $title    = $this->params['title'] ?? '';
        $fileName = $this->params['fileName'] ?? '';
        $fileSize = $this->params['fileSize'] ?? '';
        $cateId   = $this->params['cateId'] ?? 0;
        $tags     = $this->params['tags'] ?? '';

        $client                   = self::createClient();
        $createUploadVideoRequest = new CreateUploadVideoRequest([
            "title"    => $title,
            "fileName" => $fileName,
            "fileSize" => $fileSize,
            "cateId"   => $cateId,
            "tags"     => $this->account['name'] . "·" . $tags,
        ]);
        $runtime                  = new RuntimeOptions([]);

        try {
            // 复制代码运行请自行打印 API 的返回值
            $response = $client->createUploadVideoWithOptions($createUploadVideoRequest, $runtime);

            $result = [
                'uploadAddress' => $response->body->uploadAddress,
                'videoId'       => $response->body->videoId,
                'requestId'     => $response->body->requestId,
                'uploadAuth'    => $response->body->uploadAuth,
            ];
            return $this->success($result);
        } catch (TeaError $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 如有需要，请打印 error
            // Utils::assertAsString($error->message);
            throw new ApiException("上传失败");
        }
    }

    /**
     * 刷新上传凭证
     * @throws ApiException
     */
    public function refreshUploadVideo()
    {
        $videoId = $this->params['videoId'] ?? 0;

        $client                    = self::createClient();
        $refreshUploadVideoRequest = new RefreshUploadVideoRequest([
            "videoId" => $videoId,
        ]);

        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $response = $client->refreshUploadVideoWithOptions($refreshUploadVideoRequest, $runtime);

            $result = [
                'uploadAddress' => $response->body->uploadAddress,
                'videoId'       => $response->body->videoId,
                'requestId'     => $response->body->requestId,
                'uploadAuth'    => $response->body->uploadAuth,
            ];
            return $this->success($result);
        } catch (TeaError $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 如有需要，请打印 error
            // Utils::assertAsString($error->message);
            throw new ApiException("上传失败");
        }
    }

    // createClient
    private function createClient()
    {
        $accessKeyId     = $this->moduleSetting['alivod']['accessKeyId'];
        $accessKeySecret = $this->moduleSetting['alivod']['accessKeySecret'];

        $config = new Config([
            // 必填，您的 AccessKey ID
            "accessKeyId"     => $accessKeyId,
            // 必填，您的 AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "vod.cn-shanghai.aliyuncs.com";
        return new Vod($config);
    }
}
