<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = include_once './Config/Config.php';

$data = [];
// 报文请求时间
$data['requestTimestamp'] = date("YmdHis", time());
// 订单号
$data['merOrderId'] = '33XF'.time() . uniqid();
// 业务类型 机构商户号 MINIDEFAULT|QRPAYDEFAULT|YUEDANDEFAULT
$data['instMid'] = 'MINIDEFAULT';
 // 订单描述 展示在支付截图中
$data['orderDesc'] = '账单描述';
// 支付总金额
$data['totalAmount'] = 2; 
// 微信必填
$data['subAppId'] = 'wxca3a56d63895b431';  
// 微信必填  前端获取用户的openid 传给后台
$data['subOpenId'] = 'o4Sic5HPuB3j-LmnQTVIC4G_oYqY';
$data['tradeType'] = 'JSAPI';

//分账
// $subOrders = [];
// $sub['totalAmount'] = 1;      // 支付子金额
// $sub['mid'] = "898127210280001";      //
// $sub1['totalAmount'] = 1;      // 支付子金额
// $sub1['mid'] = "988460101800201";      //
// $subOrders[] = $sub;
// $subOrders[] = $sub1;
// $data['divisionFlag'] = 'true'; //分账标识
// $data['subOrders'] = $subOrders;      //

// 使用方法1
Factory::config($config);
$app = Factory::Wechat()->mini();
$reponse = $app->request($data);
echo 'response:' . $reponse . PHP_EOL;


// 使用方法2
// Factory::config($config);
// $reponse = Factory::Wechat()->pay($data);
// echo 'response:' . $reponse . PHP_EOL;
