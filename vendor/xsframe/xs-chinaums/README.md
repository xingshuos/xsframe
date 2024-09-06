# 银联商务 支付 API
银联商务sdk 和网银支付接口不一样，请注意。

* 支持微信小程序支付的API接口
* 支持自助签约采集接口 [点击查看文档](/src/Service/Contract/README.md)
* 其他支付宝和银联支付需要时在添加

## 运行要求
* PHP 7.0版本以上

## 安装
```shell
composer require "xsframe/chinaums"
```
## 使用示例
更多示例可查看[test](./test/Wechat/)目录下的文件
```php
<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = [
    // 请求网关  https://api-mop.chinaums.com/v1
    'gateway' => 'https://test-api-open.chinaums.com/v1',
    // 商户号
    'mid' => '89********5678',
    // 终端号
    'tid' => '88*****01',
    // 加密 APPID
    'appid' => '10037e************a5e5a0006',
    // 加密 KEY
    'appkey' => '1c4e3****************9e5b312e8',
    // 回调验证需要的md5key
    'md5key' => 'impARTx**************aKXDhCaTCXJ6'
];

$data = [];
// 报文请求时间
$data['requestTimestamp'] = date("YmdHis", time());
// 订单号
$data['merOrderId'] = time() . uniqid();
// 业务类型 机构商户号 MINIDEFAULT|QRPAYDEFAULT|YUEDANDEFAULT
$data['instMid'] = 'MINIDEFAULT';
 // 订单描述 展示在支付截图中
$data['orderDesc'] = '账单描述';
// 支付总金额
$data['totalAmount'] = 2; 
// 微信必填
$data['subAppId'] = 'wx0bd72821b0ce53cb';  
// 微信必填  前端获取用户的openid 传给后台
$data['subOpenId'] = 'o4Sic5HPuB3j-LmnQTVIC4G_oYqY';

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
Factory::config($config);
$reponse = Factory::Wechat()->pay($data);
echo 'response:' . $reponse . PHP_EOL;

```
## 文档
[点击查看银联商务文档](https://open.chinaums.com/resources/?code=651539656974952&url=b7abc3a6-0c49-43d4-ad7d-f6dd16ff35eb)