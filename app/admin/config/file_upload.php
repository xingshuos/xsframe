<?php
/**
 * 公共文件上传协议配置。
 *
 * 这里只定义跨业务通用的上传结果字段，不定义课程素材的扩展名、大小限制、权限或存储路径。
 * 课程素材等具体业务的规则仍由对应应用配置和服务负责。
 * @author fengzi
 */
return [
    // 公共上传服务判定“上传成功结果完整”时使用的字段。
    'result_required_fields' => [
        'file_url',
        'file_size',
        'file_ext',
        'mime_type',
        'file_md5',
        'origin_file_name',
    ],
];
