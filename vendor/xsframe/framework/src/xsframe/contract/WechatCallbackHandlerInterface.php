<?php

namespace xsframe\contract;

/**
 * 微信公众号消息业务处理契约，框架只负责通信与加解密。
 */
interface WechatCallbackHandlerInterface
{
    /**
     * 处理已验证的公众号消息，返回回复 XML；无需回复时返回 null。
     *
     * @param int $uniacid 公众号所属账号 ID
     * @param array $message 已解析的微信消息字段
     * @return string|null
     */
    public function handle(int $uniacid, array $message): ?string;
}
