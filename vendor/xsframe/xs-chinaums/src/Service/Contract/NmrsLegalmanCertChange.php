<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Service\Contract\Base;

/**
 * 法人证照变更接口
 */
class NmrsLegalmanCertChange extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/self-contract-nmrs/interface/autoReg';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'service' => 'nmrs_legalman_cert_change',
        'sign_type' => 'SHA-256',
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = [
        'service',
        'accesser_id',
        'sign_type',
        'request_date',
        'request_seq',
        'merNo',
        'legalmanCardPics',
        'legalmanCardExpireDate'
    ];
}
