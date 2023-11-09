<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型使用Think
    'type'               => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'          => 1,
    // 模板目录名
    'view_dir_name'      => 'view',
    // 模板后缀
    'view_suffix'        => 'html',
    // 模板文件名分隔符
    'view_depr'          => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'          => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'            => '}',
    // 标签库标签开始标记
    'taglib_begin'       => '{',
    // 标签库标签结束标记
    'taglib_end'         => '}',


    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'tpl_cache'          => false,
    'tpl_replace_string' => [

        // 后台公共静态目录
        '__ADMIN_COMPONENT__' => '/app/admin/static/components',
        '__ADMIN_FONTS__'     => '/app/admin/static/fonts',
        '__ADMIN_CSS__'       => '/app/admin/static/css',
        '__ADMIN_JS__'        => '/app/admin/static/js',
        '__ADMIN_IMG__'       => '/app/admin/static/images',

    ]
];
