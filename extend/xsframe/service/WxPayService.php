<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\service;

use xsframe\exception\ApiException;
use xsframe\pay\Weixin\Data\WxPayRefund;
use xsframe\util\ClientUtil;
use xsframe\util\FileUtil;
use xsframe\util\LoggerUtil;
use xsframe\pay\Weixin\Base\Config;
use xsframe\pay\Weixin\Data\WxPayAppPay;
use xsframe\pay\Weixin\Data\WxPayUnifiedOrder;
use xsframe\pay\Weixin\WxPayApi;
use xsframe\util\RandomUtil;

class WxPayService
{
    private $appid;
    private $appSecret;
    private $mchid;
    private $apikey;
    private $notifyUrl;
    private $config;
    private $certFilePath;
    private $keyFilePath;

    private $clientUrl = "https://api.mch.weixin.qq.com";

    public function __construct($appid, $mchid, $apikey, $notifyUrl, $certFileContent = null, $keyFileContent = null)
    {
        $this->appid = $appid;
        $this->mchid = $mchid;
        $this->apikey = $apikey;
        $this->notifyUrl = $notifyUrl;
        $this->appSecret = null; // 暂时不支持

        if ($certFileContent || $keyFileContent) {
            FileUtil::mkDirs(IA_ROOT . '/runtime/cert');
        }

        if ($certFileContent) {
            $this->certFilePath = IA_ROOT . '/runtime/cert/' . RandomUtil::random(128);
            file_put_contents($this->certFilePath, $certFileContent);
        }

        if ($keyFileContent) {
            $this->keyFilePath = IA_ROOT . '/runtime/cert/' . RandomUtil::random(128);
            file_put_contents($this->keyFilePath, $keyFileContent);
        }

        if (!$this->config instanceof Config) {
            $this->config = new Config($this->appid, $this->mchid, $this->notifyUrl, $this->apikey, $this->appSecret, $this->clientUrl, $this->certFilePath, $this->keyFilePath);
        }
    }

    /**
     * 微信支付
     *
     * @param string $body 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值。
     * @param string $goodsTag 订单优惠标记，代金券或立减优惠功能的参数
     * @param string $totalFee 订单总金额，单位：分
     * @param string $outTradeNo 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|*且在同一个商户号下唯一
     * @param int $attach 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据 1销售 2充值
     * @param string $tradeType 微信支付类型 调用接口提交的交易类型，取值如下：JSAPI，NATIVE，APP
     * @param string $openid 微信公号openid(公众号支付的时候用到)
     * @param string $bundleName 终端设备号(门店号或收银设备ID)，默认请传"WEB"
     * @param string $timeExpire 过期时间 订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
     * @param null $notifyUrl
     * @return array|bool
     * @throws ApiException
     */
    public function unifiedOrder($body, $totalFee, $outTradeNo, $attach, $tradeType = 'APP', $goodsTag = '', $openid = '', $bundleName = '', $timeExpire = '')
    {
        $unifiedOrder = new WxPayUnifiedOrder();

        $unifiedOrder->SetAppid($this->appid);
        $unifiedOrder->setBody($body);
        $unifiedOrder->SetMch_id($this->mchid);
        $unifiedOrder->SetTotal_fee($totalFee);
        $unifiedOrder->SetAttach($attach);
        $unifiedOrder->SetTrade_type($tradeType);
        $unifiedOrder->SetSpbill_create_ip(ClientUtil::getIp());
        $unifiedOrder->SetOut_trade_no($outTradeNo);
        $unifiedOrder->SetDevice_info($bundleName);
        $unifiedOrder->SetTime_expire($timeExpire);
        $unifiedOrder->SetOpenid($openid);
        $unifiedOrder->SetGoods_tag($goodsTag);
        $unifiedOrder->SetNotify_url($this->notifyUrl);
        if ($tradeType == 'NATIVE') {
            $unifiedOrder->SetProduct_id($outTradeNo);
        }
        $wxpayReturn = WxPayApi::unifiedOrder($this->config, $unifiedOrder, 6);
        //返回失败
        if (!($wxpayReturn['return_code'] == 'SUCCESS' && $wxpayReturn['result_code'] == 'SUCCESS')) {
            LoggerUtil::error($wxpayReturn['return_msg'] . $outTradeNo . ' ' . $totalFee . ' ' . $attach . ' ' . $wxpayReturn['return_msg']);
            throw new ApiException((isset($wxpayReturn['err_code_des']) ? $wxpayReturn['err_code_des'] : '') . $wxpayReturn['return_msg']);
        }
        return $wxpayReturn;
    }

    /**
     * 微信App支付
     *
     * @param $unifiedOrder
     *
     * @return array
     */
    public function WxPayAppPay($unifiedOrder)
    {
        $jsapi = new WxPayAppPay();
        $jsapi->SetAppid($unifiedOrder["appid"]);
        $jsapi->SetMch_id($this->config->getMerchantId());
        $jsapi->SetPrepay_id($unifiedOrder["prepay_id"]);
        $jsapi->SetPackage("Sign=wxAppPay");
        $jsapi->SetNonceStr(WxPayApi::getNonceStr());
        $timeStamp = time();
        $jsapi->SetTimeStamp($timeStamp);
        $jsapi->SetPaySign($jsapi->MakeSign($this->config, false));
        return $jsapi->GetValues();
    }

    /**
     * 退款
     * @return array|bool
     */
    public function WxPayRefund($outTradeNo, $outRefundNo, $totalFee, $refundFee = null, $userId = null)
    {
        $WxPayRefund = new WxPayRefund();

        $WxPayRefund->SetAppid($this->appid);
        $WxPayRefund->SetMch_id($this->mchid);
        $WxPayRefund->SetOut_trade_no($outTradeNo);
        $WxPayRefund->SetOut_refund_no($outRefundNo);
        $WxPayRefund->SetTotal_fee($totalFee * 100);

        if ($refundFee > 0) {
            $WxPayRefund->SetRefund_fee($refundFee * 100);
        } else {
            $WxPayRefund->SetRefund_fee($totalFee * 100);
        }

        if ($userId) {
            $WxPayRefund->SetOp_user_id($userId);
        } else {
            $WxPayRefund->SetOp_user_id($this->mchid);
        }

        $wxpayReturn = WxPayApi::refund($this->config, $WxPayRefund, 6);

        //返回失败
        if (!($wxpayReturn['return_code'] == 'SUCCESS' && $wxpayReturn['result_code'] == 'SUCCESS')) {
            throw new ApiException((isset($wxpayReturn['err_code_des']) ? $wxpayReturn['err_code_des'] : '') . $wxpayReturn['return_msg']);
        }

        return $wxpayReturn;
    }

}