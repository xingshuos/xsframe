<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = include_once './Config/Config.php';

$data = [];
Factory::config($config);
$reponse = Factory::Wechat()->callback($data);
echo Factory::Wechat()->success().PHP_EOL;
echo 'response:' . (int)$reponse . PHP_EOL;
