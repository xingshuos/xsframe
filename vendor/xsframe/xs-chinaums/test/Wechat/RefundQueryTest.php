<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = include_once './Config/Config.php';

$data = [
    'requestTimestamp' => date("YmdHis", time()),
    // 'merOrderId' => '101720220303143314904287',
    'merOrderId' => '101720220223102617810382',
    'instMid' => 'MINIDEFAULT',
];
Factory::config($config);
$reponse = Factory::Wechat()->RefundQuery()->request($data);
echo 'response:' . $reponse . PHP_EOL;
