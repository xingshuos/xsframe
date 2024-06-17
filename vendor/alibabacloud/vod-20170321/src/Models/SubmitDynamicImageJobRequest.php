<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Vod\V20170321\Models;

use AlibabaCloud\Tea\Model;

class SubmitDynamicImageJobRequest extends Model
{
    /**
     * @description The ID of the animated image template.
     *
     * @example 1a443dc52ef10abc4794d700*****
     *
     * @var string
     */
    public $dynamicImageTemplateId;

    /**
     * @description The parameters used for overriding. The value is a JSON-formatted string. For more information, see the "OverrideParams" section of the [Media processing parameters](~~98618~~) topic. The parameters are used to replace the parameters in the animated image template. For more information, see the [Basic data types](~~52839~~) topic.
     *
     * @example {"Watermarks":[{"Content":"UserID: 666**","WatermarkId":"8ca03c884944bd05efccc312367****"}]}
     *
     * @var string
     */
    public $overrideParams;

    /**
     * @description The ID of the video.
     *
     * @example 7d2fbc3e273441bdb0e08e55f8****
     *
     * @var string
     */
    public $videoId;
    protected $_name = [
        'dynamicImageTemplateId' => 'DynamicImageTemplateId',
        'overrideParams'         => 'OverrideParams',
        'videoId'                => 'VideoId',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->dynamicImageTemplateId) {
            $res['DynamicImageTemplateId'] = $this->dynamicImageTemplateId;
        }
        if (null !== $this->overrideParams) {
            $res['OverrideParams'] = $this->overrideParams;
        }
        if (null !== $this->videoId) {
            $res['VideoId'] = $this->videoId;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return SubmitDynamicImageJobRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['DynamicImageTemplateId'])) {
            $model->dynamicImageTemplateId = $map['DynamicImageTemplateId'];
        }
        if (isset($map['OverrideParams'])) {
            $model->overrideParams = $map['OverrideParams'];
        }
        if (isset($map['VideoId'])) {
            $model->videoId = $map['VideoId'];
        }

        return $model;
    }
}
