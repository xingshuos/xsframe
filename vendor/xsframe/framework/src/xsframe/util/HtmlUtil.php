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

namespace xsframe\util;

class HtmlUtil
{

    // 从 HTML 中提取纯文本
    public static function getPlainTextFromHtml($html, $maxLength)
    {
        // 移除 HTML 标签
        $plainText = strip_tags($html);

        // 去除多余空白字符（可选）
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        $plainText = trim($plainText);

        // 截取指定字数的文字
        // 注意：使用 mb_substr 处理多字节字符（如中文）
        if (mb_strlen($plainText, 'UTF-8') > $maxLength) {
            $plainText = mb_substr($plainText, 0, $maxLength, 'UTF-8');
            // 可以在末尾添加省略号（可选）
            $plainText .= '...';
        }

        return $plainText;
    }

}