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

namespace xsframe\pay\Weixin\Data;

use xsframe\pay\Weixin\Base\WxPayDataBase;
use xsframe\pay\Weixin\Exception\WxPayException;


/**
 * 接口调用结果类
 *
 * @author widyhu
 */
class WxPayResults extends WxPayDataBase
{
    /**
     * 生成签名 - 重写该方法
     *
     * @param WxPayConfigInterface $config 配置对象
     * @param bool $needSignType           是否需要补signtype
     *
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($config, $needSignType = false)
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $config->GetKey();
        //签名步骤三：MD5加密或者HMAC-SHA256
        if (strlen($this->GetSign()) <= 32) {
            //如果签名小于等于32个,则使用md5验证
            $string = md5($string);
        } else {
            //是用sha256校验
            $string = hash_hmac("sha256", $string, $config->GetKey());
        }
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * @param WxPayConfigInterface $config 配置对象
     *                                     检测签名
     */
    public function CheckSign($config)
    {
        if (!$this->IsSignSet()) {
            throw new WxPayException("签名错误！");
        }

        $sign = $this->MakeSign($config, false);
        if ($this->GetSign() == $sign) {
            //签名正确
            return true;
        }
        throw new WxPayException("签名错误！");
    }

    /**
     * 使用数组初始化
     *
     * @param array $array
     */
    public function FromArray($array)
    {
        $this->values = $array;
    }

    /**
     * 使用数组初始化对象
     *
     * @param array $array
     * @param 是否检测签名 $noCheckSign
     */
    public static function InitFromArray($config, $array, $noCheckSign = false)
    {
        $obj = new self();
        $obj->FromArray($array);
        if ($noCheckSign == false) {
            $obj->CheckSign($config);
        }
        return $obj;
    }

    /**
     * 设置参数
     *
     * @param string $key
     * @param string $value
     */
    public function SetData($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * 将xml转为array
     *
     * @param WxPayConfigInterface $config 配置对象
     * @param string $xml
     *
     * @return array|bool
     */
    public static function Init($config, $xml)
    {
        $obj = new self();
        $obj->FromXml($xml);
        //失败则直接返回失败
        if ($obj->values['return_code'] != 'SUCCESS') {
            foreach ($obj->values as $key => $value) {
                #除了return_code和return_msg之外其他的参数存在，则报错
                if ($key != "return_code" && $key != "return_msg") {
                    throw new WxPayException("输入数据存在异常！");
                    return false;
                }
            }
            return $obj->GetValues();
        }
        $obj->CheckSign($config);
        return $obj->GetValues();
    }
}