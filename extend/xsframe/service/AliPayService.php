<?php

namespace xsframe\service;

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

use xsframe\pay\Alipay\Request\AlipayTradePagePayRequest;
use xsframe\util\ErrorUtil;
use xsframe\pay\Alipay\AopClient;
use xsframe\pay\Alipay\Data\AlipayTradeAppPayData;
use xsframe\pay\Alipay\Data\AlipayTradeRefundData;
use xsframe\pay\Alipay\Request\AlipayTradeAppPayRequest;
use xsframe\pay\Alipay\Request\AlipayTradeFastpayRefundQueryRequest;
use xsframe\pay\Alipay\Request\AlipayTradeQueryRequest;
use xsframe\pay\Alipay\Request\AlipayTradeRefundRequest;
use xsframe\pay\Alipay\Request\AlipayTradeWapPayRequest;

class AliPayService
{
    private $gatewayUrl;
    private $appId;
    private $encryptKey;
    private $rsaPrivateKey;
    private $rsaPublicKey;
    private $notifyUrl;
    private $returnUrl;
    private $clientAop;

    public function __construct($gatewayUrl, $appId, $encryptKey, $rsaPrivateKey, $rsaPublicKey, $notifyUrl, $returnUrl = '')
    {
        $this->gatewayUrl    = $gatewayUrl;
        $this->appId         = $appId;
        $this->encryptKey    = $encryptKey;
        $this->rsaPrivateKey = $rsaPrivateKey;
        $this->rsaPublicKey  = $rsaPublicKey;
        $this->notifyUrl     = $notifyUrl;
        $this->returnUrl     = $returnUrl;

        if (!$this->clientAop instanceof AopClient) {
            $this->clientAop = new AopClient($gatewayUrl, $appId, $rsaPrivateKey, $rsaPublicKey, $encryptKey, 'RSA2');
        }
    }

    /**
     * 支付宝pagePay支付
     * 统一收单下单并支付页面接口 alipay.trade.page.pay https://opendocs.alipay.com/open/028r8t
     * @param array $params = array('total_amount' => 0.01 支付金额 单位元,'out_trade_no' => 商户网站唯一订单号,'subject' => '标题','body' => '类型',);
     *
     * @return string
     * @throws \Exception
     */
    public function pagePay(array $params): string
    {
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.page.pay
        $request = new AlipayTradePagePayRequest();

        $biz_content = $this->getBizContent($params);
        $request->setNotifyUrl($this->notifyUrl);
        $request->setReturnUrl($this->returnUrl);
        $request->setBizContent($biz_content);
        $response = $this->pageExecute($request, 'GET');
        return $response;
    }

    /**
     * 支付宝WapPay支付
     *
     * @param array $params = array('total_amount' => 0.01 支付金额 单位元,'out_trade_no' => 商户网站唯一订单号,'subject' => '标题','body' => '类型',);
     *
     * @return string
     * @throws \Exception
     */
    public function wapPay(array $params): string
    {
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.wap.pay
        $request = new AlipayTradeWapPayRequest();

        $biz_content = $this->getBizContent($params);
        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($biz_content);
        $response = $this->pageExecute($request, 'GET');
        return $response;
    }

    /**
     * 支付宝App支付
     *
     * @param AlipayTradeAppPayData $params = array('total_amount' => 0.01 支付金额 单位元,'out_trade_no' => 商户网站唯一订单号,'subject' => '标题','body' => '类型',);
     *
     * @return string
     * @throws \Exception
     */
    public function appPay(AlipayTradeAppPayData $params): string
    {
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request     = new AlipayTradeAppPayRequest();
        $biz_content = $this->getBizContent($params->toArray());
        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($biz_content);

        $response = $this->clientAop->sdkExecute($request);
        return $response;
    }

