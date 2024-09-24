<?php


namespace xsframe\wrapper;

use think\Exception;
use xsframe\enum\PayTypeEnum;
use xsframe\util\ArrayUtil;
use xsframe\util\LoggerUtil;
use xsframe\util\PriceUtil;
use think\facade\Db;
use think\Request;

class PayWechatNotifyWrapper
{
    public $get = null;
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->get = $this->request->getContent();

        $this->init();
    }

    private function init()
    {
        $get = $this->get;

        // $get = $this->getTextXml();
        // LoggerUtil::warning('start');
        // LoggerUtil::warning($get);
        // LoggerUtil::warning('end');

        if (empty($get)) {
            $this->fail("参数错误");
        }

        $get = ArrayUtil::xml2array($get);

        if (empty($get['result_code'])) {
            $this->fail();
        }
        if (empty($get["version"]) && ($get["result_code"] != "SUCCESS" || $get["return_code"] != "SUCCESS")) {
            LoggerUtil::error($get);
            $this->fail($get['result_code']);
        }
        if (!empty($get["version"]) && ($get["result_code"] != "0" || $get["status"] != "0")) {
            LoggerUtil::error($get['result_code']);
            $this->fail();
        }

        $this->get = $get;

        /* sign验证 start */
        $sign = $this->getNotifySign($get);
        if ($sign != $get['sign']) {
            $this->fail("签名验证失败");
        }
        /* sign验证 end */

        $this->payResult();
    }

    // 支付类型 pay_type 1微信 2支付宝 3余额 4后台支付
    private function payResult()
    {
        $attachArr = explode(":", $this->get['attach']);
        $data = [
            'module'       => $attachArr[0],
            'uniacid'      => $attachArr[1],
            'service_type' => $attachArr[2],
            'attach'       => trim($this->get['attach']),

            'pay_type'       => PayTypeEnum::WXPAY_TYPE,
            'out_trade_no'   => trim($this->get['out_trade_no']),
            'total_fee'      => PriceUtil::fen2yuan($this->get['total_fee']),
            'transaction_id' => trim($this->get['transaction_id']),
        ];

        try {
            $moduleName = $attachArr[0];
            $payPath = strval("\app\\{$moduleName}\\service\PayService");

            if (class_exists($payPath)) {
                $payService = new $payPath($data);
                $payService->payResult();
            }
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

    private function getNotifySign(array $data): string
    {
        ksort($data);
        $string1 = '';

        foreach ($data as $k => $v) {
            if (($v != '') && ($k != 'sign')) {
                $string1 .= $k . '=' . $v . '&';
            }
        }

        $apikey = $this->getApikey($data['attach']);
        return strtoupper(md5($string1 . 'key=' . $apikey));
    }

    private function succ()
    {
        $result = ["return_code" => "SUCCESS", "return_msg" => "OK"];
        echo ArrayUtil::array2xml($result);
        exit();
    }

    private function fail($msg = '签名失败')
    {
        try {
            $moduleName = app('http')->getName();
            $map = config("app.app_map");
            $realModuleName = array_key_exists($moduleName, $map) ? $map[$moduleName] : $moduleName;
            $moduleName = $realModuleName ?: $moduleName;

            $this->get['module'] = $moduleName;
            $payPath = strval("\app\\{$moduleName}\\service\PayService");
            if (class_exists($payPath)) {
                $payService = new $payPath($this->get);
                $payService->payResultError();
            }
        } catch (Exception $e) {
        }

        $result = ["return_code" => "FAIL", "return_msg" => $msg];
        echo ArrayUtil::array2xml($result);
        exit();
    }

    // 获取微信支付配置
    private function getApikey($attach)
    {
        $attachArr = explode(":", $attach);
        $uniacid = $attachArr[1];
        $settings = Db::name('sys_account')->where(['uniacid' => $uniacid])->value('settings');
        $settings = unserialize($settings);
        return $settings['wxpay']['apikey'];
    }

    // 测试数据
    private function getTextXml()
    {
        $resultXml = <<<EOF
            <xml><appid><![CDATA[wx1feae3300acbeeef]]></appid>
                <attach><![CDATA[gm_arts:8:2]]></attach>
                <bank_type><![CDATA[OTHERS]]></bank_type>
                <cash_fee><![CDATA[1]]></cash_fee>
                <fee_type><![CDATA[CNY]]></fee_type>
                <is_subscribe><![CDATA[Y]]></is_subscribe>
                <mch_id><![CDATA[1610991191]]></mch_id>
                <nonce_str><![CDATA[xhucdv5wnnd9k8in77au48xlchkiyjud]]></nonce_str>
                <openid><![CDATA[oaafU6J1RNSW-wDUFSOWjndpWDms]]></openid>
                <out_trade_no><![CDATA[CC20230417667882467862]]></out_trade_no>
                <result_code><![CDATA[SUCCESS]]></result_code>
                <return_code><![CDATA[SUCCESS]]></return_code>
                <sign><![CDATA[8E27CD353C8A8963DC0FCBA304CA333A]]></sign>
                <time_end><![CDATA[20230417140805]]></time_end>
                <total_fee>1</total_fee>
                <trade_type><![CDATA[NATIVE]]></trade_type>
                <transaction_id><![CDATA[4200001773202304171102841447]]></transaction_id>
            </xml>
EOF;
        return trim($resultXml);
    }
}