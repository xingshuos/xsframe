<?php

namespace xsframe\chinaums\Service\Contract;

use xsframe\chinaums\Service\Contract\Base;

/**
 * 商户账户信息变更接口
 */
class ComplexAlterAcctinfo extends Base
{
    /**
     * @var string 接口地址
     */
    protected $api = '/self-contract-nmrs/interface/autoReg';
    /**
     * @var array $body 请求参数
     */
    protected $body = [
        'service'=>'complex_alter_acctinfo',
        'sign_type'=>'SHA-256',
    ];
    /**
     * 必传的值
     * @var array
     */
    protected $require = ['service', 'accesser_id', 'sign_type', 'request_date','request_seq','mer_no','alter_bank_acct_no','alter_bank_no','pic_list'];
}
