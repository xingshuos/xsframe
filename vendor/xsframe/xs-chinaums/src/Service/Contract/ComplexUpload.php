<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Service\Contract\Base;

/**
 * 详细采集档案资料上传接口
 */
class ComplexUpload extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/self-contract-nmrs/interface/autoReg';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'service'=>'complex_upload',
        'sign_type'=>'SHA-256',
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
        'accesser_user_id',
        'reg_mer_type',
        'legal_name',
        'legal_idcard_no',
        'legal_mobile',
        'legal_card_deadline',
        'shop_name',
        'bank_no',
        'bank_acct_type',
        'bank_acct_no',
        'bank_acct_name',
        'shop_province_id',
        'shop_city_id',
        'shop_country_id',
        'mccCode',
        'product',
        'pic_list'
    ];
}
