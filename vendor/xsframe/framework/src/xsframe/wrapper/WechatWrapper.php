<?php


namespace xsframe\wrapper;

use xsframe\contract\WechatCallbackHandlerInterface;
use xsframe\facade\service\WechatServiceFacade;
use think\Request;
use think\facade\Log;

/**
 * 微信公众号回调入口，负责接入验证与消息传输层确认。
 */
class WechatWrapper
{
    /** @var array 请求参数，保留已有 JS SDK 接口使用方式。 */
    public $get = null;

    /** @var Request 当前请求。 */
    private $request;

    /** @var SettingsWrapper 公众号配置读取器。 */
    protected $settingsController;

    /**
     * 初始化公众号回调入口。
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->get     = $this->request->param();

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
        }

        $this->init();
    }

    /**
     * 根据请求类型处理微信验证或原有 JS SDK 操作。
     */
    private function init()
    {
        $actionValue = $this->get['action'] ?? '';
        $action = is_scalar($actionValue) ? strval($actionValue) : '';

        // 微信服务器不带 action；签名只能读取 URL 查询参数，不能与消息体混合。
        if ($action === '' && ($this->request->isGet() || $this->request->isPost())) {
            $this->handleCallback();
            return;
        }

        switch ($action) {
            case 'getSignPackage':
                $uniacid = $this->get['uniacid'] ?? $this->get['i'] ?? 0;
                $url     = $this->get['url'];

                $account = $this->settingsController->getAccountSettings($uniacid);

                $appId  = $account['settings']['wechat']['appid'];
                $secret = $account['settings']['wechat']['secret'];

                $result = WechatServiceFacade::getSignPackage($appId, $secret, $url);
                $result['debug'] = false;
                $this->success($result);
                break;
            default:
                self::success();
        }
    }

