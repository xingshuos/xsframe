<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\util;

class WechatUtil
{

    // 必须是微信浏览器
    public static function mustBeWechat($msg = null)
    {
        if (!self::isWechat()) {
            $msg = $msg ?? '请在微信客户端打开链接';
            die("<!DOCTYPE html>
                <html><head><title>抱歉，出错了</title><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1, user-scalable=0\"><link rel=\"stylesheet\" type=\"text/css\" href=\"https://res.wx.qq.com/open/libs/weui/0.4.1/weui.css\"></head>
                    <body>
                        <div class=\"weui_msg\"><div class=\"weui_icon_area\"><i class=\"weui_icon_info weui_icon_msg\"></i></div><div class=\"weui_text_area\"><h4 class=\"weui_msg_title\">{$msg}</h4></div></div>
                    </body>
                </html>");
        }
    }

    // 验证是否微信浏览器
    public static function isWechat()
    {
        if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
            return false;
        }
        return true;
    }
}