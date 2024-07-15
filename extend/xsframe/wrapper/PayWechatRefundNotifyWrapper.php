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
        $moduleName = app('http')->getName();
        $map = config("app.app_map");
        $realModuleName = array_key_exists($moduleName, $map) ? $map[$moduleName] : $moduleName;
        $moduleName = $realModuleName ?: $moduleName;

        $data = (array)$this->getReqInfoData($this->get);
        $data['module'] = $moduleName;

        try {
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
            Db::name('sys_paylog')->where(['ordersn' => $data['out_trade_no']])->update(['status' => -1]);
        } catch (Exception $e) {
            LoggerUtil::error($e->getMessage());
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
            <xml><return_code>SUCCESS</return_code><appid><![CDATA[wx21490d8841df7637]]></appid><mch_id><![CDATA[1606994267]]></mch_id><nonce_str><![CDATA[6e690d882dfc4f50df7cff71c2d0aa84]]></nonce_str><req_info><![CDATA[cnPSF/Aqj5fx7OA8CetNSqKUdS6uTTKVcvogonC9QHiq7xzt+7CEWHCdQ2KS3ou6ejkI1Z3L9Y+46yIBzFOBzuF5QCfg4Q106gMtw2DDsictyrET3MxIsIYciD3VuR/wwOUCGKMr1cPOn8X/wR888fhzJ987uPh4iVgj2lm5CBbRkglSlOCM67oW76f7h4dw/YydkUiKOxSnEvIXMQQjkXml6XYU68w/wbq9ltVazOlaH1urWUwselbKspQld2c//rPiefGRcR2RCriruGydt4k8+4YvsbEJNbhFy/HoYbVmlYgmKYqFrt+yw5vr/9pPMyJSdutN4yMjx0l1n93BRplDphvLJ+5asT1Tae51/2F2JVGYt/Qn4pnG9WFAeM74eNqWA4xlHVmG5CRKtbn8/nZlLtelGLYiuC6FTboDOHSBYcnKOM8Cy29pd79C+JFOwN6shNR6mCOrQLdHEzP6Ch3u5VTlTl/qqRIRlbFD90UmkwFJDxRcNT/PL+4KzC7RVlksH1fvjSAbsNAHGpzZlNGJrg8FyDBdx25bAR4nxoyciIGDs7/aXICgk3vUGKXm/FpLL9umaNxoX5+Gopl5mAVkMq4qV9Z028A8CXCbiZ6STNguxbTuIfJbb/TA65msuXxfr/9J5GYoZqKLmEKaiJa1Jl0XjW6SCZDlknpG3wgeNlFBQEsh215tlSrthyzj79Im5tqRu27eTTK373cN5gN3BnvBSNFarWpf+4ua63Qp5y0LaUS9d9cf3t4N6GOQnjjP4KlNVGnPzGCm+TofwQreDOM4z2RxyKvscnPEhTefNEWMhCtoMR4IA08fyqk8yrNTlEktZumjmwTVlBnYMewEuhOi78cEp2fvheCaN7ZrgXIncMYheWYeLpSFbZirjpruqGJgkvrCsTbUKnhxQdXS2qyVAZzWDMWQEqsX/tn5qj33eSqY31oquJdmQTB2VW8te5yk6veEV7p5JreKKR9HDqm/0JVo5FDy3FIwYTtX5z2FV22UV8M6hVFRLYbIhwyxc3CF9k6hFXndDu48xaCZMLlGbK2Cm+dnob7fV3qJ5w5nHqa1lDCiYJBBnu4rMVCRpU0XyPmbK+ULhNMoSSX6Y6jLCTGTRPrBFBgeZJYlRyhn4CkFkTCX24Hf9R08]]></req_info></xml>
EOF;
        return trim($resultXml);
    }
}