    /**
     * 校验微信请求，并将已验证的业务消息交给注册的应用处理。
     */
    private function handleCallback(): void
    {
        // 读取未经过默认输入过滤器改写的 URL 参数，保证签名使用微信发送的原值。
        $query = $this->request->get(false);
        $accountId = $query['i'] ?? $query['uniacid'] ?? null;
        if (!is_scalar($accountId) || !ctype_digit(strval($accountId)) || intval($accountId) < 1) {
            $this->plainResponse(400, '公众号标识无效');
        }

        $account = $this->settingsController->getAccountSettings(intval($accountId));
        $config = $account['settings']['wechat'] ?? [];
        if (!is_array($config) || empty($config['token'])) {
            $this->plainResponse(404, '公众号接入配置不存在');
        }

        $token = strval($config['token']);
        $appId = strval($config['appid'] ?? '');
        $aesKey = strval($config['encodingaeskey'] ?? '');
        if ($this->request->isGet()) {
            $echo = WechatServiceFacade::verifyCallbackEcho($token, $appId, $aesKey, $query);
            if ($echo === null) {
                $this->plainResponse(403, '微信接入签名校验失败');
            }

            // 接通状态只在微信的验证请求通过后落库，并绑定当前令牌与加密配置。
            $saved = $this->settingsController->setAccountSettings(intval($accountId), 'wechat', [
                'connected_at' => time(),
                'connection_fingerprint' => WechatServiceFacade::callbackConfigFingerprint($config),
            ]);
            if ($saved === false) {
                $this->plainResponse(500, '公众号接通状态保存失败');
            }
            $this->plainResponse(200, $echo);
        }

        if (!$this->request->isPost()) {
            $this->plainResponse(405, '不支持的请求方式');
        }
        $timestamp = $query['timestamp'] ?? null;
        $nonce = $query['nonce'] ?? null;
        if (!is_scalar($timestamp) || !is_scalar($nonce)) {
            $this->plainResponse(403, '微信消息签名校验失败');
        }

        $body = $this->request->getContent();
        if ($body === '' || strlen($body) > 1048576) {
            $this->plainResponse(400, '微信消息内容无效');
        }
        // 仅接收结构完整的微信 XML，防止对无效消息返回成功确认。
        $xml = $this->parseMessageXml($body);
        if ($xml === null) {
            $this->plainResponse(400, '微信消息格式无效');
        }
        $encryptedMode = isset($query['encrypt_type']) && is_scalar($query['encrypt_type']) && strval($query['encrypt_type']) === 'aes';
        if ($encryptedMode) {
            // 安全模式先提取密文，再用消息签名和公众号 AppID 双重校验。
            $encrypted = strval($xml->Encrypt ?? '');
            $signature = $query['msg_signature'] ?? null;
            if ($encrypted === '' || !is_scalar($signature)
                || !WechatServiceFacade::verifyCallbackSignature($token, strval($timestamp), strval($nonce), strval($signature), $encrypted)) {
                $this->plainResponse(403, '微信消息签名校验失败');
            }
            $messageXml = WechatServiceFacade::decryptCallbackMessage($encrypted, $aesKey, $appId);
            if ($messageXml === null) {
                $this->plainResponse(403, '微信消息解密失败');
            }
            $xml = $this->parseMessageXml($messageXml);
            if ($xml === null) {
                $this->plainResponse(400, '微信消息格式无效');
            }
        } else {
            $signature = $query['signature'] ?? null;
            if (!is_scalar($signature) || !WechatServiceFacade::verifyCallbackSignature($token, strval($timestamp), strval($nonce), strval($signature))) {
                $this->plainResponse(403, '微信消息签名校验失败');
            }
        }

        $message = [];
        foreach ($xml->children() as $name => $value) {
            $message[$name] = strval($value);
        }
        if (empty($message['ToUserName']) || empty($message['FromUserName']) || empty($message['MsgType'])) {
            $this->plainResponse(400, '微信消息缺少必要字段');
        }

        // 框架只绑定应用契约；规则查询、关键词匹配和回复 XML 均由 xs_wechat 处理。
        $handlerClass = config('wechat_callback.handler');
        if (!is_string($handlerClass) || !class_exists($handlerClass)) {
            $this->plainResponse(503, '公众号消息处理服务不可用');
        }
        try {
            $handler = app($handlerClass);
            if (!$handler instanceof WechatCallbackHandlerInterface) {
                $this->plainResponse(503, '公众号消息处理服务配置无效');
            }
            $reply = $handler->handle(intval($accountId), $message);
        } catch (\Throwable $error) {
            Log::error('公众号消息处理失败：' . $error->getMessage());
            $this->plainResponse(500, '公众号消息处理失败');
        }

        if ($reply === null || $reply === '') {
            $this->plainResponse(200, 'success');
        }
        if ($encryptedMode) {
            try {
                $reply = WechatServiceFacade::encryptCallbackReply($reply, $token, $aesKey, $appId, strval($timestamp), strval($nonce));
            } catch (\Throwable $error) {
                Log::error('微信回复加密失败：' . $error->getMessage());
                $this->plainResponse(500, '微信回复加密失败');
            }
            if ($reply === null) {
                $this->plainResponse(500, '微信回复加密失败');
            }
        }
        $this->xmlResponse($reply);
    }

    /**
     * 将微信消息 XML 解析为安全的 SimpleXML 对象。
     */
    private function parseMessageXml(string $body): ?\SimpleXMLElement
    {
        // 微信消息不需要 DTD，拒绝实体声明，避免外部实体和实体展开风险。
        if (stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) {
            return null;
        }
        $previousXmlErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previousXmlErrors);
        return $xml !== false && $xml->getName() === 'xml' ? $xml : null;
    }

    /**
     * 将应用生成的 XML 直接返回微信服务器。
     */
    private function xmlResponse(string $body): void
    {
        http_response_code(200);
        header('Content-Type: application/xml; charset=utf-8');
        exit($body);
    }

    /**
     * 微信回调必须返回纯文本，不能包装成后台统一 JSON 响应。
     */
    private function plainResponse(int $status, string $body): void
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        exit($body);
    }

    /**
     * 保留原有 JS SDK 接口的 JSON 响应格式。
     */
    protected function success($data = [], $code = "200", $message = 'success')
    {
        $code    = $data['code'] ?? $code;
        $message = $data['msg'] ?? $message;
        $data    = $data['data'] ?? $data;

        $retData = [
            'code' => (string)$code,
            'msg'  => $message,
            'data' => $data
        ];
        exit(json_encode($retData));
    }
}
