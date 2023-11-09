<?php

namespace app\store\controller\pc;

use AlibabaCloud\SDK\Vod\V20170321\Models\DecryptKMSDataKeyRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\GenerateKMSDataKeyRequest;
use AlibabaCloud\Tea\Utils\Utils;
use app\store\facade\service\ChaptersItemServiceFacade;
use xsframe\exception\ApiException;
use app\store\facade\service\CourseHistoryServiceFacade;
use app\store\facade\service\GoodsServiceFacade;
use app\store\facade\service\UserCourseServiceFacade;

use AlibabaCloud\SDK\Vod\V20170321\Vod;
use AlibabaCloud\Tea\Exception\TeaError;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Vod\V20170321\Models\GetVideoPlayAuthRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;

class UserCourse extends Base
{

    public function index()
    {
        $courseList = UserCourseServiceFacade::getAll(['mid' => $this->userId, 'chapters_id' => 0, 'is_deleted' => 0], "*", "createtime desc");
        $courseList = GoodsServiceFacade::listMergeGoodsInfo($courseList, 'goodsid');

        $result = [
            'courseList' => $courseList
        ];
        return $this->success($result);
    }

    public function history()
    {
        $courseList = CourseHistoryServiceFacade::getAll(['mid' => $this->userId, 'chapters_id' => 0, 'is_deleted' => 0], "*", "update_time desc");
        $courseList = GoodsServiceFacade::listMergeGoodsInfo($courseList, 'goodsid');

        $result = [
            'courseList' => $courseList
        ];
        return $this->success($result);
    }

    public function play()
    {
        $id            = $this->params['id'] ?? 0;
        $chapterItemId = $this->params['itemId'] ?? 0;

        $goodsInfo = GoodsServiceFacade::getInfo(['id' => $id], "id,title");

        $result = [
            'goodsInfo'     => $goodsInfo,
            'goodsId'       => $id,
            'chapterItemId' => $chapterItemId,
        ];
        return $this->template('pc/goods/play', $result);
    }

    /**
     * 获取播放信息
     * @return array
     * @throws ApiException
     */
    public function getPlayInfo()
    {
        $goodsId       = $this->params['goodsId'] ?? 0;
        $chapterId     = $this->params['chapterId'] ?? 0;
        $chapterItemId = $this->params['chapterItemId'] ?? 0;

        $isPlay = UserCourseServiceFacade::checkIsPlay($this->userId, $goodsId, $chapterId, $chapterItemId);
        if (empty($isPlay)) {
            throw new ApiException("购买后可以解锁全部内容~");
        }

        $chaptersItemInfo = ChaptersItemServiceFacade::getInfo(['id' => $chapterItemId, "is_deleted" => 0]);

        if (empty($chaptersItemInfo) || $chaptersItemInfo['status'] == 0) {
            throw new ApiException("该课程暂未开启，请耐心等待~");
        }

        $videoId = $chaptersItemInfo['video_url'];

        $client = self::createClient();

        $getVideoPlayAuthRequest = new GetVideoPlayAuthRequest([
            "videoId"         => $videoId,
            "authInfoTimeout" => 3600,
            "apiVersion"      => "1.0.0"
        ]);

        $runtime = new RuntimeOptions([]);

        try {
            // 复制代码运行请自行打印 API 的返回值 无加密
            $response = $client->getVideoPlayAuthWithOptions($getVideoPlayAuthRequest, $runtime);

            $playAuth = $response->body->playAuth;

            $result = [
                'vid'      => $videoId,
                'playAuth' => $playAuth,
                'cover'    => tomedia($chaptersItemInfo['thumb']),
            ];
            return $this->success($result);
        } catch (TeaError $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 如有需要，请打印 error
            // Utils::assertAsString($error->message);
            throw new ApiException("播放授权失败,请联系管理员~");
        }
    }

    // 解密KMS数据密钥密文
    private function DecryptKMSDataKey()
    {
        $cipherText = "123456";

        // 工程代码泄露可能会导致AccessKey泄露，并威胁账号下所有资源的安全性。以下代码示例仅供参考，建议使用更安全的 STS 方式，更多鉴权访问方式请参见：https://help.aliyun.com/document_detail/311677.html
        $client                   = self::createClient();
        $decryptKMSDataKeyRequest = new DecryptKMSDataKeyRequest(["cipherText" => $cipherText]);
        $runtime                  = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $client->decryptKMSDataKeyWithOptions($decryptKMSDataKeyRequest, $runtime);
        } catch (TeaError $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }

            dump($error->message);
            die;

            // 如有需要，请打印 error
            // Utils::assertAsString($error->message);
        }
    }

    // 创建KMS数据密钥
    private function GenerateKMSDataKey()
    {
        // 工程代码泄露可能会导致AccessKey泄露，并威胁账号下所有资源的安全性。以下代码示例仅供参考，建议使用更安全的 STS 方式，更多鉴权访问方式请参见：https://help.aliyun.com/document_detail/311677.html
        $client                    = self::createClient();
        $generateKMSDataKeyRequest = new GenerateKMSDataKeyRequest([]);
        $runtime                   = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $client->generateKMSDataKeyWithOptions($generateKMSDataKeyRequest, $runtime);
        } catch (TeaError $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }

            dump($error->message);
            die;

            // 如有需要，请打印 error
            // Utils::assertAsString($error->message);
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