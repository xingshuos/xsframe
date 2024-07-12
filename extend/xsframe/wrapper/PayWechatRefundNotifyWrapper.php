<?php


namespace xsframe\wrapper;

use think\Exception;
use xsframe\util\AesEncoderUtil;
use xsframe\util\ArrayUtil;
use xsframe\util\LoggerUtil;
use think\facade\Db;
use think\Request;

class PayWechatRefundNotifyWrapper
{
    const MCH_KEY = 'xxxxxxxxxxxxxxxxxxxxxxxxxxx';

    public $get = null;
    private $module = null;
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

        if (empty($get['return_code'])) {
            $this->fail();
        }
        if ($get["return_code"] != "SUCCESS") {
            LoggerUtil::error($get);
            $this->fail($get['result_code']);
        }

        $this->get = $get;

        $this->refundResult();
    }

    // 支付类型 pay_type 1微信 2支付宝 3余额 4后台支付
    private function refundResult()
    {
        try {
            $moduleName = app('http')->getName();
            $data = (array)$this->getReqInfoData($this->get);
            $data['module'] = $moduleName;

            $payPath = strval("\app\\{$moduleName}\\service\PayService");
            $payService = new $payPath($data);
            $payService->refundResult();
        } catch (Exception $e) {
            LoggerUtil::error($e->getMessage());
        }

        $this->refundPayLog($data);
        $this->succ();
    }

    // 退款记录修改
    private function refundPayLog($data): bool
    {
        try {
            $data['out_trade_no'] && Db::name('sys_paylog')->where(['ordersn' => $data['out_trade_no']])->update(['status' => -1]);
        } catch (Exception $e) {

        }
        return true;
    }

    private function getReqInfoData(array $data): array
    {
        $apikey = $this->getApiKeyByAppid($data['appid']);
        return AesEncoderUtil::decrypt($data['req_info'], $apikey);
    }

    private function succ()
    {
        $result = ["return_code" => "SUCCESS", "return_msg" => "OK"];
        echo ArrayUtil::array2xml($result);
        exit();
    }

    private function fail($msg = '签名失败')
    {
        $result = ["return_code" => "FAIL", "return_msg" => $msg];
        echo ArrayUtil::array2xml($result);
        exit();
    }

    // 获取微信支付配置
    private function getApiKeyByAppid($appid)
    {
        $apikey = "";
        $accountList = Db::name('sys_account')->field("uniacid,settings")->where(['status' => 1, 'deleted' => 0])->select()->toArray();
        foreach ($accountList as $item) {
            $settings = unserialize($item['settings']);
            if ($settings && $settings['wxpay'] && $settings['wxpay']['appid'] == $appid) {
                $apikey = $settings['wxpay']['apikey'];
            }
        }
        return $apikey;
    }

    // 获取微信支付配置
    private function getApikey($uniacid)
    {
        $settings = Db::name('sys_account')->where(['uniacid' => $uniacid])->value('settings');
        $settings = unserialize($settings);
        return $settings['wxpay']['apikey'];
    }

    // 测试数据
    private function getTextXml()
    {
        $resultXml = <<<EOF
            <xml>
                <return_code>SUCCESS</return_code>
                <appid><![CDATA[wx21490d8841df7637]]></appid>
                <mch_id><![CDATA[1606994267]]></mch_id>
                <nonce_str><![CDATA[4685a1d8fd42adefde6f46b866c718df]]></nonce_str>
                <req_info><![CDATA[cnPSF/Aqj5fx7OA8CetNSqKUdS6uTTKVcvogonC9QHiq7xzt+7CEWHCdQ2KS3ou6ejkI1Z3L9Y+46yIBzFOBzuF5QCfg4Q106gMtw2DDsifBDg92gh2pb4zlUvtz08BeYGZoaMNoyCnfMqClWCpsfvhzJ987uPh4iVgj2lm5CBbRkglSlOCM67oW76f7h4dwb8fwHKpr6FdH1vOq8+2L1uHoD1XRlKUzkUWRqCNZPaJaH1urWUwselbKspQld2c//rPiefGRcR2RCriruGydt4k8+4YvsbEJNbhFy/HoYbVmlYgmKYqFrt+yw5vr/9pPMyJSdutN4yMjx0l1n93BRplDphvLJ+5asT1Tae51/2F2JVGYt/Qn4pnG9WFAeM74eNqWA4xlHVmG5CRKtbn8/nZlLtelGLYiuC6FTboDOHT5+I5g7Hi0KP+uwdMx2ixwN07mqIKUnLKiCwPC4/1R8h3u5VTlTl/qqRIRlbFD90UmkwFJDxRcNT/PL+4KzC7RVlksH1fvjSAbsNAHGpzZlO5iN3J8Al9xRWjuCI0NZZQeA9UTOrLSiBnm8gJT+J73ZjU+Nw1LW7K5yVPyyumsOr4dC+CrvxO3JkGN1qyKU+jb+dwwFUQ7w+mvBCUpPn+8z14d5M/98bqIKfzSHNmncRfuo7nbThc4rvloRopIsGS6vvNAXutR8ycx8HPBmWFhBvwb8RoYg5Y9zdeHl/imfXl/P9COjWt/68JcV+Dy6LspJ/Tl20sIwX/bY/0qED9WLW1MRI660cVkvbeKUZevx5nC0lQjGVIeA72K7kDU5BQJZl5yCAfIdoGHf/V9u1cdMvpF2IPYAKobuaGY7NJXO2jxTuIM/WQ9tfXspuLMJ99tH+t+Z5zgBAvL0Cdz7rIyFzmT6O7QuDt7r/6+Kk9yZWhERIGM9H75fkLptiLy1g6kqpl62HuDFNZumMy2fCtyC8Z4HPfC40K02N9W7398s8qzU5RJLWbpo5sE1ZQZ2DGBO08Rl/srQrzIIRuxsgBFMR1WHg51CmJEYFomDkaDdh4APMfkAYcBVGzxzs1bEwOqFAKCtWSFI6FYd+mV4IzyFJ8SJ8e84ZxgEnA+gKoaASAg9QO7e8LaUaxFKip6x+hD7ZZO9Z9kBjlvIau156CL]]></req_info>
            </xml>
EOF;
        return trim($resultXml);
    }
}


