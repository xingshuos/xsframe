<?php


namespace xsframe\wrapper;

use think\Exception;
use xsframe\enum\PayTypeEnum;
use xsframe\facade\service\PayServiceFacade;
use xsframe\util\LoggerUtil;
use think\facade\Db;
use think\Request;

class PayAlipayNotifyWrapper
{
    public $get = null;
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->get = $this->request->param();

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
        $data = [
            'module'       => $attachArr[0],
            'uniacid'      => $attachArr[1],
            'service_type' => $attachArr[2],

            'pay_type'       => PayTypeEnum::ALIPAY_TYPE,
            'out_trade_no'   => $this->get['out_trade_no'],
            'total_fee'      => $this->get['total_amount'],
            'transaction_id' => $this->get['trade_no'],
        ];

        try {
            $moduleName = $attachArr[0];
            $payPath = strval("\app\\{$moduleName}\\service\PayService");
            $payService = new $payPath($data);
            $payService->payResult();
        } catch (Exception $e) {
            LoggerUtil::error($e->getMessage());
        }

        $this->addPayLog($data);
        $this->succ();
    }

    private function addPayLog($data): bool
    {
        try {
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
        } catch (Exception $e) {

        }
        return true;
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
        //        $resultData = array(
        //            'gmt_create'       => '2023-04-22 11:03:04',
        //            'charset'          => 'UTF-8',
        //            'gmt_payment'      => '2023-04-22 11:03:10',
        //            'notify_time'      => '2023-04-22 11:06:27',
        //            'subject'          => '国美线上课程-支付测试',
        //            'sign'             => 'cUEBe/W14v8KPjtqeBF6T6xVggPAoWM57s0ZEWezMNjRwdsVzs+e89qXfyxotxv7st+D2F9RWCg9cXjMR6iqf22N7S4xmFn5dyrqZLHBplNH5qi1t5ZDJgMN5Z304vCuqwJy9CzDYg+5YVkzc7gAI9d9/KEqpFMt0fYBksrMC9F6YYW8EDjMl1QM0nEVRM/sr+nTfHYG93wMfxdkXFUW4+BmAPfoosPfjYaokMhqOTYJmE49SrDn70QDxnnGbPhRyq/EvZESgHwspDn/0hAIrEfygQW5nKPiuMLpUlAitBSyFSOKY/XLCwAbhQVYxUAOjZz4sTnoQUthttTej9mikA==',
        //            'buyer_id'         => '2088112861965621',
        //            'body'             => 'gm_arts:8:2',
        //            'invoice_amount'   => '0.01',
        //            'version'          => '1.0',
        //            'notify_id'        => '2023042201222110310065621427432976',
        //            'fund_bill_list'   => '[{"amount":"0.01","fundChannel":"PCREDIT"}]',
        //            'notify_type'      => 'trade_status_sync',
        //            'out_trade_no'     => 'CC20230422847766790424',
        //            'total_amount'     => '0.01',
        //            'trade_status'     => 'TRADE_SUCCESS',
        //            'trade_no'         => '2023042222001465621442440856',
        //            'auth_app_id'      => '2021002112625616',
        //            'receipt_amount'   => '0.01',
        //            'point_amount'     => '0.00',
        //            'buyer_pay_amount' => '0.01',
        //            'app_id'           => '2021002112625616',
        //            'sign_type'        => 'RSA2',
        //            'seller_id'        => '2088931657381979',
        //        );
        $resultData = [
            'gmt_create'       => '2024-01-14 14:38:24',
            'charset'          => 'UTF-8',
            'seller_email'     => 'zhifu@lymlart.com',
            'subject'          => '给孩子的留言',
            'sign'             => 'LWuOwV1GCP+oD+Vha61DbF5Ig4QXSMTWtSCa9iTp8L7AaLMt2nPp9woHGWtXKqvfl48wiyWgPIZfxHc4dZnIeNT8h/SFFYFX/9lOBqEWJO08LO/OLtQfNzfy6v89aBackiMdAWeQTC7Ba8FUG4LWm8jPAG+V9MEf4+biIMzg+hkgWN3Oadi3mOJuV6VX2jp/OZbse8ZZieU7ZLbKIVB4usEtFTMQfmsPGz40vgLD7gepq0/RBp2Mb8uihCyQBzVKSDps7tk4SLmZMwfejxp1O9uDerHCTY50GiJawM2Z/rcynO2LW8ZxrXvgUIvqLdxwTVEw80USaM1Z3RiU5Lpc9A==',
            'body'             => 'jt_mail:1:1',
            'buyer_id'         => '2088112861965621',
            'invoice_amount'   => '0.01',
            'notify_id'        => '2024011401222143824065621462286766',
            'fund_bill_list'   => '[{"amount":"0.01","fundChannel":"PCREDIT"}]',
            'notify_type'      => 'trade_status_sync',
            'trade_status'     => 'TRADE_SUCCESS',
            'receipt_amount'   => '0.01',
            'buyer_pay_amount' => '0.01',
            'app_id'           => '2021002112625616',
            'sign_type'        => 'RSA2',
            'seller_id'        => '2088931657381979',
            'gmt_payment'      => '2024-01-14 14:38:24',
            'notify_time'      => '2024-01-14 14:38:25',
            'version'          => '1.0',
            'out_trade_no'     => 'JM20240114209642698664',
            'total_amount'     => '0.01',
            'trade_no'         => '2024011422001465621437015339',
            'auth_app_id'      => '2021002112625616',
            'buyer_logon_id'   => '342***@qq.com',
            'point_amount'     => '0.00',
        ];
        return $resultData;
    }
}