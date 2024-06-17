<?php

namespace xsframe\service;

use xsframe\base\BaseService;
use xsframe\exception\ApiException;
use xsframe\pay\Alipay\Data\AlipayTradeRefundData;
use xsframe\util\ErrorUtil;
use xsframe\util\PriceUtil;
use xsframe\util\RandomUtil;

class PayService extends BaseService
{
    private $wxPayService;
    private $aliPayService;

    /**
     * wap - 微信js sdk支付
     * @param $ordersn
     * @param $price
     * @param string $title
     * @param int $serviceType
     * @param string $openid
     * @return array
     * @throws ApiException
     */
    public function wxPay($ordersn, $price, string $title = '', int $serviceType = 0, string $openid = ''): array
    {
        try {
            $body = $title;
            $orderPrice = PriceUtil::yuan2fen($price);                          // 订单总金额，单位为：分
            $outTradeNo = $ordersn;                                             // 订单号
            $attach = $this->module . ":" . $this->uniacid . ":" . $serviceType;// 商品附加信息（订单支付成功回调原样返回数据）
            $tradeType = 'JSAPI';                                               // 支付类型
            $goodsTag = "";                                                     // 商品优惠信息
            $bundleName = "WEB";
            $timeExpire = date('YmdHis', time() + 600); // 订单过期时间 10分钟

            $paymentSet = $this->account['settings']['wxpay'];

            if (!$this->wxPayService instanceof wxPayService) {
                $notifyUrl = $this->siteRoot . "/" . $this->module . "/wechat/notify";
                $this->wxPayService = new WxPayService($paymentSet['appid'], $paymentSet['mchid'], $paymentSet['apikey'], $notifyUrl);
            }

            if (empty($openid)) {
                throw new ApiException("微信授权失效，请刷新下页面再付费即可");
            }

            $unifiedReturn = $this->wxPayService->unifiedOrder($body, $orderPrice, $outTradeNo, $attach, $tradeType, $goodsTag, $openid, $bundleName, $timeExpire);

            $wOpt = [];
            $string = "";
            $wOpt['appId'] = $paymentSet['appid'];
            $wOpt['timeStamp'] = strval(time());
            $wOpt['nonceStr'] = RandomUtil::random(8);
            $wOpt['package'] = 'prepay_id=' . $unifiedReturn['prepay_id'];
            $wOpt['signType'] = 'MD5';
            ksort($wOpt, SORT_STRING);
            foreach ($wOpt as $key => $v) {
                $string .= "{$key}={$v}&";
            }
            $string .= "key=" . $paymentSet['apikey'];
            $wOpt['paySign'] = strtoupper(md5($string));

            return $wOpt;
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * wap - 微信native支付
     * @param $ordersn
     * @param $price
     * @param string $title
     * @param int|string $serviceType
     * @return string|array
     * @throws ApiException
     */
    public function wxNative($ordersn, $price, $serviceType = 1, string $title = '',string $notifyUrl='')
    {
        try {
            $body = $title;
            $totalFee = PriceUtil::yuan2fen($price);
            $outTradeNo = $ordersn;
            $attach = $this->module . ":" . $this->uniacid . ":" . $serviceType;
            $tradeType = "NATIVE";
            $goodsTag = "";

            if (!$this->wxPayService instanceof WxPayService) {
                $paymentSet = $this->account['settings']['wxpay'];
                if(empty($notifyUrl)){
                     $notifyUrl = $this->siteRoot . "/" . $this->module . "/wechat/notify";
                }
              

                if (empty($paymentSet['appid']) || empty($paymentSet['mchid']) || empty($paymentSet['apikey'])) {
                    throw new ApiException("后台微信支付配置信息未配置");
                }

                $this->wxPayService = new WxPayService($paymentSet['appid'], $paymentSet['mchid'], $paymentSet['apikey'], $notifyUrl);
            }

            return $this->wxPayService->unifiedOrder($body, $totalFee, $outTradeNo, $attach, $tradeType, $goodsTag);
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage());
        }
    }


    // 获取token与user_id（唯一）
    // 文档地址1:https://opendocs.alipay.com/open/284/web?pathHash=9ec22daf
    // 文档地址2:https://opendocs.alipay.com/open/02np96?pathHash=db2cdfed
    public function aliOauthToken($authCode)
    {
        try {
            if (!$this->aliPayService instanceof AliPayService) {
                $paymentSet = $this->account['settings']['alipay'];
                $gatewayUrl = "https://openapi.alipay.com/gateway.do";

                if (empty($paymentSet['appid']) || empty($paymentSet['encrypt_key'])) {
                    throw new ApiException("未配置支付宝支付参数");
                }

                $this->aliPayService = new AliPayService($gatewayUrl, $paymentSet['appid'], $paymentSet['encrypt_key'], $paymentSet['private_key'], $paymentSet['public_key']);
            }
            return $this->aliPayService->aliOauthToken($authCode);
        } catch (ApiException|\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    // 获取支付宝用户信息
    // 文档地址1:https://opendocs.alipay.com/open/284/web?pathHash=9ec22daf
    // 文档地址2:https://opendocs.alipay.com/open/02np96?pathHash=db2cdfed
    public function aliUserInfo($accessToken)
    {
        try {
            if (!$this->aliPayService instanceof AliPayService) {
                $paymentSet = $this->account['settings']['alipay'];
                $gatewayUrl = "https://openapi.alipay.com/gateway.do";

                if (empty($paymentSet['appid']) || empty($paymentSet['encrypt_key'])) {
                    throw new ApiException("未配置支付宝支付参数");
                }

                $this->aliPayService = new AliPayService($gatewayUrl, $paymentSet['appid'], $paymentSet['encrypt_key'], $paymentSet['private_key'], $paymentSet['public_key']);
            }
            return $this->aliPayService->aliUserInfo($accessToken);
        } catch (ApiException|\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * web - 支付宝支付 pc网页支付
     * @param $ordersn
     * @param $price
     * @param int $serviceType
     * @param string $title
     * @param null $returnUrl
     * @param bool $returnQrcode
     * @param int $qrcodeWidth
     * @return string|array
     * @throws ApiException
     */
    public function aliPagePay($ordersn, $price, int $serviceType = 0, string $title = '', $returnUrl = null, bool $returnQrcode = false, int $qrcodeWidth = 300)
    {
        try {
            $params = [
                'out_trade_no' => $ordersn,
                'total_amount' => $price,
                'subject'      => $title,
                'body'         => $this->module . ":" . $this->uniacid . ":" . $serviceType,
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
            ];

            if ($returnQrcode) {
                $params['qr_pay_mode'] = 4;
                $params['qrcode_width'] = $qrcodeWidth;
            }

            $params['return_url'] = $returnUrl;

            if (!$this->aliPayService instanceof AliPayService) {
                $paymentSet = $this->account['settings']['alipay'];
                $gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $notifyUrl = $this->siteRoot . "/" . $this->module . "/alipay/notify";

                if (empty($paymentSet['appid']) || empty($paymentSet['encrypt_key'])) {
                    throw new ApiException("未配置支付宝支付参数");
                }

                $this->aliPayService = new AliPayService($gatewayUrl, $paymentSet['appid'], $paymentSet['encrypt_key'], $paymentSet['private_key'], $paymentSet['public_key'], $notifyUrl, $returnUrl);
            }

            return $this->aliPayService->pagePay($params);
        } catch (ApiException|\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * wap - 支付宝支付 手机端支付
     * @param $ordersn
     * @param $price
     * @param int $serviceType
     * @param string $title
     * @param null $returnUrl
     * @param bool $returnQrcode
     * @param int $qrcodeWidth
     * @return string
     * @throws ApiException
     */
    public function wapPay($ordersn, $price, int $serviceType = 0, string $title = '', $returnUrl = null)
    {
        try {
            $params = [
                'out_trade_no' => $ordersn,
                'total_amount' => $price,
                'subject'      => $title,
                'body'         => $this->module . ":" . $this->uniacid . ":" . $serviceType,
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
            ];

            $params['return_url'] = $returnUrl;

            if (!$this->aliPayService instanceof AliPayService) {
                $paymentSet = $this->account['settings']['alipay'];
                $gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $notifyUrl = $this->siteRoot . "/" . $this->module . "/alipay/notify";

                if (empty($paymentSet['appid']) || empty($paymentSet['encrypt_key'])) {
                    throw new ApiException("未配置支付宝支付参数");
                }

                $this->aliPayService = new AliPayService($gatewayUrl, $paymentSet['appid'], $paymentSet['encrypt_key'], $paymentSet['private_key'], $paymentSet['public_key'], $notifyUrl, $returnUrl);
            }

            return $this->aliPayService->wapPay($params);
        } catch (ApiException|\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 支付宝退款
     * @return array
     * @throws ApiException
     */
    public function aliRefund($out_trade_no, $refund_amount, $refund_reason)
    {
        try {
            if (!$this->aliPayService instanceof AliPayService) {
                $paymentSet = $this->account['settings']['alipay'];
                $gatewayUrl = "https://openapi.alipay.com/gateway.do";
                $notifyUrl = $this->siteRoot . "/alipay/notify";
                $this->aliPayService = new AliPayService($gatewayUrl, $paymentSet['appid'], $paymentSet['encrypt_key'], $paymentSet['private_key'], $paymentSet['public_key'], $notifyUrl);
            }

            $AlipayTradeRefundData = new AlipayTradeRefundData();
            $AlipayTradeRefundData->setOutTradeNo($out_trade_no);
            $AlipayTradeRefundData->setRefundAmount(floatval($refund_amount) * 100);
            $AlipayTradeRefundData->setRefundReason($refund_reason);

            return $this->aliPayService->refund($AlipayTradeRefundData);
        } catch (ApiException|\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 验证支付宝支付回调参数
     *
     * @param $postData
     * @param $signType
     * @return bool
     */
    public function aliRsaCheck($postData, $signType): bool
    {
        if (!$this->aliPayService instanceof AliPayService) {
            $paymentSet = $this->account['settings']['alipay'];
            $gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $notifyUrl = $this->siteRoot . "/alipay/notify";

            $this->aliPayService = new AliPayService($gatewayUrl, $paymentSet['appid'], $paymentSet['encrypt_key'], $paymentSet['private_key'], $paymentSet['public_key'], $notifyUrl);
        }
        return $this->aliPayService->rsaCheck($postData, $signType);
    }
}