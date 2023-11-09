<?php


namespace xsframe\wrapper;

use xsframe\enum\PayTypeEnum;
use xsframe\facade\service\PayServiceFacade;
use xsframe\util\LoggerUtil;
use xsframe\util\PriceUtil;
use think\facade\Db;
use think\Request;

class PayAlipayNotifyWrapper
{
    public $get = null;
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->get     = $this->request->param();

        $this->init();
    }

    private function init()
    {
        // LoggerUtil::info($this->get);
        // $this->get = $this->getTextData();

        if (empty($this->get)) {
            exit("fail");
        }
        if ($this->get["trade_status"] != "TRADE_SUCCESS" && $this->get["trade_status"] != "TRADE_FINISHED") {
            exit("fail");
        }
        $rsaCheck = PayServiceFacade::aliRsaCheck($this->get, $this->get['sign_type']);
        if ($rsaCheck === false) {
            LoggerUtil::error($this->get);
            exit("fail");
        }
        $this->payResult();
    }

    private function payResult()
    {
        $attachArr = explode(":", $this->get['body']);
        $data      = [
            'module'       => $attachArr[0],
            'uniacid'      => $attachArr[1],
            'service_type' => $attachArr[2],

            'pay_type'       => PayTypeEnum::ALIPAY_TYPE,
            'out_trade_no'   => $this->get['out_trade_no'],
            'total_fee'      => $this->get['total_amount'],
            'transaction_id' => $this->get['trade_no'],
        ];

        $moduleName = $attachArr[0];
        $payPath    = strval("\app\\{$moduleName}\\controller\Pay");
        $order      = new $payPath($data);
        $ret        = $order->payResult();

        if ($ret) {
            $this->addPayLog($data);
            $this->succ();
        } else {
            $this->fail();
        }
    }

    private function addPayLog($data)
    {
        $payLogData = [
            'uniacid'    => $data['uniacid'],
            'type'       => $data['pay_type'],
            'ordersn'    => $data['out_trade_no'],
            'fee'        => $data['total_fee'],
            'module'     => $data['module'],
            'status'     => 1,
            'createtime' => time(),
        ];
        Db::name('sys_paylog')->insert($payLogData);
    }

    private function succ()
    {
        exit("success");
    }

    private function fail()
    {
        exit("fail");
    }

    private function getTextData()
    {
        $resultData = array(
            'gmt_create'       => '2023-04-22 11:03:04',
            'charset'          => 'UTF-8',
            'gmt_payment'      => '2023-04-22 11:03:10',
            'notify_time'      => '2023-04-22 11:06:27',
            'subject'          => '国美线上课程-支付测试',
            'sign'             => 'cUEBe/W14v8KPjtqeBF6T6xVggPAoWM57s0ZEWezMNjRwdsVzs+e89qXfyxotxv7st+D2F9RWCg9cXjMR6iqf22N7S4xmFn5dyrqZLHBplNH5qi1t5ZDJgMN5Z304vCuqwJy9CzDYg+5YVkzc7gAI9d9/KEqpFMt0fYBksrMC9F6YYW8EDjMl1QM0nEVRM/sr+nTfHYG93wMfxdkXFUW4+BmAPfoosPfjYaokMhqOTYJmE49SrDn70QDxnnGbPhRyq/EvZESgHwspDn/0hAIrEfygQW5nKPiuMLpUlAitBSyFSOKY/XLCwAbhQVYxUAOjZz4sTnoQUthttTej9mikA==',
            'buyer_id'         => '2088112861965621',
            'body'             => 'gm_arts:8:2',
            'invoice_amount'   => '0.01',
            'version'          => '1.0',
            'notify_id'        => '2023042201222110310065621427432976',
            'fund_bill_list'   => '[{"amount":"0.01","fundChannel":"PCREDIT"}]',
            'notify_type'      => 'trade_status_sync',
            'out_trade_no'     => 'CC20230422847766790424',
            'total_amount'     => '0.01',
            'trade_status'     => 'TRADE_SUCCESS',
            'trade_no'         => '2023042222001465621442440856',
            'auth_app_id'      => '2021002112625616',
            'receipt_amount'   => '0.01',
            'point_amount'     => '0.00',
            'buyer_pay_amount' => '0.01',
            'app_id'           => '2021002112625616',
            'sign_type'        => 'RSA2',
            'seller_id'        => '2088931657381979',
        );
        return $resultData;
    }
}