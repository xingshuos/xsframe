<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = include_once './Config/Config.php';

$data = [
    'request_date' => date('YmdHis'),//请求时间
    'ums_reg_id'=>'20181218161925001674',
    'request_seq' => uniqid(), 
    'company_account' => '77777778888899', 
    'trans_amt' => '1', 
];
Factory::config($config);
$reponse = Factory::Contract()->CompanyAccountVerify($data);
echo 'response:' . $reponse . PHP_EOL;
