<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = include_once './Config/Config.php';

$data = [
    'request_date' => date('YmdHis'),//请求时间
    'mer_no' => '20181218161925001674',
    'request_seq' => uniqid(), 
    'alter_bank_acct_no' => '20191111111111111',
    'alter_bank_no' => '20191111111111111',
    'pic_list' => [
        'document_type' => '0025',
        'document_name' => '0025',
        'file_path' => '0025',
        'file_size' => '0025',
    ]
];
Factory::config($config);
$reponse = Factory::Contract()->ComplexAlterAcctinfo()->request($data);
echo 'response:' . $reponse . PHP_EOL;
