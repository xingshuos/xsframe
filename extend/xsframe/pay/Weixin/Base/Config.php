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

/**
 * Created by Date: 2019/4/11
 */

namespace xsframe\pay\Weixin\Base;


use xsframe\pay\Weixin\Intf\path;
use xsframe\pay\Weixin\Intf\unknown_type;
use xsframe\pay\Weixin\Intf\WxPayConfigInterface;

class Config extends WxPayConfigInterface
{
    protected $appId;
    protected $merchantId;
    protected $notifyUrl;
    protected $proxyHost = '0.0.0.0';
    protected $signType = 'MD5';
    protected $proxyPort = '0';
    protected $reportLevenl = 1;
    protected $key;
    protected $appSecret;
    protected $sslCertPath;
    protected $apiHost;
    protected $sslKeyPath;

    public function __construct($appId, $merchantId, $notifyUrl, $key, $appSecret, $apiHost, $sslCertPath = null, $sslKeyPath = null)
    {
        $this->apiHost = $apiHost;
        $this->appSecret = $appSecret;
        $this->appId = $appId;
        $this->merchantId = $merchantId;
        $this->notifyUrl = $notifyUrl;
        $this->key = $key;
        $this->sslCertPath = $sslCertPath;
        $this->sslKeyPath = $sslKeyPath;

    }

    /**
     * @return mixed
     */
    public function getApiHost()
    {
        return $this->apiHost;
    }


    /**
     * @param $notifyUrl
     *
     * @return $this
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @return mixed
     */
    public function getProxyHost()
    {
        return $this->proxyHost;
    }

    /**
     * @return mixed
     */
    public function getSignType()
    {
        return $this->signType;
    }

    /**
     * @return mixed
     */
    public function getProxyPort()
    {
        return $this->proxyPort;
    }

    /**
     * @return mixed
     */
    public function getReportLevenl()
    {
        return $this->reportLevenl;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return mixed
     */
    public function getSslCertPath()
    {
        return $this->sslCertPath;
    }

    public function GetProxy(&$proxyHost, &$proxyPort)
    {
        // TODO: Implement GetProxy() method.
    }


    /**
     * @return mixed
     */
    public function getSslKeyPath()
    {
        return $this->sslKeyPath;
    }


}