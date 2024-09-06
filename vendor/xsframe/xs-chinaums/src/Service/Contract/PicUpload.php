<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Service\Contract\Base;

/**
 * 关闭接口
 */
class PicUpload extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/self-contract-nmrs/interface/autoReg';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'service'=>'pic_upload',
        'sign_type'=>'SHA-256',
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = ['service', 'accesser_id', 'sign_type', 'request_date','request_seq','pic_base64'];
}
