<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Vod\V20170321\Models;

use AlibabaCloud\Tea\Model;

class SubmitAIImageJobRequest extends Model
{
    /**
     * @description The ID of the pipeline that is used for the AI processing job.
     *
     * <props="intl">> This parameter is optional if you have specified a default pipeline ID. If you need to submit image AI processing jobs in a batch to a specific pipeline, [submit a ticket](https://workorder-intl.console.aliyun.com/?spm=5176.12672711.top-nav.ditem-sub.3cd51fa3WvRsjz#/ticket/add/?productId=1270) to contact Alibaba Cloud technical support.</props>
     * @example 6492025b8f*****6ba5bb755a33438
     *
     * @var string
     */
    public $AIPipelineId;

    /**
     * @description The ID of the AI template. You can use one of the following methods to obtain the ID:
     *
     *   Obtain the value of TemplateId from the response to the [AddAITemplate](~~102930~~) that you call to create the template.
     *   Obtain the value of TemplateId from the response to the [ListAITemplate](~~102936~~) operation after you create the template.
     *
     * @example ef1a8842cb9f*****cea80cad902e416
     *
     * @var string
     */
    public $AITemplateId;

    /**
     * @var string
     */
    public $ownerAccount;

    /**
     * @var string
     */
    public $ownerId;

    /**
     * @var string
     */
    public $resourceOwnerAccount;

    /**
     * @var string
     */
    public $resourceOwnerId;

    /**
     * @description The user data.
     *
     *   The value must be a JSON string.
     *   You must specify the MessageCallback or Extend parameter.
     *   The value can contain a maximum of 512 bytes.
     *
     * For more information, see the "UserData: specifies the custom configurations for media upload" section of the [Request parameters](~~86952~~) topic.
     * @example {"Extend":{"localId":"****","test":"www"}}
     *
     * @var string
     */
    public $userData;

    /**
     * @description The ID of the video. You can use one of the following methods to obtain the ID:
     *
     *   Log on to the [ApsaraVideo VOD](https://vod.console.aliyun.com) console. In the left-side navigation pane, choose **Media Files** > **Audio/Video**. On the Video and Audio page, view the ID of the video file. This method is applicable to files that are uploaded by using the ApsaraVideo VOD console.
     *   Obtain the value of VideoId from the response to the [CreateUploadVideo](~~55407~~) operation that you call to upload the video.
     *   Obtain the value of VideoId from the response to the [SearchMedia](~~86044~~) operation after you upload the video.
     *
     * @example 357a8748c5774*****89d2726e6436aa
     *
     * @var string
     */
    public $videoId;
    protected $_name = [
        'AIPipelineId'         => 'AIPipelineId',
        'AITemplateId'         => 'AITemplateId',
        'ownerAccount'         => 'OwnerAccount',
        'ownerId'              => 'OwnerId',
        'resourceOwnerAccount' => 'ResourceOwnerAccount',
        'resourceOwnerId'      => 'ResourceOwnerId',
        'userData'             => 'UserData',
        'videoId'              => 'VideoId',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->AIPipelineId) {
            $res['AIPipelineId'] = $this->AIPipelineId;
        }
        if (null !== $this->AITemplateId) {
            $res['AITemplateId'] = $this->AITemplateId;
        }
        if (null !== $this->ownerAccount) {
            $res['OwnerAccount'] = $this->ownerAccount;
        }
        if (null !== $this->ownerId) {
            $res['OwnerId'] = $this->ownerId;
        }
        if (null !== $this->resourceOwnerAccount) {
            $res['ResourceOwnerAccount'] = $this->resourceOwnerAccount;
        }
        if (null !== $this->resourceOwnerId) {
            $res['ResourceOwnerId'] = $this->resourceOwnerId;
        }
        if (null !== $this->userData) {
            $res['UserData'] = $this->userData;
        }
        if (null !== $this->videoId) {
            $res['VideoId'] = $this->videoId;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return SubmitAIImageJobRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['AIPipelineId'])) {
            $model->AIPipelineId = $map['AIPipelineId'];
        }
        if (isset($map['AITemplateId'])) {
            $model->AITemplateId = $map['AITemplateId'];
        }
        if (isset($map['OwnerAccount'])) {
            $model->ownerAccount = $map['OwnerAccount'];
        }
        if (isset($map['OwnerId'])) {
            $model->ownerId = $map['OwnerId'];
        }
        if (isset($map['ResourceOwnerAccount'])) {
            $model->resourceOwnerAccount = $map['ResourceOwnerAccount'];
        }
        if (isset($map['ResourceOwnerId'])) {
            $model->resourceOwnerId = $map['ResourceOwnerId'];
        }
        if (isset($map['UserData'])) {
            $model->userData = $map['UserData'];
        }
        if (isset($map['VideoId'])) {
            $model->videoId = $map['VideoId'];
        }

        return $model;
    }
}