    /**
     * 根据订单号查询支付结果【 统一收单线下交易查询】
     *
     * @param string $out_trade_no 订单支付时传入的商户订单号 trade_no,out_trade_no如果同时存在优先取trade_no
     * @param string $trade_no 支付宝交易号，和商户订单号不能同时为空
     *
     * @return bool
     * @throws \Exception
     */
    public function getPayStatusByTradeNo($out_trade_no, $trade_no)
    {
        $request     = new AlipayTradeQueryRequest();
        $content     = [
            'out_trade_no' => $out_trade_no,
            'trade_no'     => $trade_no
        ];
        $biz_content = $this->getBizContent($content);
        $request->setBizContent($biz_content);
        $result = $this->clientAop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return $result->$responseNode;
        } else {
            return false;
        }
    }

    /**
     * 支付宝退款
     *
     * @param $params = array('out_trade_no' => 1 商户网站唯一订单号 out_request_no => 2 退款请求编号 refund_amount退款金额 refund_reason 退款的原因说明可为空 )
     *
     * @return array
     * @throws \Exception
     */
    public function refund(AlipayTradeRefundData $alipayTradeRefundData): array
    {
        $request = new AlipayTradeRefundRequest();

        $biz_content = $this->getBizContent($alipayTradeRefundData->toArray());
        $request->setBizContent($biz_content);

        $result = $this->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'refund_status' => 1,
                'trade_no'      => $result->$responseNode->trade_no,
                'out_trade_no'  => $result->$responseNode->out_trade_no,
                'fund_change'   => $result->$responseNode->fund_change,
                'refund_fee'    => $result->$responseNode->refund_fee,
            ];
        } else {
            return [
                'refund_status'        => -1,
                'refund_error_message' => $result->$responseNode->sub_code . ' ' . $result->$responseNode->sub_msg,
            ];
        }
    }

    /**
     * 统一收单交易退款查询
     *
     * @param $params = array('out_trade_no' => 1 商户网站唯一订单号 out_request_no请求退款接口时，传入的退款请求号 )
     *
     * @return array
     * @throws \Exception
     */
    public function refundQuery(array $params): array
    {
        $request = new AlipayTradeFastpayRefundQueryRequest();

        $biz_content = $this->getBizContent($params);
        $request->setBizContent($biz_content);

        $result       = $this->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return ErrorUtil::error(1, "退款成功", $result->$responseNode);
        } else {
            return ErrorUtil::error($resultCode, $result->$responseNode->sub_msg);
        }
    }

    /**
     * 验证异步返回参数
     *
     * @param        $params
     * @param string $signType
     *
     * @return bool
     */
    public function rsaCheck($params, $signType = 'RSA2')
    {
        $data = $this->clientAop->rsaCheckV1($params, null, $signType);
        if ($data === false) {
            $data = $this->clientAop->rsaCheckV2($params, null, $signType);
        }
        return $data;
    }

    /**
     * 发起http请求
     *
     * @param      $request
     * @param null $authToken
     * @param null $appInfoAuthtoken
     *
     * @return mixed|\SimpleXMLElement
     * @throws \Exception
     */
    private function execute($request, $authToken = null, $appInfoAuthtoken = null)
    {
        $response = $this->clientAop->execute($request, $authToken, $appInfoAuthtoken);
        return $response;
    }

    /**
     * 发起网页支付请求
     *
     * @param        $request
     * @param string $httpmethod 请求方式 POST GET
     *
     * @return \xsframe\pay\Alipay\提交表单HTML文本|string
     * @throws \Exception
     */
    private function pageExecute($request, $httpmethod = "POST")
    {
        $response = $this->clientAop->pageExecute($request, $httpmethod);
        return $response;
    }

    /**
     * 处理提交参数
     *
     * @param array $params
     *
     * @return string
     */
    private function getBizContent(array $params): string
    {
        $params      = array_filter($params);
        $biz_content = [];

        foreach ($params as $key => $item) {
            $biz_content[$key] = $item;
        }

        $biz_content = json_encode($biz_content);
        return $biz_content;
    }
}