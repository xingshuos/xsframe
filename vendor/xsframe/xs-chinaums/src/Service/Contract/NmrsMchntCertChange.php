<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Service\Contract\Base;

/**
 * 商户证照变更接口
 */
class NmrsMchntCertChange extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/self-contract-nmrs/interface/autoReg';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'service' => 'nmrs_mchnt_cert_change',
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
        'merchantCardPics',
        'merchantCardExpireDate',
        'merchantRegistryName',
        'merchantProvince',
        'merchantCity',
        'merchantCounty',
        'merchantDetailAddress',
    ];
}
