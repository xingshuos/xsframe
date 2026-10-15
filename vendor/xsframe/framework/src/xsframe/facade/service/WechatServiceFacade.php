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

namespace xsframe\facade\service;


use xsframe\service\WechatService;
use think\Facade;

/**
 * @method static getAccessToken(string $appId, string $secret, $expire = 7000, $isReload = false)
 * @method static getSignPackage(string $appId, string $secret, string $url)
 * @method static sendTplNotice(string $appId, string $secret, string $openid, array $msg, string $url, string $templateId)
 * @method static wechatAuth(string $code, string $appid, string $secret, string $snsapi = 'snsapi_base')
 * @method static createMenu(string $appid, string $secret, array $menus, $isReload = false)
 * @method static addConditionalMenu(string $appid, string $secret, array $button, $matchrule, $isReload = false)
 * @method static deleteConditionalMenu(string $appid, string $secret, string $menuid, $isReload = false)
 * @method static tryMatchConditionalMenu(string $appid, string $secret, string $userId, $isReload = false)
 * @method static getAllMenus(string $appid, string $secret, $isReload = false)
 * @method static bool verifyCallbackSignature(string $token, string $timestamp, string $nonce, string $signature, string $encrypted = '')
 * @method static ?string verifyCallbackEcho(string $token, string $appId, string $aesKey, array $query)
 * @method static ?string decryptCallbackMessage(string $encrypted, string $aesKey, string $appId)
 * @method static ?string encryptCallbackReply(string $replyXml, string $token, string $aesKey, string $appId, string $timestamp, string $nonce)
 * @method static string callbackConfigFingerprint(array $config)
 */
class WechatServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return WechatService::class;
    }
}
