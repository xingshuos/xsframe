<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Vod\V20170321\Models;

use AlibabaCloud\Tea\Model;

class UpdateAITemplateRequest extends Model
{
    /**
     * @description The detailed configurations of the AI template. The value is a JSON string.
     *
     * @example {"AuditItem":["terrorism","porn"],"AuditRange":["text-title","video"],"AuditContent":["screen"],"AuditAutoBlock":"yes"}
     *
     * @var string
     */
    public $templateConfig;

    /**
     * @description The ID of the AI template. You can use one of the following methods to obtain the ID of the AI template:
     *
     *   Call the [AddAITemplate](~~102930~~) operation to add an AI template if no AI template exists. The value of TemplateId from the response is the ID of the AI template.
     *   Call the [ListAITemplate](~~102936~~) operation if the template already exists. The value of TemplateId from the response is the ID of the AI template.
     *
     * @example 1706a0063dd733f6a823ef32e0a5****
     *
     * @var string
     */
    public $templateId;

    /**
     * @description The name of the AI template. The name can be up to 128 bytes in length.
     *
     * @example DemoAITemplate
     *
     * @var string
     */
    public $templateName;
    protected $_name = [
        'templateConfig' => 'TemplateConfig',
        'templateId'     => 'TemplateId',
        'templateName'   => 'TemplateName',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->templateConfig) {
            $res['TemplateConfig'] = $this->templateConfig;
        }
        if (null !== $this->templateId) {
            $res['TemplateId'] = $this->templateId;
        }
        if (null !== $this->templateName) {
            $res['TemplateName'] = $this->templateName;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return UpdateAITemplateRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['TemplateConfig'])) {
            $model->templateConfig = $map['TemplateConfig'];
        }
        if (isset($map['TemplateId'])) {
            $model->templateId = $map['TemplateId'];
        }
        if (isset($map['TemplateName'])) {
            $model->templateName = $map['TemplateName'];
        }

        return $model;
    }
}
