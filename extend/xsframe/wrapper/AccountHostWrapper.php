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

namespace xsframe\wrapper;


use xsframe\enum\SysSettingsKeyEnum;
use think\facade\Cache;
use think\facade\Db;

class AccountHostWrapper
{
    private $hostKey = SysSettingsKeyEnum::DOMAIN_MAPPING_LIST_KEY;

    // 获取域名映射关系列表
    public function getAccountHost($reload = false)
    {
        $hostList = Cache::get($this->hostKey);
        if (empty($hostList) || $reload) {
            $newList = [];

            try {
                $dbHostList = Db::name('sys_account_host')->field("id,uniacid,host_url,default_module,default_url")->select()->toArray();
                if ($dbHostList) {
                    foreach ($dbHostList as $hostInfo) {
                        $newList[$hostInfo['host_url']] = $hostInfo;
                    }
                    Cache::set($this->hostKey, $newList);
                }
            } catch (\Exception $exception) {

            }

            $hostList = $newList;
        }
        return empty($hostList) ? [] : $hostList;
    }

    // 设置域名映射关系列表
    public function setAccountHost()
    {
        $hostList = $this->getAccountHost(true);
        return $hostList;
    }

    // 重新加载域名映射关系列表
    public function reloadAccountHost()
    {
        $hostList = $this->getAccountHost(true);
        return $hostList;
    }

    // 获取域名绑定的uniacid值
    public function getAccountHostUniacid($hostUrl)
    {
        // TODO 之后最好加入缓存，目前直接读取数据库
        $hostList = $this->getAccountHost(true);

        $uniacid = 0;
        if (!empty($hostList)) {
            $uniacid = array_key_exists($hostUrl, $hostList) ? $hostList[$hostUrl]['uniacid'] : 0;
        }
        return $uniacid;
    }

}