<?php


namespace xsframe\facade\service;


use xsframe\base\BaseFacade;
use xsframe\service\JingTanService;

/**
 * @method static getAssetList(string $phone, int $pIndex = 1, int $pSize = 10)
 * @method static getAssetInfo(string $phone, string $nftId)
 * @method static getAccessToken(mixed|string $auth_code)
 * @method static getUserInfo(mixed $accessToken)
 * @method static getAllAssetListByMobile($mobile, false|mixed $reload = false)
 */
class JingTanServiceFacade extends BaseFacade
{
    protected static function getFacadeClass()
    {
        return JingTanService::class;
    }
}