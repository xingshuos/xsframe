<?php
/**
 * ALIPAY API: alipay.trust.User.token.get request
 *
 * @author auto create
 * @since  1.0, 2019-03-08 15:29:11
 */

namespace xsframe\pay\Alipay\Request;
class AlipayTrustUserTokenGetRequest
{
    /**
     * 入参json串
     **/
    private $aliTrustUserInfo;

    private $apiParas    = [];
    private $terminalType;
    private $terminalInfo;
    private $prodCode;
    private $apiVersion  = "1.0";
    private $notifyUrl;
    private $returnUrl;
    private $needEncrypt = false;


    public function setAliTrustUserInfo($aliTrustUserInfo)
    {
        $this->aliTrustUserInfo                = $aliTrustUserInfo;
        $this->apiParas["ali_trust_user_info"] = $aliTrustUserInfo;
    }

    public function getAliTrustUserInfo()
    {
        return $this->aliTrustUserInfo;
    }

    public function getApiMethodName()
    {
        return "alipay.trust.User.token.get";
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function getTerminalType()
    {
        return $this->terminalType;
    }

    public function setTerminalType($terminalType)
    {
        $this->terminalType = $terminalType;
    }

    public function getTerminalInfo()
    {
        return $this->terminalInfo;
    }

    public function setTerminalInfo($terminalInfo)
    {
        $this->terminalInfo = $terminalInfo;
    }

    public function getProdCode()
    {
        return $this->prodCode;
    }

    public function setProdCode($prodCode)
    {
        $this->prodCode = $prodCode;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setNeedEncrypt($needEncrypt)
    {

        $this->needEncrypt = $needEncrypt;

    }

    public function getNeedEncrypt()
    {
        return $this->needEncrypt;
    }

}
