<?php

namespace app\admin\service;

/**
 * 资源数据装饰器
 * 负责在资源数据返回前端前，根据权限信息对数据进行装饰处理
 * 包括：注入权限标识、剥离不可下载资源的文件URL
 * 设计为共享层组件，可被 sz_contest 和 sz_jiaowu 模块共同引用
 * @author fengzi
 */
class ResourceDataDecorator
{
    /**
     * 对资源数据应用完整权限装饰
     * 会注入 permission 标识，并在无下载权限时清空文件URL
     * @param array &$data 资源数据（引用传递，直接修改）
     * @param array $permissions 权限信息 ['allow_view' => bool, 'allow_download' => bool]
     * @return void
     * @author fengzi
     */
    public static function applyPermission(&$data, $permissions)
    {
        // 注入权限标识，前端据此控制UI展示
        $data['permission'] = [
            'allow_view'     => $permissions['allow_view'],
            'allow_download' => $permissions['allow_download'],
        ];

        // 无下载权限时，清空文件URL防止前端直接获取下载地址
        if (!$permissions['allow_download']) {
            $data['file_url'] = '';
            $data['url_full'] = '';
            $data['download_disabled'] = true;
        }
    }

    /**
     * 对资源数据应用预览权限装饰
     * 仅注入权限标识和下载禁用标记，不清空文件URL（预览场景需要保留URL）
     * @param array &$data 资源数据（引用传递，直接修改）
     * @param array $permissions 权限信息 ['allow_view' => bool, 'allow_download' => bool]
     * @return void
     * @author fengzi
     */
    public static function applyPreviewPermission(&$data, $permissions)
    {
        // 注入权限标识
        $data['permission'] = [
            'allow_view'     => $permissions['allow_view'],
            'allow_download' => $permissions['allow_download'],
        ];

        // 无下载权限时标记禁用，但保留URL供预览使用
        if (!$permissions['allow_download']) {
            $data['download_disabled'] = true;
        }
    }
}
