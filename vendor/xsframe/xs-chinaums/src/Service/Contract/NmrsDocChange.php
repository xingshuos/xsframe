<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Service\Contract\Base;

/**
 * 商户图片信息变更接口
 */
class NmrsDocChange extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/self-contract-nmrs/interface/autoReg';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'service' => 'nmrs_doc_change',
        'sign_type' => 'SHA-256',
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = ['service', 'accesser_id', 'sign_type', 'request_date', 'request_seq','merNo','pic_list'];

}
