<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = include_once './Config/Config.php';
$data = [
    'request_date' => '20230113100309', //请求时间
    'request_seq' => '574a47ee20404d4ca5bbc65a1e989a82',
    'accesser_acct' => 'd158ca13-5886-45',
];
$platform = 'H5';
// $platform = 'pc';
Factory::config($config);
$reponse = Factory::Contract()
    ->MerchantReg()
    ->setPlatform($platform)
    ->request($data);
echo 'response:' . $reponse . PHP_EOL;
